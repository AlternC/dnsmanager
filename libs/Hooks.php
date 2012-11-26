<?php

require_once LIBS . "/AHooks.php";

/**
 * 
 */
class Hooks {
  private static $objects = array();

  private static function getFiles() {
    $files = array();

    foreach (glob(MODULES . '/*/hooks.php') as $file) {
      $parts = explode('/', $file);
      $module_name = $parts[count($parts)-2];
      $files[$module_name] = $file;
    }

    return $files;
  }

  private static function getObjects() {
    if (!empty(self::$objects))
      return self::$objects;

    $files = Hooks::getFiles();

    foreach ($files as $module => $file) {
      require_once $file;
      $class_name = ucfirst($module) . 'Hooks';

      if (class_exists($class_name))
	self::$objects[$module] = new $class_name();
    }

    return self::$objects;
  }

  public static function call($event, array &$args = array()) {
    $objects = Hooks::getObjects();

    $hooks = array();
    foreach ($objects as $module => $obj)
      if (method_exists($obj, $event))
	$hooks[$module] = $obj;

    if ($event != 'ordering_hooks') {
      $infos = array(&$event, &$hooks);
      Hooks::call('ordering_hooks', $infos);
    }

    foreach ($hooks as $obj)
      $obj->$event($args);
  }
}
