#!/usr/bin/env php
<?php
require_once __DIR__ . '/../lib.php';
if (empty($argv[1]) || empty($argv[2])) {
  echo "login .. pass\n";
  exit;
}

$informations = array(
		      'login' => $argv[1],
		      'pass' => $argv[2],
		      'admin' => true,
		      'enabled' => true,
		      );
Users::addUser($informations);
