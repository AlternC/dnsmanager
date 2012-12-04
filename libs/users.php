<?php

function check_user_identity($mandatory=true) {
  $realm = 'AlternC DNS Manager';

  if ($mandatory && !isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="' . $realm . '"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Please authenticate';
    exit;
  } 

  /*
   * Autre exemple de hook possible :
   * Hooks::call('pre_check_user_identity', array($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']));
   */
  if (isset($_SERVER['PHP_AUTH_USER'])) {
    require_once __DIR__ . '/../modules/users/lib.php';
    $GLOBALS["me"] = Users::auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

    if (!$GLOBALS["me"]) {
      header('WWW-Authenticate: Basic realm="' . $realm . '"');
      header('HTTP/1.0 401 Unauthorized');
      echo 'Login or password incorrect, or account disabled';
      exit;
    }
    Hooks::call('post_check_user_identity', $GLOBALS["me"]);
  }

  //  mq("UPDATE user SET lastlogin=NOW() WHERE id='".$GLOBALS["me"]["id"]."';");
}


/* ************************************************************ */
/** Returns TRUE if the current user is an administrator
 */
function is_admin() {
  return (isset($GLOBALS["me"]) && isset($GLOBALS["me"]["admin"]) && $GLOBALS["me"]["admin"]!= 0);
}

