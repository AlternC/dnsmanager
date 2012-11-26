<?php
require_once __DIR__ . '/../../config.inc.php';
require_once LIBS . '/Db.php';
/*
try {
  $db = new Db($db);
}
catch (Exception $e) {
  echo _('Connexion à la base de données échouée :') . ' ' . $e->getMessage() . "\n";
  exit;
}
*/

class Users {
  public static function auth($_login, $_pass) {
    global $db;

    $pass = $db->qonefield('SELECT pass FROM users WHERE login = ?', array($_login));
    $pass = crypt($_pass, $pass);
  
    $query = 'SELECT uid, login, email, admin '
      . 'FROM users '
      . 'WHERE login = ? AND pass = ? AND enabled = 1';
    return $db->qone($query, array($_login, $pass), PDO::FETCH_ASSOC);
  }

  public static function getAllUsers($mode = '') {
    global $db;

    if ($mode == 'assoc')
      $users = $db->qassoc('SELECT uid, login FROM users ORDER BY login ASC');
    elseif ($mode == 'listone')
      $users = $db->qlistone("SELECT login FROM users ORDER BY login ASC");
    else
      $users = $db->qlist('SELECT uid, login, email, enabled, admin FROM users ORDER BY login ASC');

    return $users;
  }

  public static function getAllCustomers($mode = '') {
    global $db;

    if ($mode == 'assoc')
      $users = $db->qassoc('SELECT uid, login FROM users WHERE admin=0 ORDER BY login ASC');
    elseif ($mode == 'listone')
      $users = $db->qlistone("SELECT login FROM users WHERE admin=0 ORDER BY login ASC");
    else
      $users = $db->qlist('SELECT uid, login, email, enabled, admin FROM users WHERE admin=0 ORDER BY login ASC');

    return $users;
  }

  public static function addUser($informations = array()) {
    global $db;

    $login = (empty($informations['login'])) ? '' : (string)$informations['login'];
    $pass = (empty($informations['pass'])) ? '' :  crypt($informations['pass']);
    $email = (empty($informations['email'])) ? '' :  (string)$informations['email'];
    $enabled = (!empty($informations['enabled']) && in_array($informations['enabled'], array(true, 'on', 1))) ? true : false;
    $admin = (!empty($informations['admin']) && in_array($informations['admin'], array(true, 'on', 1))) ? true : false;

    if (empty($login))
      return 0;

    $db->q('INSERT INTO `users` (uid, login, pass, email, enabled, admin) VALUES(NULL, ?, ?, ?, ?, ?)',
	   array(
		 $login,
		 crypt($pass),
		 $email,
		 ($enabled) ? 1 : 0,
		 ($admin) ? 1 : 0,
		 )
	   );

    return $db->lastInsertId();
  }
}
