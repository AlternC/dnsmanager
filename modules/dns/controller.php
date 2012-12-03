<?php

require_once(__DIR__.'/libs/constants.php');

class DnsController extends AController {

  public function indexAction() {
    // ...
  }


  /*
   * Show the list of servers of a user : 
   */
  public function serversAction() {
    check_user_identity();
    global $db;

    $uid=$GLOBALS['me']['uid'];
    $st = $db->q('SELECT * FROM servers WHERE user=?',array($uid));
    $servers = array();
    while ($data = $st->fetch()) {
      $servers[] = array(
		       '_' => $data,
		       'name' => l($data->hostname, 'dns/show/' . $data->id),
		       'fqdn' => $data->fqdn,
		       'ip' => $data->ip,
		       'ssl' => $data->hasssl,
		       'enabled' => ($data->enabled)?_("Yes"):_("No"),
		       'lastcount' => "<span class=\"".$this->lastcountstatus($data->lastcount)."\">".$data->lastcount."</span>",
		       'updated' => $data->updated,
		       );
    }
    if (count($servers)==0) {
      
    }
    Hooks::call('dns_list_servers', $servers);
    foreach ($servers as $k => $server) {
      $uid = $server['_']->id;
      $servers[$k]['actions'] = l(_("Edit"), 'dns/edit/' . $uid) .
	' | ' . l(_("Delete"), 'dns/delete/' . $uid)
	;
    }

    $headers = array(
		     'name' => _('Hostname'),
		     'fqdn' => _('Complete Hostname (FQDN)'),
		     'ip' => _('IP Address'),
		     'enabled' => _('Enabled'),
		     'lastcount' => _('Last Domain Count'),
		     'updated' => _('Updated on'),
		     );
    Hooks::call('dns_list_headers', $headers);
    $headers['actions'] = _('Actions');

    $this->render('list', array('servers' => $servers, 'headers' => $headers));
  }



  /*
   * Show one server of a user: 
   */
  public function showAction($params) {
    check_user_identity();
    global $db;

    $id = intval($params[0]);
    $uid=$GLOBALS['me']['uid'];
    $st = $db->q('SELECT * FROM servers WHERE user=? AND id=?',array($uid,$id));
    $servers = array();
    $server = $st->fetch();
    if (!$server) {
      $errors[]=_("Server not found");
      $this->render('list', array('errors'=>$errors));      
      return;
    }

    // List the zones for this server
    $zones = $db->qlist('SELECT * FROM zones WHERE server=? ORDER BY zone',array($id));

    // List the last log for this server
    $st = $db->q("SELECT difflog.* FROM difflog  WHERE difflog.server=".$id." ORDER BY difflog.datec DESC LIMIT 0,50;");
    $aaction=array(
		   0 => _("Timeout connecting to the server"),
		   1 => _("Zone added"),
		   2 => _("Zone added but disabled (conflict)"),
		   3 => _("Zone deleted"),
		   4 => _("Zone enabled (no conflict)"),
		   );
    while ($data = $st->fetch()) {
      $diff[] = array(
		      '_' => $data,
		      'action' => $aaction[$data->action],
		      'zone' => $data->zone,
		      'datec' => $data->datec,
		      );
    }

    $diffheaders = array(
		     'action' => _('Event'),
		     'zone' => _('Zone'),
		     'datec' => _('Date of the event'),
		     );


    $this->render('show', array('server' => $server, 'zones' => $zones, 'diff' => $diff, 'diffheaders' => $diffheaders));
  }
    

  public function addAction() {
    check_user_identity();
    
    $errors = array(); // OK if no problem
    $uid=$GLOBALS['me']['uid'];

    if (!empty($_POST)) {
      $errors = $this->verifyForm($_POST, 'add');
      
      if (empty($errors)) {
        global $db;
        $db->q('INSERT INTO `servers` (user, hostname, fqdn, login, password, ip, hasssl, enabled, created) VALUES(?, ?, ?, ?, ?, ?, ?, ?, NOW() )',
               array( $uid, $_POST['hostname'], $_POST['fqdn'], $_POST['login'], $_POST['password'], $_POST['ip'],  ($_POST['hasssl'] == 'on') ? 1 : 0,   ($_POST['enabled'] == 'on') ? 1 : 0  )
	       );
	$id = $db->lastInsertId();
	if (!$id) {
	  $errors[]=_("An error occurred, please try again later");	  
	} else {
	  $args = array(
			'id' => $id,
			'hostname' => $_POST['hostname'],
			'fqdn' => $_POST['fqdn'],
			'login' => $_POST['login'],
			'password' => $_POST['password'],
			'ip' => $_POST['ip'],
			'hasssl' => ($_POST['hasssl'] == 'on'),
			'enabled' => ($_POST['enabled'] == 'on'),
			);
	  Hooks::call('dns_add', $args);
	  // Message + redirection
	  header('Location: ' . BASE_URL . 'dns/show/' . $id . '?msg=' . _("Server Added..."));
	  exit;
	} 
      }       
    }

    /*
     * Valeurs pour pré-remplir le formulaire
     *
     * Deux cas possibles...
     * 1/ On vient d'arriver sur la page ( empty($_POST) ):
     * on pré-rempli le formulaire avec... rien (nouvel utilisateur)
     *
     * 2/ On à validé le formulaire, mais il y a une erreur:
     * on pré-rempli le formulaire avec les données de la saisie.
     */
    $form_data = (empty($_POST)) ? array() : $_POST; 

    $this->render('form', array('op' => 'add', 'data' => $form_data, 'errors' => $errors));
  }



  /*
   * Edit a server
   */
  public function editAction($params) {
    check_user_identity();

    global $db;
    $id = intval($params[0]);
    $uid=$GLOBALS['me']['uid'];

    $server = $db->qone('SELECT * FROM servers WHERE user=? AND id=?', array($uid,$id));

    if ($server == false)
      not_found();

    $errors = array(); 

    if (!empty($_POST)) {
      $errors = self::verifyForm($_POST, 'edit');

      if (empty($errors)) {
        $db->q('UPDATE servers SET fqdn=?, hostname=?, login=?, password=?, ip=?, hasssl=?, enabled=? WHERE id=?',
               array(
                     $_POST['fqdn'],
                     $_POST['hostname'],
                     $_POST['login'],
                     $_POST['password'],
                     $_POST['ip'],
                     ($_POST['hasssl'] == 'on') ? 1 : 0,
                     ($_POST['enabled'] == 'on') ? 1 : 0,
		     $server->id,
                     )
               );

	/*
	$old_server = $server;
	$server = $db->qone('SELECT uid, login, email, enabled, admin, validated FROM servers WHERE uid = ?', array($server->uid));
	$args = array('old_server' => $old_server, 'new_server' => $server);
	Hooks::call('servers_edit', $args);
	*/

        // Message + redirection
	header('Location: ' . BASE_URL . 'dns/show/' . $server->id . '?msg=' . _("Server successfully updated"));
	exit;
      }
    }

    /*
     * Valeurs pour pré-remplir le formulaire
     *
     * Deux cas possibles...
     * 1/ On vient d'arriver sur la page ( empty($_POST) ):
     * on pré-rempli le formulaire avec les données de l'utilisateur
     *
     * 2/ On à validé le formulaire, mais il y a une erreur:
     * on pré-rempli le formulaire avec les données de la saisie.
     */

    if (empty($_POST)) {
      $form_data = get_object_vars($server); // get_object_vars : stdClass -> array
    }
    else {
      $form_data = $_POST;
    }

    $this->render('form', array('op' => 'edit', 'hostname' => $server->hostname, 'data' => $form_data, 'errors' => $errors));
  }


  /*
   * Delete a server
   */
  public function deleteAction($params) {
    check_user_identity();

    global $db;
    $id = intval($params[0]);
    $uid=$GLOBALS['me']['uid'];
    $server = $db->qone('SELECT * FROM servers WHERE user=? AND id=?', array($uid,$id));
    if ($server == false)
      not_found();

    if (!empty($_POST['op'])) {
	$db->q('DELETE FROM servers WHERE id=?', array($id));
	$args = array($server);
	Hooks::call('server_delete', $args);
        // Message + redirection
	header('Location: ' . BASE_URL . 'dns/servers?msg=' . sprintf(_("Server %s successfully deleted"), $server->hostname));
	exit;
    }
    $this->render('delete', array('server' => $server));
  }



  /*
   * Show the last log of a user:
   */
  public function logAction() {
    check_user_identity();
    global $db;

    $uid=intval($GLOBALS['me']['uid']);
    $offset=intval($_REQUEST["offset"]);
    if ($offset<0) $offset=0; 
    $count=100; //intval($_REQUEST["count"]);
    $st = $db->q("SELECT difflog.*, servers.fqdn FROM difflog, servers WHERE servers.id=difflog.server AND servers.user=".$uid." ORDER BY difflog.datec DESC LIMIT ".$offset.",".$count.";");
    $servers = array();
    $aaction=array(
		   0 => _("Timeout connecting to the server"),
		   1 => _("Zone added"),
		   2 => _("Zone added but disabled (conflict)"),
		   3 => _("Zone deleted"),
		   4 => _("Zone enabled (no conflict)"),
		   );
    while ($data = $st->fetch()) {
      $diff[] = array(
		       '_' => $data,
		       'fqdn' => l($data->fqdn, 'dns/show/' . $data->server),
		       'action' => $aaction[$data->action],
		       'zone' => $data->zone,
		       'datec' => $data->datec,
		       );
    }
    if (count($diff)==0) {
      
    }
    /*
    foreach ($diff as $k => $server) {
      $uid = $server['_']->id;
      $servers[$k]['actions'] = l(_("Edit"), 'dns/edit/' . $uid) .
	' | ' . l(_("Delete"), 'dns/delete/' . $uid)
	;
    }
    */

    $headers = array(
		     'fqdn' => _('Server FQDN'),
		     'action' => _('Event'),
		     'zone' => _('Zone'),
		     'datec' => _('Date of the event'),
		     );
    //    $headers['actions'] = _('Actions');
    $this->render('diff', array('diff' => $diff, 'headers' => $headers));
  }






  /* Check a form for the user editor */
  private static function verifyForm($data, $op) {
    $errors = array();
    if (empty($data['hostname']))
	$errors[] = _("Please set the hostname of the server. This should be unique between all AlternC DNS Manager accounts");
    if (empty($data['ip']))
	$errors[] = _("Please set the public IPv4 address of the server.");

    // Todo : check the unicity of FQDN and HOSTNAME and IP on ADD, and EDIT (with exclusion of current one)
    switch ($op) {
    case 'add':
      /*
      if (empty($data['pass']))
	$errors[] = _("Please set a password");
      elseif ($data['pass'] != $data['pass_confirm'])
	$errors[] = _("The passwords are different, please check");
      */
      break;
    case 'edit':
      /*
      if ($data['pass'] != $data['pass_confirm'])
	$errors[] = _("The passwords are different, please check");
      */
      break;
    }
    return $errors;
  }


  private function lastcountstatus($count) {
    if ($count<0) return "error";
    if ($count==0) return "warning";
    return "information";
  }


} /* Controller */
