<?php
function htpasswd_add($htpasswd, $login, $passwd) {
  file_put_contents($htpasswd, $login . ':' . $passwd . "\n", FILE_APPEND);
}

function htpasswd_delete($htpasswd, $login) {
  htpasswd_update('delete', $htpasswd, $login, '');
}

function htpasswd_updatepasswd($htpasswd, $login, $passwd) {
  htpasswd_update('passwd', $htpasswd, $login, $passwd);
}

function htpasswd_updatelogin($htpasswd, $old_login, $new_login) {
  htpasswd_update('login', $htpasswd, $old_login, $new_login);
}

function htpasswd_update($type, $htpasswd, $login, $info) {
  $new_file = '';

  $file = file_get_contents($htpasswd);
  $found=false;

  foreach (explode("\n", $file) as $line) {
    if (empty($line))
      continue;

    list($l, $p) = explode(':', $line, 2);
    if ($l == $login) {
      switch ($type) {
      case 'login':
	$new_file .= $info . ':' . $p . "\n";
	$found=true;
	break;
      case 'passwd':
	$new_file .= $l . ':' . $info . "\n";
	$found=true;
	break;
      case 'delete':
	break;
      default:
	$new_file .= $l . ':' . $p . "\n";
	break;
      }
    }
    else
      $new_file .= $l . ':' . $p . "\n";
  }
  if (!$found) {
    if ($type=="passwd") {
      $new_file .= $login. ':' . $info . "\n";
    }
  }
  file_put_contents($htpasswd, $new_file);
}
