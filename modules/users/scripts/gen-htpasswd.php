#!/usr/bin/env php
<?php
require_once __DIR__ . '/../../../config.inc.php';
require_once LIBS . '/Db.php';

try {
  $db = new Db($db);
}
catch (Exception $e) {
  __("Erreur lors de la connexion à la base de données.") . "\n";
  echo $e->getMessage() . "\n";
  exit(1);
}

$users = $db->qlist("SELECT login, pass FROM users WHERE pass IS NOT NULL AND pass != '' AND enabled = 1");
foreach ($users as $user) {
  echo $user->login . ':' . $user->pass . "\n";
}
