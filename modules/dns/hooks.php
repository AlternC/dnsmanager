<?php
class DnsHooks extends AHooks {
  public function menu(&$menu) {
    $menu[] = array(
		    'url' => '/dns/servers',
		    'name' => _("My Servers"),
		    );
    $menu[] = array(
		    'url' => '/dns/log',
		    'name' => _("Server Logs"),
		    );
  }

  public function menu_plus(&$menu) {
    if (is_admin()) {
      $menu[] = array(
		      'url' => '/dns/allservers',
		      'name' => _("All Servers"),
		      );
      $menu[] = array(
		      'url' => '/dns/alllog',
		      'name' => _("All Server Logs"),
		      );
    }
  }


  public function users_me() {
    $this->render("me");
  }

}
