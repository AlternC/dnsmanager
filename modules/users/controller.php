<?php

//require_once MODULES . '/servers/lib.php';

class UsersController extends AController {

  /*
   * Users are redirected to "my account"
   * Administrators are shown the user list.
   */
  public function indexAction() {
    check_user_identity();

    if (!is_admin()) {
      header('Location: ' . BASE_URL . 'users/me');
      exit;
    }
    global $db;

    $st = $db->q('SELECT uid, login, email, ' .
		 'IF(enabled, :yes, :no) as enabled, ' .
		 'IF(admin, :yes, :no) as admin FROM users ' .
		 'ORDER BY admin, login',
		 array('yes' => "X", 'no' => ""));
    $users = array();
    while ($data = $st->fetch()) {
      $users[] = array(
		       '_' => $data,
		       'name' => l($data->login, 'users/show/' . $data->uid),
		       'email' => $data->email,
		       'enabled' => $data->enabled,
		       'admin' => $data->admin,
		       );
    }
    Hooks::call('users_list_users', $users);
    foreach ($users as $k => $user) {
      $uid = $user['_']->uid;
      $users[$k]['actions'] = l(_("Edit"), 'users/edit/' . $uid) .
	' | ' . l(_("Delete"), 'users/delete/' . $uid) .
	' | ' . l(_("Connect as"), 'users/impersonate/' . $uid);
    }

    $headers = array(
		     'name' => _('Name'),
		     'email' => _('Email'),
		     'enabled' => _('Enabled'),
		     'admin' => _('Admin.'),
		     );
    Hooks::call('users_list_headers', $headers);
    $headers['actions'] = _('Actions');


    $this->render('list', array('users' => $users, 'headers' => $headers));
  }

  /*
   * Show one user account infos (for admins only)
   */
  public function showAction($params) {
    check_user_identity();

    if (!is_admin())
      not_found();
    global $db;

    $info = trim($params[0]);
    if (is_numeric($info)) {
      $uid = intval($info);
      $user = $db->qone('SELECT uid, login, email, ' .
			'IF(enabled, :yes, :no) as enabled, ' .
			'IF(admin, :yes, :no) as admin, apikey ' .
			'FROM users WHERE uid = :uid',
			array('yes' => _("yes"), 'no' => _("no"), 'uid' => $uid));
    }
    else {
      $login = $info;
      $user = $db->qone('SELECT uid, login, email, ' .
			'IF(enabled, :yes, :no) as enabled, ' .
			'IF(admin, :yes, :no) as admin, apikey ' .
			'FROM users WHERE login = :login',
			array('yes' => _("yes"), 'no' => _("no"), 'login' => $login));
    }
    if ($user == false)
      not_found();

    $this->render('show', array('user' => $user));
  }


  /* Check a form for the user editor */
  private static function verifyForm($data, $op) {
    $errors = array();
    if ($op != 'meedit') {
      if (empty($data['login']))
	$errors[] = _("Please set the login name");
    }

    switch ($op) {
    case 'add':
      if (empty($data['pass']))
	$errors[] = _("Please set a password");
      elseif ($data['pass'] != $data['pass_confirm'])
	$errors[] = _("The passwords are different, please check");
      break;
    case 'edit':
    case 'meedit':
      if ($data['pass'] != $data['pass_confirm'])
	$errors[] = _("The passwords are different, please check");
      break;
    }
    if (empty($data['email']))
      $errors[] = _("The email address is mandatory");
    return $errors;
  }

  /*
   * Add a user (for admins only)
   */
  public function createAction() {

    $errors = array(); // OK if no problem
    
    if (!empty($_POST)) {
      $errors = $this->verifyForm($_POST, 'add');
      // already exist ? 
      
      if (empty($errors)) {
        global $db;
	$args=$db->qone("SELECT * FROM users WHERE email=?",array($_POST['email']),PDO::FETCH_ASSOC);
	if (!$args) {
	  $db->q('INSERT INTO `users` (login, pass, email, enabled, admin, validated) VALUES (?, ?, ?, ?, ?, ?)',
		 array(
		       $_POST['login'],
		       crypt($_POST['pass'],$this->getSalt()),
		       $_POST['email'],
		       1, 0, 0, ) );
	  $uid = $db->lastInsertId();
	  $args = array(
			'uid' => $uid,
			'login' => $_POST['login'],
			'pass' => $_POST['pass'],
			'email' => $_POST['email'],
			'enabled' => ($_POST['enabled'] == 'on'),
			'admin' => ($_POST['admin'] == 'on'),
			);
	  Hooks::call('users_add', $args);
	} 
	// Send a confirmation mail 
	$key=substr(md5(HASH_KEY."-".$args["uid"]),0,8);
	$to      = $args["email"];
	$subject = _("Your account on AlternC DNS Manager");
	$message = sprintf(_("
Hi,

Your account on AlternC DNS Manager has just been created.

Please click the link below to activate it:

%s

If you didn't expect this message, please ignore it

--
Regards,

AlternC's technical team.
"),FULL_URL."users/validate?id=".$args["uid"]."&key=".$key);

	$headers = 'From: '.MAIL_FROMNAME.' <'.MAIL_FROM.'>'. "\r\n" .
	  'Reply-To: '.MAIL_FROM. "\r\n" .
	  'Content-type: text/plain; charset=utf-8' . "\r\n" .
	  'X-Mailer: PHP/' . phpversion();
	
	mail($to, $subject, $message, $headers);
	
        // Message + redirection
	$this->render("createok");

	//	header('Location: ' . BASE_URL . 'users/createok/' . $uid . '?msg=' . _("Your account has beend successfully created. Please check your mail to validate it."));
	exit;
      } else {
	$data=array("login"=>$_POST['login'], "email" => $_POST['email']);
	$this->render('createform',array("errors"=>$errors, "data" => $data));
	return;
      }
    } else {
      header("Location: /");
      exit();
    }
  }

    /*
     * Account Created, show a message
     */
    public function createokAction() {
      render("createok");
    }


    /*
     * Email Validation 
     */
    public function validateAction() {
      $errors = array(); // OK if no problem
      global $db;
      $me=$db->qone('SELECT * FROM `users` WHERE uid=?;', array(intval($_GET['id'])) );
      if (!$me) {
	$errors[]=_("Your account has not been found, please check the link");
	$this->render("validate",array("errors" => $errors));
	exit();
      }
      if ($_GET["key"]!=substr(md5(HASH_KEY."-".$_GET["id"]),0,8)) {
	$errors[]=_("The key is missing or not correct, please check");
	$this->render("validate",array("errors" => $errors));
	exit();	
      }
      $db->qone("UPDATE users SET validate=1 WHERE uid=?",array(intval($_GET['id'])));
      $errors[]=_("Your account has been validated, please login to use our services");
      $this->render("validate",array("errors" => $errors));
    }

  /*
   * Add a user (for anonymous only)
   */
  public function addAction() {
    check_user_identity();

    if (!is_admin())
      not_found();

    $errors = array(); // OK if no problem

    if (!empty($_POST)) {
      $errors = $this->verifyForm($_POST, 'add');


      if (empty($errors)) {
        global $db;
        $db->q('INSERT INTO `users` (login, pass, email, enabled, admin, validated) VALUES(?, ?, ?, ?, ?, ?)',
               array(
                     $_POST['login'],
                     crypt($_POST['pass'],$this->getSalt()),
                     $_POST['email'],
                     ($_POST['enabled'] == 'on') ? 1 : 0,
                     ($_POST['admin'] == 'on') ? 1 : 0,
                     ($_POST['validated'] == 'on') ? 1 : 0
                     )
               );
	$uid = $db->lastInsertId();
	$args = array(
		      'uid' => $uid,
		      'login' => $_POST['login'],
		      'pass' => $_POST['pass'],
		      'email' => $_POST['email'],
		      'enabled' => ($_POST['enabled'] == 'on'),
		      'admin' => ($_POST['admin'] == 'on'),
		      'validated' => ($_POST['validated'] == 'on'),
		      );
	Hooks::call('users_add', $args);

        // Message + redirection
	header('Location: ' . BASE_URL . 'users/show/' . $uid . '?msg=' . _("Ajout OK..."));
	exit;
      }
    }





    /*
     * Valeurs pour pré-remplir le formulaire
     *
     * Deux cas possibles...
     * 1/ On vient d'arriver sur la page ( empty($_POST) ) :
     * on pré-rempli le formulaire avec... rien (nouvel utilisateur)
     *
     * 2/ On à validé le formulaire, mais il y a une erreur :
     * on pré-rempli le formulaire avec les données de la saisie.
     */
    $form_data = (empty($_POST)) ? array() : $_POST; 

    $this->render('form', array('op' => 'add', 'data' => $form_data, 'errors' => $errors));
  }


  /*
   * Edit a user (admins only)
   */
  public function editAction($params) {
    check_user_identity();

    if (!is_admin())
      not_found();
    global $db;
    $uid = intval($params[0]);
    $user = $db->qone('SELECT uid, login, email, enabled, admin, validated FROM users WHERE uid = ?', array($uid));

    if ($user == false)
      not_found();

    $errors = array(); 

    if (!empty($_POST)) {
      $errors = self::verifyForm($_POST, 'edit');

      if (empty($errors)) {
        $db->q('UPDATE users SET login = ?, email = ?, enabled = ?, admin = ?, validated= ? WHERE uid = ?',
               array(
                     $_POST['login'],
                     $_POST['email'],
                     ($_POST['enabled'] == 'on') ? 1 : 0,
                     ($_POST['admin'] == 'on') ? 1 : 0,
                     ($_POST['validated'] == 'on') ? 1 : 0,
		     $user->uid,
                     )
               );

	$old_user = $user;
	$user = $db->qone('SELECT uid, login, email, enabled, admin, validated FROM users WHERE uid = ?', array($user->uid));
	$args = array('old_user' => $old_user, 'new_user' => $user);
	Hooks::call('users_edit', $args);

	if (!empty($_POST['pass'])) {
	  if (!empty($_POST['notify_new_passwd'])) {
	    $this->mail_notify_new_passwd($user->email, $user->login, $_POST['pass']);
	  }
	  if (!empty($_POST['notify_new_account'])) {
	    $this->mail_notify_new_account($user->email, $user->login, $_POST['pass']);
	  }

	  $db->q('UPDATE users SET pass = ? WHERE uid = ?', array(crypt($_POST['pass'],$this->getSalt()), $user->uid));

	  $args = array('uid' => $user->uid, 'login' => $user->login, 'pass' => $_POST['pass']);
	  Hooks::call('users_edit_pass', $args);
	}

        // Message + redirection
	header('Location: ' . BASE_URL . 'users/show/' . $user->uid . '?msg=' . _("Mise à jour OK..."));
	exit;
      }
    }

    /*
     * Valeurs pour pré-remplir le formulaire
     *
     * Deux cas possibles...
     * 1/ On vient d'arriver sur la page ( empty($_POST) ) :
     * on pré-rempli le formulaire avec les données de l'utilisateur
     *
     * 2/ On à validé le formulaire, mais il y a une erreur :
     * on pré-rempli le formulaire avec les données de la saisie.
     */

    if (empty($_POST)) {
      $form_data = get_object_vars($user); // get_object_vars : stdClass -> array
    }
    else {
      $form_data = $_POST;
    }

    $this->render('form', array('op' => 'edit', 'login' => $user->login, 'data' => $form_data, 'errors' => $errors));
  }


  /*
   * Delete a user (admin only)
   */
  public function deleteAction($params) {
    check_user_identity();

    if (!is_admin())
      not_found();
    global $db;
    $uid = intval($params[0]);
    $user = $db->qone('SELECT uid, login, email, enabled, admin FROM users WHERE uid = ?', array($uid));
    if ($user == false)
      not_found();

    if (!empty($_POST['op'])) {
      if ($_POST['op'] == _("Yes, Delete")) {
	$db->q('DELETE FROM users WHERE uid = ?', array($uid));
	$args = array($user);
	Hooks::call('users_delete', $args);
        // Message + redirection
	header('Location: ' . BASE_URL . 'users?msg=' . sprintf(_("User %s successfully deleted"), $user->login));
	exit;
      }
      else {
        // Message + redirection
	header('Location: ' . BASE_URL . 'users/show/' . $uid . '?msg=' . _("Nothing has been deleted"));
	exit;
      }
    }
    $this->render('delete', array('user' => $user));
  }

  /*
   * Connect as another user (for admins only)
   */
  public function impersonateAction($params) {
    check_user_identity();

    if (!is_admin())
      not_found();
    global $db;
    $uid = intval($params[0]);
    $user = $db->qone('SELECT uid, login, email, enabled, admin FROM users WHERE uid = ?', array($uid));
    if ($user == false)
      not_found();
    setcookie('impersonate', $user->uid, 0, '/');
    header('Location: ' . BASE_URL);
    exit;
  }


  /*
   * Leave the connect as a user 
   */
  public function stopimpersonateAction() {
    check_user_identity();

    setcookie('impersonate', '0', 1, '/');
    header('Location: ' . BASE_URL .'users');
    exit;
  }


  /*
   * My Account
   */
  public function meAction($params) {
    global $db;
    check_user_identity();

    $uid=$GLOBALS['me']['uid'];

    $user = $db->qone('SELECT * FROM users WHERE uid = ?',
                      array($GLOBALS['me']['uid']));
    if ($user == false)
      not_found();

    if ($params[0] == 'edit') {
      $errors = array();

      if (!empty($_POST)) {
	$errors = self::verifyForm($_POST, 'meedit');

	if (empty($errors)) {
	  $db->q('UPDATE users SET email = ? WHERE uid = ?', array($_POST['email'], $user->uid));
	  $old_user = $user;
	  $user = $db->qone('SELECT uid, login, email, enabled, admin FROM users WHERE uid = ?', array($user->uid));
	  $args = array('old_user' => $old_user, 'new_user' => $user);
	  Hooks::call('users_edit', $args);

	  if (!empty($_POST['pass'])) {
	    $db->q('UPDATE users SET pass = ? WHERE uid = ?', array(crypt($_POST['pass'],$this->getSalt()), $user->uid));
	    $args = array('uid' => $user->uid, 'login' => $user->login, 'pass' => $_POST['pass']);
	    Hooks::call('users_edit_pass', $args);
	  }

	  // Message + redirection
	  header('Location: ' . BASE_URL . 'users/me?msg=' . _("Mise à jour OK..."));
	  exit;
	}
      }

      /*
       * Valeurs pour pré-remplir le formulaire
       *
       * Deux cas possibles...
       * 1/ On vient d'arriver sur la page ( empty($_POST) ) :
       * on pré-rempli le formulaire avec les données de l'utilisateur
       *
       * 2/ On à validé le formulaire, mais il y a une erreur :
       * on pré-rempli le formulaire avec les données de la saisie.
       */

      if (empty($_POST)) {
	$form_data = get_object_vars($user); // get_object_vars : stdClass -> array
      }
      else {
	$form_data = $_POST;
      }

      $this->render('form', array('op' => 'meedit', 'data' => $form_data, 'errors' => $errors));
    }
    else
      $this->render('me', array('user' => $user, 'contacts' => $contacts));
  }

  public function logoutAction() {
    $realm = 'AlternC DNS Manager';
    header('WWW-Authenticate: Basic realm="' . $realm . '"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Logout';
    exit;
  }




private function mail_notify_new_passwd($email, $login, $pass) {
  $to      = $email;
  $subject = _("Password change on AlternC DNS Manager");
  $message = sprintf(_("
Bonjour,

le mot de passe de votre compte sur le DNS Manager AlternC vient d'être modifié.

Vous pouvez y accéder à l'adresse : %s

Votre nom d'utilisateur est : %s
Votre mot de passe est : %s

--
Cordialement,

L'équipe technique
"),FULL_URL,$login,$pass);

  $headers = 'From: '.MAIL_FROMNAME.' <'.MAIL_FROM.'>'. "\r\n" .
    'Reply-To: '.MAIL_FROM. "\r\n" .
    'Content-type: text/plain; charset=utf-8' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

  mail($to, $subject, $message, $headers);
}

private function mail_notify_new_account($email, $login, $pass) {
  $to      = $email;
  $subject = _("Your account on AlternC DNS Manager");
  $message = sprintf(_("
Bonjour,

votre compte sur le DNS Manager AlternC vient d'être créé.

Vous pouvez y accéder à l'adresse : %s

Votre nom d'utilisateur est : %s
Votre mot de passe est : %s

Nous vous invitons à le modifier en cliquant sur 'Mon compte' puis 'Modifier'

--
Cordialement,

L'équipe technique 
"),FULL_URL,$login,$pass);

  $headers = 'From: '.MAIL_FROMNAME.' <'.MAIL_FROM.'>'. "\r\n" .
    'Reply-To: '.MAIL_FROM. "\r\n" .
    'Content-type: text/plain; charset=utf-8' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

  mail($to, $subject, $message, $headers);
}


/** Returns a hash for the crypt password hashing function.
 * as of now, we use php5.3 almost best hashing function: SHA-256 with 1000 rounds and a random 16 chars salt.
 */
private function getSalt() {
  $salt = substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 16);
  return '$5$rounds=1000$'.$salt.'$';
}



}