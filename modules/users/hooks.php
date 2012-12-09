<?php

require_once __DIR__ . '/libs/htpasswd-utils.php';
require_once __DIR__ . '/lib.php';

class UsersHooks  extends AHooks {

  /*
   * Add menu links
   */
  public function menu(&$menu) {
    if (is_admin()) {
      $menu[] = array(
                      'url' => '/users/',
                      'name' => _("User Management"),
                      );
    }
    /* TODO : allow a password change (so give a "my account" entry here)
    $menu[] = array(
                    'url' => '/users/me',
                    'name' => _("My Account"),
                    );
    */
  }

  /*
   * Fonctionnalité d'"imitation".
   */
  public function post_check_user_identity(&$me) {
    if (is_admin() && !empty($_COOKIE['impersonate'])) {
      global $db;
      $query = 'SELECT uid, login, email, admin '
        . 'FROM users '
        . 'WHERE uid = ?';
      $user = $db->qone($query, array(intval($_COOKIE['impersonate'])), PDO::FETCH_ASSOC);

      /*
       * Si l'utilisateur pour lequel on veut se faire passer existe bien,
       * on remplace $me par l'utilisateur tout en indiquant dans 'impersonator' le véritable utilisateur.
       */
      if ($user != false) {
        $user['impersonator'] = $me;
        $me = $user;
      }
    }
  }

  /*
   * Pour indiquer à l'utilisateur qu'il utilise la fonctionnalité d'"imitation".
   */
  public function content_top(&$html) {
    if (!empty($GLOBALS['me']['impersonator'])) {
      $msg = sprintf(_("You are %s and you are connected as %s.");
                     $GLOBALS['me']['impersonator']['login'],
                     $GLOBALS['me']['login']) . ' ' .
        '<a href="' . BASE_URL . 'users/stopimpersonate">' . _("Go back to my admin account identity.") . '</a>';
      $html[] = '<p>' . $msg . '</p>';
    }
  }

  /*
   * Mise à jour d'un .htaccess
   */
  public function users_add($user) {
    htpasswd_add(__DIR__ . '/htpasswd.users', $user['login'], crypt($user['pass']));
  }

  public function users_edit($args) {
    if ($args['old_user']->login != $args['new_user']->login) {
      htpasswd_updatelogin(__DIR__ . '/htpasswd.users',
			   $args['old_user']->login,
			   $args['new_user']->login);
    }
  }

  public function users_edit_pass($args) {
    htpasswd_updatepasswd(__DIR__ . '/htpasswd.users',
			  $args['login'],
			  crypt($args['pass']));
  }

  public function users_delete($args) {
    $user = $args[0];
    htpasswd_delete(__DIR__ . '/htpasswd.users',
		    $user->login);
  }

  public function index_indexview() {
    $this->render('home');
  }

}
