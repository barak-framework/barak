<?php

class ApplicationController {

  const CONTROLLERPATH = "app/controllers/";

  const CONTROLLERSUBNAME = "Controller";

  private $_locals = [];
  private $_render = null;
  private $_redirect_to = null;

  private $_route;

  final public function __construct(ApplicationRoute $route) { // genişletilemez fonksiyon

    $this->_route = $route;

    // router'in localslarını(sayfadan :id, çekmek için), controller'dan gelen localslara yükle ki action içerisinden erişebilesin
    $this->_locals = $route->locals;
  }

  final public function __get($local) { // genişletilemez fonksiyon
    return $this->_locals[$local];
  }

  final public function __set($local, $value) { // genişletilemez fonksiyon
    $this->_locals[$local] = $value;
  }

  final public function __isset($local) { // genişletilemez fonksiyon
    return isset($this->_locals[$local]);
  }

  final public function __unset($local) { // genişletilemez fonksiyon
    unset($this->_locals[$local]);
  }

  final public function render($options) { // genişletilemez fonksiyon
    $this->_render = $options;
  }

  final public function redirect_to($url) { // genişletilemez fonksiyon
    $this->_redirect_to = $url;
  }

  private function _filter($action, $filter_actions) {

    foreach ($filter_actions as $filter_action) {

      if (array_key_exists(0, $filter_action)) {
        $filter_action_name = $filter_action[0];
        if (method_exists($this, $filter_action_name)) {
          if (array_key_exists("only", $filter_action)) {
            if (in_array($action, $filter_action["only"]))
              $this->{$filter_action_name}();
          } elseif (array_key_exists("except", $filter_action)) {
            if (!in_array($action, $filter_action["except"]))
              $this->{$filter_action_name}();
          } elseif (!array_key_exists("only", $filter_action) and !array_key_exists("except", $filter_action)) {
            $this->{$filter_action_name}();
          }
          if ($this->_redirect_to) exit($this->_redirect_to());
          if ($this->_render)      exit($this->_render());
        }
      }

    }
  }

  private function _helpers() {
    ApplicationHelper::load($this->helpers);
  }

  private function _render() {
    $v = new ApplicationView();

    // render template
    if ($this->_route->path) { // have path? for scope, resouce, resouces

      $v->set(["layout" => $this->_route->path]);
      $v->set(["view" => $this->_route->path . "/" . $this->_route->controller, "action" => $this->_route->action]);

    } else { // normal path

      $v->set(["view" => "/" . $this->_route->controller, "action" => $this->_route->action]);

    }

    // controllerin localsları
    if ($this->_locals)
      $v->set(["locals" => $this->_locals]);

    // controllerin renderi
    if ($this->_render)
      $v->set($this->_render);

    echo $v->run();
  }

  private function _redirect_to() {
    exit(header("Location: http://" . $_SERVER['SERVER_NAME'] . "/" . trim($this->_redirect_to, "/"), false, 303));
  }

  private static function _load($file, $path = "") {
    $controller_class = ucwords($file) . self::CONTROLLERSUBNAME;
    $controller_path  = self::CONTROLLERPATH . trim($path,"/") . "/" . $controller_class . '.php';

    if (!file_exists($controller_path))
      throw new Exception("Controller dosyası mevcut değil → " . $controller_path);

    require_once $controller_path;

    if (!class_exists($controller_class))
      throw new Exception("Controller sınıfı yüklenemedi → " . $controller_class);
  }

  private function _run() {

    if (isset($this->helpers)) $this->_helpers();

    if (isset($this->before_actions)) $this->_filter($this->_route->action, $this->before_actions);

    if (method_exists($this, $this->_route->action)) $this->{$this->_route->action}();

    if (!isset($this->_redirect_to)) {
      $main_redirect_to = $this->_redirect_to;
      $this->_redirect_to = null;
    }

    if (!isset($this->_render)) {
      $main_render = $this->_render;
      $this->_render = null;
    }

    if (isset($this->after_actions)) $this->_filter($this->_route->action, $this->after_actions);

    if (isset($main_redirect_to)) $this->_redirect_to = $main_redirect_to;

    if (isset($main_render)) $this->_render = $main_render;

    if (isset($this->_redirect_to)) $this->_redirect_to();

    // default render must be!
    $this->_render();
  }

  public static function dispatch(ApplicationRoute $route) {
    if ($route->path) {
      self::_load(trim($route->path, "/")); // for superclass
      self::_load($route->controller, $route->path);
    } else {
      self::_load($route->controller);
    }

    // run controller class and before_actions, before_afters, helper functions
    $controller_class = ucwords($route->controller) . self::CONTROLLERSUBNAME;

    $c = new $controller_class($route);
    $c->_run();
  }
}
?>
