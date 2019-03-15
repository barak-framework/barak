<?php

class ApplicationController {

  const CONTROLLERPATH = "app/controllers/";

  const CONTROLLERSUBNAME = "Controller";

  // controller locals
  private $_locals = [];

  // methods
  private $_render = NULL;
  private $_redirect_to = NULL;
  private $_send_data = NULL;

  // options
  protected $helpers = NULL;
  protected $before_actions = NULL;
  protected $after_actions = NULL;

  private $_route;

  final public function __get($local) { // genişletilemez method
    return $this->_locals[$local];
  }

  final public function __set($local, $value) { // genişletilemez method
    $this->_locals[$local] = $value;
  }

  final public function __isset($local) { // genişletilemez method
    return isset($this->_locals[$local]);
  }

  final public function __unset($local) { // genişletilemez method
    unset($this->_locals[$local]);
  }

  final public function send_data($body, $filename, $content_type = NULL) { // genişletilemez method
    $this->_send_data = ["options" => [$body, $filename], "content_type" => $content_type];
  }

  final public function redirect_to($url) { // genişletilemez method
    $this->_redirect_to = $url;
  }

  final public function render($view_options, $response_options = NULL) { // genişletilemez method
    $this->_render = ["view_options" => $view_options, "response_options" => $response_options];
  }

  public static function get_response(ApplicationRoute $route) {
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
    // run controller class and before_actions, before_afters, helper methods
    $controller_class = ucwords($route->controller) . self::CONTROLLERSUBNAME;
    $c = new $controller_class();
    $c->_route = $route;
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

          // her action öncesi,
          // locals yükünü boşalt
          $this->_locals = [];
          // methodların yüklerini boşalt
          $this->_render = NULL;
          $this->_redirect_to = NULL;
          $this->_send_data = NULL;

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
    $response = new ApplicationResponse();
    $response->body = $this->_send_data["options"]; // body and filename
    $response->status_code = NULL;
    $response->content_type = $this->_send_data["content_type"];
    return $response;
  }

  private function _redirect_to() {
    $response = new ApplicationResponse();
    $response->body = $this->_redirect_to;
    $response->status_code = 302;
    return $response;
  }

  private function _render() {
    $v = new ApplicationView();

    // render template
    if ($this->_route->path) { // have path? for scope, resouce, resouces

      $v->layout = trim($this->_route->path, "/");
      $v->view = $this->_route->path . $this->_route->controller;
      $v->action = $this->_route->action;

    } else { // normal path

      $v->view = $this->_route->controller;
      $v->action = $this->_route->action;

    }

    // controllerin localsları
    if ($this->_locals)
      $v->locals = $this->_locals;

    // controllerin renderi
    if ($this->_render)
      $v->set($this->_render["view_options"]);

    $body = $v->run();

    // response for body
    $response = new ApplicationResponse();
    $response->body = $body;
    $response->set($this->_render["response_options"]);
    return $response;
  }

  private function _run() {

    // include helper classes
    if ($this->helpers) ApplicationHelper::load($this->helpers);

    // before actions
    // eğer _send_data, _redirect_to, _render herhangi biri atanmışsa çalıştır ve sonlandır
    if ($this->before_actions) {

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
    if ($this->after_actions) {
      if ($this->_filter($this->_route->action, $this->after_actions)) {
        if ($this->_send_data)   return self::_send_data();
        if ($this->_redirect_to) return self::_redirect_to();
        if ($this->_render)      return self::_render();
      }
    }

    // main action için daha önce saklanan _send_data verisini çalıştır ve sonlandır
    if ($main_send_data) {
      $this->_send_data = $main_send_data;
      return self::_send_data();
    }

    // main action için daha önce saklanan _redirect_to verisini çalıştır ve sonlandır
    if ($main_redirect_to) {
      $this->_redirect_to = $main_redirect_to;
      return self::_redirect_to();
    }

    // main action için daha önce saklanan _locals ile _render verilerini çalıştır ve sonlandır
    if ($main_render) {
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
