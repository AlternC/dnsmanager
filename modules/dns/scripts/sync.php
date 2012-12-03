#!/usr/bin/env php
<?php

   /** ************************************************************
    * Download the domlist.php pages of all configured AlternC's 
    * instance, and put them in the database of synchronized domains
    * remember the diff with previous retrieval.
    */

// Maximum number of parallel HTTP(S) sockets
define("MAX_SOCKETS",200);
// Location of the system-wide Certificate Authority File  (MUST BE an absolute path)
define("DEFAULT_CAFILE","/etc/ssl/certs/ca-certificates.crt");
// Absolute path to the bind slavedns zone list. 
// We must be allowed to write to this file.
define("SLAVEZONE_FILE","/tmp/bind.slave.conf");
// Command to launch to reload bind configuration. 
// Should be something like "rndc reconfig" maybe using sudo if you are neither root nor bind user.
// DON'T USE BIND9 RESTART OR RNDC RELOAD. READ THE MAN PAGES!
// You can replace it by a shell-script that could push the zone to the real servers
define("BIND_RELOAD","sudo rndc reconfig");


if (isset($argv) && count($argv)>1) {
  // we may ask for only some FQDN. TODO.
}


require_once(__DIR__ . '/../../../common.php');
require_once(__DIR__.'/../libs/constants.php');
//require_once __DIR__ . '/../libs/api.php';


/** ************************************************************ 
 * Launch parallel (using MAX_SOCKETS sockets maximum) retrieval
 * of URL using CURL 
 * @param $urls array of associative array, each having the following keys : 
 *  url = url to get (of the form http[s]://login:password@host/path/file?querystring )
 *  cafile = if https, can point to a ca file, if not specified, will use a default cafile.
 *  - any other key will be sent as it is to the callback function
 * @param $callback function called for each request when completing. First argument is the $url object, second is the content (output)
 *  third is the info structure from curl for the returned page. 200 for OK, 403 for AUTH FAILED, 0 for timeout, dump it to know it ;) 
 *  this function should return as soon as possible to allow other curl calls to complete properly.
 * @param $cursom_options array of custom CURL options for all transfers
 */
function rolling_curl($urls, $callback, $custom_options = null) {
  // make sure the rolling window isn't greater than the # of urls
  $rolling_window = MAX_SOCKETS;
  $rolling_window = (count($urls) < $rolling_window) ? count($urls) : $rolling_window;

  $master = curl_multi_init();
  $curl_arr = array();

  // add additional curl options here
  $std_options = array(CURLOPT_RETURNTRANSFER => true,
		       CURLOPT_FOLLOWLOCATION => true,
		       CURLOPT_CONNECTTIMEOUT => 5,
		       CURLOPT_MAXREDIRS => 5);
                       // Default timeout is 10 seconds
  $options = ($custom_options) ? ($std_options + $custom_options) : $std_options;

  // start the first batch of requests
  for ($i = 0; $i < $rolling_window; $i++) {
    $ch = curl_init();
    $options[CURLOPT_URL] = $urls[$i]["url"];
    curl_setopt_array($ch,$options);
    // Handle custom cafile for some https url
    if (strtolower(substr($options[CURLOPT_URL],0,5))=="https") { // https :) 
      if (isset($urls[$i]["cafile"]) && $urls[$i]["cafile"] && is_file($urls[$i]["cafile"])) {
	curl_setopt($ch,CURLOPT_CAINFO,$urls[$i]["cafile"]);
      } else {
	curl_setopt($ch,CURLOPT_CAINFO,DEFAULT_CAFILE);
      }
    }
    curl_multi_add_handle($master, $ch);
  }

  do {
    while(($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM);
    if($execrun != CURLM_OK)
      break;
    // a request was just completed -- find out which one
    while($done = curl_multi_info_read($master)) {
      $info = curl_getinfo($done['handle']);
      if ($info['http_code'] == 200)  {
	$output = curl_multi_getcontent($done['handle']);
      } else {
	// request failed.  add error handling.
	$output="";
      }
      // request terminated.  process output using the callback function.
      // Pass the url array to the callback, so we need to search it
      foreach($urls as $url) {
	if ($url["url"]==$info["url"]) {
	  $callback($url,$output,$info);
	  break;
	}
      }

      // If there is more: start a new request
      // (it's important to do this before removing the old one)
      if ($i<count($urls)) {
	$ch = curl_init();
	$options[CURLOPT_URL] = $urls[$i++];  // increment i
	curl_setopt_array($ch,$options);
	curl_multi_add_handle($master, $ch);
      }
      // remove the curl handle that just completed
      curl_multi_remove_handle($master, $done['handle']);
    }
  } while ($running);
  
  curl_multi_close($master);
  return true;
}


$somethingchanged=false; // Did something changed ? if yes, reconfig bind 
$serversalert=array(); // Which servers had alerts 

function parsezone($url,$content,$info) {
  global $db,$somethingchanged,$serversalert;
  /*
  echo "Url:\n"; print_r($url);
  echo "Content:\n"; print_r($content);
  echo "Info:\n"; print_r($info);
  */

  if ($info["http_code"]==200) {
    $s=explode("\n",$content);
    $domains=array();
    foreach($s as $line) {
      $line=trim(strtolower($line));
      if (preg_match("#^[0-9a-z-\.]+$#",$line)) { // roughly. All what I found was *too* RFC-compliant, but Icann & registries are not respecting RFC...
	$domains[]=$line;
      }
    }
    $domains=array_unique($domains);
    sort($domains); 
    // compare with current (sql) list of zones, add or remove as needed...
    $st=$db->q("SELECT zone FROM zones WHERE server=? ORDER BY zone;",array($url["id"]));
    $current=array();
    while ($z=$st->fetch()) {
      $current[]=$z->zone;
    }
    
    // Compare and create/destroy accordingly 
    $create=array_diff($domains,$current);
    foreach($create as $domain) {
      // search for this domain in our list : if it already exists, don't enable it
      $already=$db->qone("SELECT * FROM zones WHERE enabled=1 AND server!=? AND zone=?",array($url["id"],$domain));
      if ($already) {
	$enabled=0; 
	$action=DIFF_ACTION_INSERTED_DISABLED;
	// Send a mail ? TODO
      } else {
	$enabled=1;
	$action=DIFF_ACTION_INSERTED;
      }
      $somethingchanged=true;
      $db->q("INSERT INTO zones SET server=?, zone=?, enabled=?, datec=NOW();",array($url["id"],$domain,$enabled));
      $db->q("INSERT INTO difflog SET server=?, zone=?, datec=NOW(), action=?;",array($url["id"],$domain,$action));
    }
    
    $delete=array_diff($current,$domains);
    foreach($delete as $domain) {
      $somethingchanged=true;
      $db->q("DELETE FROM zones WHERE server=? AND zone=?;",array($url["id"],$domain));
      $db->q("INSERT INTO difflog SET server=?, zone=?, datec=NOW(), action=?;",array($url["id"],$domain,DIFF_ACTION_DELETED));
      // search for this domain in our list : if it already exist, and is disabled, enable it
      $already=$db->qone("SELECT * FROM zones WHERE enabled=0 AND server!=? AND zone=?",array($url["id"],$domain));
      if ($already) {
	// Send a mail ? TODO
	$db->q("UPDATE zones SET enabled=1,datec=NOW() WHERE id=?;",array($already->id));
	$db->q("INSERT INTO difflog SET server=?, zone=?, datec=NOW(), action=?;",array($already->server, $domain, DIFF_ACTION_ENABLED));
      }
    }
    $db->q("UPDATE servers SET updated=NOW() WHERE id=?;",array($url["id"]));

  } else { // HTTP_CODE != 200 
    $serversalert[]=$url["id"];
    $db->q("INSERT INTO difflog SET server=?, datec=NOW(), action=?;",array($url["id"],DIFF_ACTION_TIMEOUT));
  }
} // parsezone (callback function)


$st=$db->q("SELECT id,fqdn,login,password,hasssl,cacert FROM servers WHERE enabled=1 ORDER BY id;");

$scount=0;
$srv=array();
// How many HTTP(S) sockets can we do in parallel ? 
while ($c=$st->fetch(PDO::FETCH_ASSOC)) {
  $srv[$scount]=array( "url" => "http".(($c["hasssl"])?"s":"")."://".urlencode($c["login"]).":".urlencode($c["password"])."@".$c["fqdn"]."/admin/domlist.php",
		       "id" => $c["id"]
		       );
  if ($c["hasssl"] && $c["cacert"]) {
    $tmpname=tempnam('/tmp/','slavedns-certificate.');
    if (@file_put_contents($tmpname,$c["cacert"])) {
      $srv[$scount]["cafile"]=$tmpname;
    } else {
      @unlink($tmpname);
    }
  }
  $scount++;
} // for each active server

// PARALLEL GET 
rolling_curl($srv,"parsezone");

if ($somethingchanged) {
  $f=fopen(SLAVEZONE_FILE,"wb");
  $st=$db->q("SELECT zones.zone,servers.ip FROM zones,servers WHERE zones.enabled=1 AND servers.id=zones.server ORDER BY zones.zone;");
  if (!$st || !$f) {
    echo "Can't open the zonefile for writing, or can't do the query to the zone table\n";
    exit();
  }
  while ($z=$st->fetch()) {
    fputs($f,'zone "'.$z->zone.'" {
    type slave;
    allow-query { any; };
    file "'.$z->zone.'";
    masters { '.$z->ip.'; };
};
');
  }
  fclose($f);
  exec(BIND_RELOAD,$out,$res);
  if ($res!=0) {
    echo "Error launching ".BIND_RELOAD." command, please check\n";
  }
}

// Did something wrong happened for one of the servers
// If yes, send some alerts
if (count($serversalert)) {
}