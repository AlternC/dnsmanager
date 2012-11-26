<?php
class IndexHooks {

  public function menu(&$menu) {
    $menu[] = array(
		    'url' => '/',
		    'name' => _("Home"),
		    );
  }

  public function ordering_hooks(&$infos) {
    $event = &$infos[0];
    $hooks = &$infos[1];
    /* FIXME: where shall we manage the menu ? (shall we??)
    if ($event == 'menu') {
      $h_index = $hooks['index'];
      unset($hooks['index']);
      $h_users = $hooks['users'];
      unset($hooks['users']);
      $h_servers = $hooks['servers'];
      unset($hooks['servers']);
      $h = $hooks;
      $hooks = array();
      $hooks['index'] = $h_index;
      $hooks['users'] = $h_users;
      $hooks['servers'] = $h_servers;
      $hooks = array_merge($hooks, $h);
    }
    */
  }
}
