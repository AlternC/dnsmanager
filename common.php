<?php

// please REQUIRE_ONCE that file from any startup script that should use the microframework
// whether they are webpages or cli or daemons.

define("OMKT_VERSION","1.0");

// Configuration
require_once __DIR__ . '/config.inc.php';

// Some libraries
require_once LIBS . '/utils.php';
require_once LIBS . '/html_utils.php';
require_once LIBS . '/users.php';
require_once LIBS . '/Db.php';
require_once LIBS . '/SimpleRouter.php';
require_once LIBS . '/Hooks.php';
require_once LIBS . '/lang.php';

// Default database
try {
  $db = new Db($db);
}
catch (Exception $e) {
  echo _('Cannot connect to the database:') . ' ' . $e->getMessage() . "\n";
  exit;
}
