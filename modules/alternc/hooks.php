<?php

class AlterncHooks  extends AHooks {

  /*
   * Add menu links
   */
  public function menu(&$menu) {
    /*
    if (is_admin()) {
      $menu[] = array(
                      'url' => '/users/',
                      'name' => _("User Management"),
                      );
    }
    */
  }

  public function index_indexview() {
    $this->render('home');
  }

}
