#!/usr/bin/env php
<?php
   /** ************************************************************
    * Download the domlist.php pages of all configured AlternC's 
    * instance, and put them in the database of synchronized domains
    * remember the diff with previous retrieval.
    */
require_once(__DIR__."/sync.conf.php");

// FIXME: if -f FORCE the reload of all zones
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
  if ($GLOBALS["DEBUG"]) $std_options[CURLOPT_VERBOSE]=true;
  $options = ($custom_options) ? ($std_options + $custom_options) : $std_options;

  // start the first batch of requests
  for ($i = 0; $i < $rolling_window; $i++) {
    $ch = curl_init();
    $options[CURLOPT_URL] = $urls[$i]["url"];
    if ($GLOBALS["DEBUG"]) echo "URL: ".$urls[$i]["url"]."\n";
    curl_setopt_array($ch,$options);
    // Handle custom cafile for some https url
    if (strtolower(substr($options[CURLOPT_URL],0,5))=="https") { // https :)
      if (isset($urls[$i]["nosslcheck"]) && $urls[$i]["nosslcheck"]==1) {
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
      } else {
	if (isset($urls[$i]["cafile"]) && $urls[$i]["cafile"] && is_file($urls[$i]["cafile"])) {
	  curl_setopt($ch,CURLOPT_CAINFO,$urls[$i]["cafile"]);
	  if ($GLOBALS["DEBUG"]) echo "cainfo set to ".$urls[$i]["cafile"]."\n";
	} else {
	  curl_setopt($ch,CURLOPT_CAINFO,DEFAULT_CAFILE);
	  if ($GLOBALS["DEBUG"]) echo "cainfo set to DEFAULT\n";
	}
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
      // TODO : since ssl_verify_result is buggy, if we have [header_size] => 0  && [request_size] => 0 && [http_code] => 0, AND https, we can pretend the SSL certificate is buggy.
      if ($GLOBALS["DEBUG"]) { echo "Info for ".$done['handle']." \n"; print_r($info); } 
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
$stats=array("serverok"=>0,"added"=>0,"deleted"=>0,"serverfailure"=>0);

function parsezone($url,$content,$info) {
  global $db,$somethingchanged,$serversalert,$stats;

  if ($GLOBALS["DEBUG"]) {  
    echo "Url:\n"; print_r($url);
    echo "Content:\n"; print_r($content);
    echo "Info:\n"; print_r($info);
  }

  if ($info["http_code"]==200) {
    $s=explode("\n",$content);
    $domains=array();
    foreach($s as $line) {
      $line=trim(strtolower($line));
      if (preg_match("#^[0-9a-z-]+\.[0-9a-z-\.]+$#",$line)) {
	// the preg is rough ... All what I found was *too* RFC-compliant, but Icann & registries are not respecting RFC...
	// And we FORCE to have at least one DOT so that we don't start to host a TLD ^o^
	$domains[]=$line;
      }
    }
    if (!count($domains)) {
      $db->q("INSERT INTO difflog SET server=?, datec=NOW(), action=?;",array($url["id"],DIFF_ACTION_EMPTY));
      return; // Exit for this server in case of empty zone...
    }

    $stats["serverok"]++;
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

	// Also search if this is a subdomain of an existing zone, on a server not owned by the same user !
	$members=explode(".",$domain);
	$sql="";
	while (count($members)>1) {
	  if ($sql) $sql.=" OR ";
	  array_pop($members);
	  $sql.=" zone='".implode(".",$members)."' ";
	}
	
	$already=$db->qone("SELECT zones.id FROM zones,servers WHERE zones.enabled=1 AND zones.server=servers.id AND servers.user!=? AND ($sql) ",array($url["user"]));
	if ($already) {
	  $enabled=0; 
	  $action=DIFF_ACTION_INSERTED_DISABLED;
	  // Send a mail ? TODO
	} else {
	  $enabled=1;
	  $action=DIFF_ACTION_INSERTED;
	}

      }

      $somethingchanged=true;
      $stats["added"]++;
      $db->q("INSERT INTO zones SET server=?, zone=?, enabled=?, datec=NOW();",array($url["id"],$domain,$enabled));
      $db->q("INSERT INTO difflog SET server=?, zone=?, datec=NOW(), action=?;",array($url["id"],$domain,$action));
    }
    
    $delete=array_diff($current,$domains);
    foreach($delete as $domain) {
      $somethingchanged=true;
      $stats["deleted"]++;
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
    $db->q("UPDATE servers SET updated=NOW(), lastcount=(SELECT COUNT(*) FROM zones WHERE server=? AND enabled=1) WHERE id=?;",array($url["id"],$url["id"]));

  } else { // HTTP_CODE != 200 
    $stats["serverfailure"]++;
    $serversalert[]=$url["id"];
    $db->q("INSERT INTO difflog SET server=?, datec=NOW(), action=?;",array($url["id"],DIFF_ACTION_TIMEOUT));
  }
} // parsezone (callback function)






$st=$db->q("SELECT id,user,url,cacert,nosslcheck FROM servers WHERE enabled=1 ORDER BY id;");

$scount=0;
$srv=array();
// How many HTTP(S) sockets can we do in parallel ? 
while ($c=$st->fetch(PDO::FETCH_ASSOC)) {
  $srv[$scount]=array( "url" => $c["url"],
		       "id" => $c["id"],
		       "nosslcheck" => $c["nosslcheck"],
		       "user" => $c["user"]
		       );
  if ($c["cacert"]) {
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

// Add a difflog entry for server 0 telling that sync was launched properly
$db->q("INSERT INTO difflog SET server=0, datec=NOW(), action=?, zone=?;",array(DIFF_ACTION_STATS,serialize($stats)));

