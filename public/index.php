<?php

// Configuration
require_once __DIR__ . '/../common.php';


try {
  // Router
  $r = new SimpleRouter();
  $r->route('/', 'index:index'); // Default route
  $r->run($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
}
catch (Exception $e) {
  echo _('Error:') . ' ' . $e->getMessage() . "\n";
}

/*
   Globals used in this application : 
   $me[] = array of key=>value pair : the current user from the User table.
   $class = name of the part of the application that we are executing (first part of the url which is http://domain.tld/class/action
   $action = the action requested. May be "" (=="list") or "edit", "doedit", "add", "doadd", "delete", "dodelete" ...
*/
