<?php

// Automatically detect the language from the HTTP_ACCEPT_LANGUAGE and 
// set gettext accordingly ...

$available_lang=array("en"=>"en_US","fr"=>"fr_FR");

if (!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
  $_SERVER["HTTP_ACCEPT_LANGUAGE"]="en_US";
}

if (isset($_COOKIE["lang"])) { // Cookie from previous usage.
  $lang=$_COOKIE["lang"];
}

if (isset($_REQUEST["setlang"])) { // Explicit language switching...
  $lang=$_REQUEST["setlang"];
}

if (!(isset($lang))) {  // Use the browser first preferred language
  $lang=strtolower(substr(trim($_SERVER["HTTP_ACCEPT_LANGUAGE"]),0,2));
}
$lang=substr($lang,0,2);

if (!isset($available_lang[$lang])) {
  list($lang)=each($available_lang);
} 

$lang=$available_lang[$lang];

setcookie("lang",$lang,0,"/");

/* Language ok, set the locale environment */
$langpath = bindtextdomain("default", LOCALES);
$langcharset=$lang.".UTF-8";
putenv("LC_MESSAGES=".$langcharset);
putenv("LANG=".$langcharset);
putenv("LANGUAGE=".$langcharset);
setlocale(LC_ALL,$langcharset);
textdomain("default");

bind_textdomain_codeset("default","UTF-8");
