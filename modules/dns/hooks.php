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


  public function users_me() {
    $this->render("me");
  }

}
