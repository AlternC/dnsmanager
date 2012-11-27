<?php
class DnsHooks extends AHooks {
  public function menu(&$menu) {
    $menu[] = array(
		    'url' => '/dns/servers',
		    'name' => _("My Servers"),
		    );
  }


}
