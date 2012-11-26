<?php

function format_size($s) {
  if ($s<1024) return $s."B";
  if ($s<1024*1024) return (intval($s/102.4)/10)."KiB";
  if ($s<1024*1024*1024) return (intval($s/102.4/1024)/10)."MiB";
  if ($s<1024*1024*1024*1024) return (intval($s/102.4/1024/1024)/10)."GiB";
  if ($s<1024*1024*1024*1024*1024) return (intval($s/102.4/1024/1024/1024)/10)."TiB";
  return "too big to be printed, surely an error...";
}

// Overload this with a proper gettext function when in multilanguage env.
function _l($str) { return $str; } 
function __($str) { echo _($str); } 

function error($str) { 
  echo "ERR: ".$str."\n"; 
}

function not_found() {
  header("HTTP/1.0 404 Not Found");
  echo "<h1>"._("Page Not Found")."</h1>\n<p>"._("The requested page has not been found or an error happened. Please check")."</p>\n";
  exit(); 
}

function text_cut($text, $length = 80) {
   if (strlen($text) > $length)
     $text = substr($text, 0, $length - 3) . '...';
   return $text;
}
