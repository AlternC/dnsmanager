<?php

class indexController extends AController {

  function indexController() {
    
  }

  function indexAction() {
    //    $view["msg"] = _("Welcome to AlternC DNS Manager");
    $this->render("index", $view);
  }
}
