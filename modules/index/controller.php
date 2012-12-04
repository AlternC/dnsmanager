<?php

class indexController extends AController {

  function indexController() {
    
  }

  function indexAction() {
    //    $view["msg"] = _("Welcome to AlternC DNS Manager");
    check_user_identity(false);

    $this->render("index", $view);
  }
}
