<?php

// retrieve many urls at the same time, (asynchronously)
// return the content of each url, or false if an error occured for each one.
// - separate reponse headers and response content
// - manage "location:" response headers (absolute one only, no relative url...)
// - manage slow servers (wait for them, don't "loop for them" :) )
// - manage down servers (return "false") (TODO)
// - manage HTTPS protocol (using proc_open() and openssl s_client with -CAfile parameter. 
//
// based on brage@jeffnappi.com & Jose María Rodríguez Valls works (from php user contribs)

function FECHO($str) {
  echo $str;
}

function http_fetch($urlArr,$getheads=0) {
  // urlArr is an array of associative arrays, each having the following keys : 
  // $urls["url"] = the complete url (like http(s)://login:password@host/path/file?querystring), may contain login/password 
  // $urls["ca"] = if the url is https, this points to the CA File that contains a list of allowed Certificate Authorities for this request

  $maxsize=512*1024; // Max 512KB per page; 
  $sockets = Array(); // socket array!
  $urlInfo = Array(); // info arr
  $retDone = Array(); // 1 for done on each url
  $locations = array(); // locations change (max = 10)
  $retData = Array(); // Data of each url
  $retHead = Array(); // Response header of each url (useful for "location:" header.)
  $inbody=array(); // are we reading body or headers ?
  $errno  = Array();  // error code if not zero for each url.
  $errstr  = Array(); // error string if not null for each url
  $insockets=array(); // Input 
  $outsockets=array(); // and Output sockets to read/write from/to the remote end. used by fsockopen or openssl s_client

  for ($x=0;$x<count($urlArr);$x++) {
    $urlInfo[$x] = parse_url($urlArr[$x]);
    $urlInfo[$x]["port"] = ($urlInfo[$x]["port"]) ? $urlInfo[$x]["port"] : 80;
    $urlInfo[$x]["path"] = ($urlInfo[$x]["path"]) ? $urlInfo[$x]["path"] : "/";
    FECHO("[opening] host:".$urlInfo[$x]["host"]." port:".$urlInfo[$x]["port"]." ");
    $sockets[$x] = fsockopen($urlInfo[$x]["host"], $urlInfo[$x]["port"],
			     $errno[$x], $errstr[$x], 10); // timeout 10 sec.  
    if ($sockets[$x]) { // if not, we hit a socket error
      socket_set_blocking($sockets[$x],FALSE);
      $query = ($urlInfo[$x]["query"]) ? "?" . $urlInfo[$x]["query"] : "";
      fputs($sockets[$x],"GET " . $urlInfo[$x]["path"] . "$query HTTP/1.0\r\n" . 
	    "Host: " . $urlInfo[$x]["host"] . "\r\n" .
	    "User-Agent: Mozilla/5.0 (X11; U; Linux i686; fr-FR; rv:1.8) Gecko/20090201 Debian/3.0.0.5-4\r\n" .
	    "\r\n");
      FECHO("[getting] ");
    } else {
      FECHO("[error] ");
    }
    FECHO("path:".$urlInfo[$x]["path"]." query:".$query."<br>\n");
  }


  // ok, sock and query sent, now read the data from each one
  $done = false;
  $wait = false; // wait a little if previous cycle gave nothing ;) 
  // TODO : if we take nothing during X cycle, give up.
  while (!$done) {
    if ($wait) usleep($se);
    $wait = true; // we may wait on the next turn
    for ($x=0; $x < count($urlArr);$x++) {
      if ($sockets[$x] && !feof($sockets[$x])) {
	if ($inbody[$x]) { // in response body, let's fetch data.
	  $s = fgets($sockets[$x],1024);
	  if ($s) $wait=false; // we read sthg, don't wait on the next turn ;)
	  $retData[$x] .= $s;
	} else { // in response headers

	  $s = fgets($sockets[$x],1024);
	  // try to find a Location header: 
	  if (eregi("^location:(.*)$",$s,$match)) {
	    if (!$locations[$x]) $locations[$x]=0;
	    $locations[$x]++;
	    if ($locations[$x]==10) {
	      FECHO("[LOCATION LOOP DETECTED!!] ".$match[1]." <br>\n");
	      $retDone[$x] = 1;
	      $retHead[$x]=""; $retData[$x]=""; $errno[$x]=12;
	    } else {
	      FECHO("[LOCATION FOUND] ".$match[1]." <br>\n");
	      $new=trim($match[1]);
	      // ok, hard's life :) let's fetch the new real url.
	      fclose($sockets[$x]);
	      $urlInfo[$x] = parse_url($new);
	      $urlInfo[$x][port] = ($urlInfo[$x][port]) ? $urlInfo[$x][port] : 80;
	      $urlInfo[$x][path] = ($urlInfo[$x][path]) ? $urlInfo[$x][path] : "/";
	      FECHO("[opening] host:".$urlInfo[$x][host]." port:".$urlInfo[$x][port]." ");
	      $sockets[$x] = fsockopen($urlInfo[$x][host], $urlInfo[$x][port],
				       $errno[$x], $errstr[$x], 10); // timeout 10 sec.
	      socket_set_blocking($sockets[$x],FALSE);
	      $query = ($urlInfo[$x][query]) ? "?" . $urlInfo[$x][query] : "";
	      fputs($sockets[$x],"GET " . $urlInfo[$x][path] . "$query HTTP/1.0\r\n" .
		    "Host: " . $urlInfo[$x][host] . "\r\n" .
		    "User-Agent: Mozilla/5.0 (X11; U; Linux i686; fr-FR; rv:1.4) Gecko/20030910 Debian/1.4-4\r\n" .
		    "\r\n");
	      FECHO("path:".$urlInfo[$x][path]." query:".$query."<br>\n");
	      $retHead[$x]="";
	      $retDone[$x]=0;
	      $retData[$x]="";
	      $inbody[$x]=0;
	    } // test the location loop...
	  }
	  if (substr($s,0,2)=="\r\n") { // end of headers
	    $inbody[$x]=1; // ok, no location header, we will read and store the content next time.
	  } else { // still inside response headers
	    if ($s) $wait=false; // we read sthg, don't wait on the next turn ;)
	    $retHead[$x] .= $s;
	  }
	}
      } else { // this one ended
	if ($retDone[$x]!=1) { FECHO("[$x done, size ".strlen($retData[$x])."]\n"); }     
	$retDone[$x] = 1;
      }
      if (strlen($retData[$x])>$maxsize) { 
	if ($retDone[$x]!=1) { FECHO("[$x done, size ".strlen($retData[$x])."]\n"); }     
	$retDone[$x] = 1;
      }
    } // for each socket

    $done = (array_sum($retDone) == count($urlArr)); // are we done with everybody ?
    if ($wait) {
      $se+=500000; // wait one more 1/2 sec every empty loop.
      if ($se>4000000) { // 8 loops without any data ! = last wait was 4 sec (about 15 sec waiting)
	FECHO("[GIVE UP] ");
	$done=true; // give up !
      }
    } else {
      $se=0; // no wait so reset the wait counter
    }
  }
  FECHO("[got] Bytes/Socket: ");
  for ($x=0; $x < count($urlArr);$x++) { FECHO(strlen($retData[$x])." "); }
  FECHO("<br>\n");
  if ($getheads)
    return array($retData,$retHead);
  else
    return $retData; // returns array of http streams
}


