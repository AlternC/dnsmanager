<?php
require_once LIBS . '/IRouter.php';

/**
 * La classe SimpleRouter permet de router les requêtes des utilisateurs vers la bonne action à réaliser.
 *
 * Le fonctionnement de ce routeur est assez simple :
 * @TODO à décrire.
 */
class SimpleRouter implements IRouter {
  private $default_class;
  private $default_action;

  public function __construct(IController $c = NULL) {
    
  }

  public function route($request, $action) {
    if ($request == '/') {
      $infos = explode(':', $action, 2);
      $this->default_class = $infos[0];
      $this->default_action = $infos[1];
    }
  }

  private function getURI($uri, $script) {
    $dir = dirname($script);
    if ($dir == '/')
      return $uri;
    return preg_replace('`^' . $dir . '`', '', $uri);
  }

  public function run($uri, $script) {
    $request = $this->getURI($uri, $script);
    $path = $request;
    if (strpos($request,"?")!==false) {
      $path=substr($request,0,strpos($request,"?"));
    }

    @list($class,$action,$params)=explode("/",trim($path,"/"),3);
    if (isset($params)) {
      $params=explode("/",$params);
    } else {
      $params=array();
    }
    //echo "class:$class action:$action\n"; 
    if (!preg_match("#^[0-9a-z]*$#",$class) || !preg_match("#^[0-9a-z]*$#",$action)) {
      not_found();
    }

    if (empty($class))
      $class = $this->default_class;
    if (empty($action))
      $action = $this->default_action;


    // I'm naming controller and actions like Zend do ;) 
    $classfile=strtolower($class)."Controller";
    $actionmethod=strtolower($action)."Action";

    // Adds here requires for class hierarchy ...
    require_once LIBS . "/AController.php";

    $controller_file = MODULES . '/' . strtolower($class) . '/controller.php';
    // Now we have class and action and they look nice. Let's instanciate them if possible 
    if (!file_exists($controller_file)) {
      not_found();
    }


    // We prepare the view array for the rendering of data: 
    $view=array();
    //$view["me"]=$me;
    $view["class"]=$class;
    $view["action"]=$action;

    define("VIEW_DIR", ROOT . "/view");
    // We define the view here because the controller class may need it in its constructor ;)


    require_once($controller_file);
    $$classfile=new $classfile();

    if (!method_exists($$classfile,$actionmethod)) {
      error("Method not found");
      not_found();
    }

    // We launch the requested action.
    // in "<class>Controller" class, we launch "<action>Action" method : 
    $$classfile->$actionmethod($params);
    // This action will either do a redirect(); to point to another page, 
    // or do a render($viewname) to render a view 
  }
}
