<?php

class ApplicationController {

  const CONTROLLERPATH = "app/controllers/";
  const HELPERPATH = "app/helpers/";

  private $_locals = [];
  private $_render;
  private $_redirect_to;

  private $_route;

  final public function __construct(ApplicationRoute $route) { // genişletilemez fonksyion
    $this->_route = $route;
  }

  final public function __get($local) { // genişletilemez fonksyion
    return $this->_locals[$local];
  }

  final public function __set($local, $value) { // genişletilemez fonksyion
    $this->_locals[$local] = $value;
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
          if (isset($this->_redirect_to)) exit($this->_redirect_to());
          if (isset($this->_render))      exit($this->_render());
        }
      }

    }
  }

  private function _helper($helper) {
    if (is_array($this->helpers)) {
      foreach ($this->helpers as $helper) {
        $helper_path = self::HELPERPATH . $helper . "Helper.php";
        if (!file_exists($helper_path))
          throw new FileNotFoundException("Helper dosyası mevcut değil", $helper_path);
        include $helper_path;
      }
    } elseif($this->helpers == "all") {
      foreach(glob($self::HELPERPATH . "*.php") as $class) {
        include_once $class;
      }
    } else {
      throw new Exception("Helper methodunda bilinmeyen parametre", $this->helper);
    }
  }

  private function _render() {
    // render controller choice
    $v = new ApplicationView();

    // render template
    if ($this->_route->path) { // have scope or path of resouce/resouces

      $v->set(["layout" => $this->_route->path]);
      $v->set(["view" => $this->_route->path . "/" . $this->_route->controller, "action" => $this->_route->action]);

    } else { // normal path

      $v->set(["view" => "/" . $this->_route->controller, "action" => $this->_route->action]);

    }

    // $vars["_locals"] : controllerin localsları
    if ($this->_locals)
      $v->set(["locals" => $this->_locals]);

    // vars["_render"] : controllerın renderi
    if ($this->_render)
      $v->set($this->_render);

    $v->run();
  }

  private function _redirect_to() {
    exit(header("Location: http://" . $_SERVER['SERVER_NAME'] . "/" . trim($this->_redirect_to, "/"), false, 303));
  }

  final public function run() { // genişletilemez fonksyion

    if (isset($this->helpers)) $this->_helper($this->helpers);

    if (isset($this->before_actions)) $this->_filter($this->_route->action, $this->before_actions);

    if (method_exists($this, $this->_route->action)) $this->{$this->_route->action}();
    if (isset($this->_redirect_to)) $main_redirect_to = $this->_redirect_to;
    if (isset($this->_render)) $main_redirect_to = $this->_render;

    if (isset($this->after_actions)) $this->_filter($this->_route->action, $this->after_actions);

    if (isset($main_redirect_to)) {
      $this->_redirect_to = $main_redirect_to;
      return $this->_redirect_to();
    }
    if (isset($main_render)) {
      $this->_render = $main_render;
      return $this->_render();
    }

    // default render
    $this->_render();
  }

  final public function render($options) { // genişletilemez fonksyion
    $this->_render = $options;
  }

  final public function redirect_to($url) { // genişletilemez fonksyion
    $this->_redirect_to = $url;
  }

  final public static function load_file($file, $path = "") { // genişletilemez fonksyion
    $controller_class = ucwords($file) . "Controller";
    $controller_path  = self::CONTROLLERPATH . trim($path,"/") . "/" . $controller_class . '.php';
    if (!file_exists($controller_path))
      throw new FileNotFoundException("Controller dosyası mevcut değil", $controller_path);

    include $controller_path;

    if (!class_exists($controller_class))
      throw new FileNotFoundException("Controller sınıfı yüklenemedi", $controller_class);
  }
}
?>