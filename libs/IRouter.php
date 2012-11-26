<?php
require_once LIBS . '/IController.php';

interface IRouter {
  public function __construct(IController $c = NULL);
  public function route($request, $method);
  public function run($uri, $script);
}
