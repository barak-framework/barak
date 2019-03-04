<?php

class ApplicationController {

  const CONTROLLERPATH = "app/controllers/";

  const CONTROLLERSUBNAME = "Controller";

  private $_locals = [];
  private $_render = NULL;
  private $_redirect_to = NULL;
  private $_send_data = NULL;

  private $_route;

  final public function __construct(ApplicationRoute $route) { // genişletilemez fonksiyon
    $this->_route = $route;
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

  final public function send_data($content, $filename, $contenttype = NULL) { // genişletilemez fonksiyon
    $this->_send_data = [$content, $filename, $contenttype];
  }

  final public function redirect_to($url) { // genişletilemez fonksiyon
    $this->_redirect_to = $url;
  }

  final public function render($options) { // genişletilemez fonksiyon
    $this->_render = $options;
  }

  public static function get_content(ApplicationRoute $route) {
    if ($route->path != "") {
      $_before_path = "";
      $_paths = explode("/", trim($route->path, "/"));
      foreach ($_paths as $_path) {
        self::_load($_path, $_before_path);
        $_before_path = "{$_path}/";
      }
    }
    // main class
    self::_load($route->controller, $route->path);
    // run controller class and before_actions, before_afters, helper functions
    $controller_class = ucwords($route->controller) . self::CONTROLLERSUBNAME;
    $c = new $controller_class($route);
    return $c->_run();
  }

  private static function _load($file, $path = "") {
    $controller_class = ucwords($file) . self::CONTROLLERSUBNAME;
    $controller_file  = self::CONTROLLERPATH . $path . "{$controller_class}.php";

    if (!file_exists($controller_file))
      throw new Exception("Controller dosyası mevcut değil → " . $controller_file);

    require_once $controller_file;
    if (!class_exists($controller_class))
      throw new Exception("Controller sınıfı yüklenemedi → " . $controller_class);
  }

  private function _filter($action, $filter_actions) {

    foreach ($filter_actions as $filter_action) {

      if (array_key_exists(0, $filter_action)) {

        $filter_action_name = $filter_action[0];
        if (method_exists($this, $filter_action_name)) {

          // her action öncesi locals yükünü boşalt
          $this->_locals = [];

          if (array_key_exists("only", $filter_action)) {

            if (in_array($action, $filter_action["only"])) $this->{$filter_action_name}();

          } elseif (array_key_exists("except", $filter_action)) {

            if (!in_array($action, $filter_action["except"])) $this->{$filter_action_name}();

          } elseif (!array_key_exists("only", $filter_action) and !array_key_exists("except", $filter_action)) {
            $this->{$filter_action_name}();
          }

          // interrupt ?
          if ($this->_redirect_to || $this->_render || $this->_send_data) return TRUE;
        }

      }
    }
    // kesinti olmadı!
    return FALSE;
  }

  private function _send_data() {
    return [NULL, $this->_send_data];
  }

  private function _redirect_to() {
    return [302, $this->_redirect_to];
  }

  private function _render() {
    $v = new ApplicationView();

    // render template
    if ($this->_route->path) { // have path? for scope, resouce, resouces

      $v->set(["layout" => $this->_route->path]);
      $v->set(["view" => "/" . $this->_route->path . $this->_route->controller, "action" => $this->_route->action]);

    } else { // normal path

      $v->set(["view" => "/" . $this->_route->controller, "action" => $this->_route->action]);

    }

    // controllerin localsları
    if ($this->_locals)
      $v->set(["locals" => $this->_locals]);

    // controllerin renderi
    if ($this->_render)
      $v->set($this->_render);

    return [200, $v->run()];
  }

  private function _run() {

    // include helper classes
    if (isset($this->helpers)) ApplicationHelper::load($this->helpers);

    // before actions
    // eğer _send_data, _redirect_to, _render herhangi biri atanmışsa çalıştır ve sonlandır
    if (isset($this->before_actions)) {

      if ($this->_filter($this->_route->action, $this->before_actions)) {

        if ($this->_send_data)   return self::_send_data();
        if ($this->_redirect_to) return self::_redirect_to();
        if ($this->_render)      return self::_render();
      }
    }

    // router'in localslarını(sayfadan :id, çekmek için),
    // controller'dan gelen localslara yükle ki action içerisinden erişebilesin
    $this->_locals = $this->_route->locals;

    // kick main action!
    if (method_exists($this, $this->_route->action)) $this->{$this->_route->action}();

    // main action için _locals, _send_data, _redirect_to, _render için atanan verileri sakla
    $main_locals = $this->_locals;
    $main_send_data = $this->_send_data;
    $main_redirect_to = $this->_redirect_to;
    $main_render = $this->_render;

    // after actions
    // eğer _send_data, _redirect_to, _render herhangi biri atanmışsa çalıştır ve sonlandır
    if (isset($this->after_actions)) {
      if ($this->_filter($this->_route->action, $this->after_actions)) {
        if ($this->_send_data)   return self::_send_data();
        if ($this->_redirect_to) return self::_redirect_to();
        if ($this->_render)      return self::_render();
      }
    }

    // main action için daha önce saklanan _send_data verisini çalıştır ve sonlandır
    if ($main_send_data != NULL) {
      $this->_send_data = $main_send_data;
      return self::_send_data();
    }
    // main action için daha önce saklanan _redirect_to verisini çalıştır ve sonlandır
    if ($main_redirect_to != NULL) {
      $this->_redirect_to = $main_redirect_to;
      return self::_redirect_to();
    }

    // main action için daha önce saklanan _locals ile _render verilerini çalıştır ve sonlandır
    if ($main_render != NULL) {
      $this->_locals = $main_locals;
      $this->_render = $main_render;
      return self::_render();
    }

    // before actions, main action, after actions içerisinde
    // hiçbir şekilde _redirect_to, _render için atanan veri yok ise varsayılan _render çalışmalı!
    return $this->_render();
  }

}
?>
