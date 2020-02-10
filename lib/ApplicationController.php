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
  protected $flash = [];
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

  private function _response_send_data() {
    $response = new ApplicationResponse();
    $response->status_code = NULL;
    $response->content_type = $this->_send_data["content_type"];
    $response->body = $this->_send_data["options"]; // body and filename
    return $response;
  }

  private function _response_redirect_to() {
    $response = new ApplicationResponse();
    $response->status_code = 302;
    $response->content_type = NULL;
    $response->body = $this->_redirect_to;
    return $response;
  }

  private function _response_render() {
    // View - start
    $v = new ApplicationView();

    // varsayılan render ayarlarını bul
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

    // Overwrite default render options
    // controllerin renderi (main action içerisinde manuel olarak render yazılmışsa)
    if ($this->_render)
      $v->set($this->_render["view_options"]);

    $body = $v->run(true);

    // Response - start
    $response = new ApplicationResponse();

    // controllerin renderi (main action içerisinde manuel olarak render yazılmışsa)
    if ($this->_render)
      $response->set($this->_render["response_options"]);

    $response->body = $body;
    return $response;
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
        if (!method_exists($this, $filter_action_name))
          throw new Exception("Before/After actions olarak tanımı yapılmayan method → " . $filter_action_name);

        // her action öncesi,
        // locals yükünü boşalt
        $this->_locals = [];

        // methodların yüklerini boşalt
        $this->_send_data = NULL;
        $this->_redirect_to = NULL;
        $this->_render = NULL;

        if (array_key_exists("only", $filter_action)) {

          if (in_array($action, $filter_action["only"])) $this->{$filter_action_name}();

        } else if (array_key_exists("except", $filter_action)) {

          if (!in_array($action, $filter_action["except"])) $this->{$filter_action_name}();

        } else if (!array_key_exists("only", $filter_action) and !array_key_exists("except", $filter_action)) {
          $this->{$filter_action_name}();
        }

        // _localsda flash varsa çalıştıralım
        if (!empty($this->flash)) ApplicationFlash::sets($this->flash);

        // Before/After Actions için tanımlanan method(filter_action_name) çalıştıysa ve bir kesinti olduysa?
        if ($this->_redirect_to || $this->_render || $this->_send_data) return TRUE;

      }
    }

    // kesinti olmadı!
    return FALSE;
  }

  private function _run() {

    // include helper classes
    if ($this->helpers) ApplicationHelper::load($this->helpers);

    // before actions
    // eğer main_action'a bağlı bir before action çalışacaksa _locals'ı boşalt onu çalıştır
    // eğer _send_data, _redirect_to, _render herhangi biri atanmışsa çalıştır ve sonlandır
    if ($this->before_actions) {
      if ($this->_filter($this->_route->action, $this->before_actions)) {
        if ($this->_send_data)   return $this->_response_send_data();
        if ($this->_redirect_to) return $this->_response_redirect_to();
        if ($this->_render)      return $this->_response_render();
      }
    }

    // router'in localslarını(sayfadan :id, çekmek için),
    // controller'dan gelen localslara yükle ki main action içerisinden erişebilesin
    $this->_locals = $this->_route->locals;

    // kick main action!
    if (method_exists($this, $this->_route->action)) $this->{$this->_route->action}();

    // main action çalışma sonrası
    // içeriğin ne ile döneceğini bilmedğimizi varsayalım
    $main_content = NULL;

    // _localsda flash varsa çalıştıralım
    if (!empty($this->flash)) ApplicationFlash::sets($this->flash);

    // main action için daha önce saklanan _send_data verisini çalıştır ve beklet
    if (!$main_content && $this->_send_data) {
      $main_content = $this->_response_send_data();
    }

    // main action için daha önce saklanan _redirect_to verisini çalıştır ve beklet
    if (!$main_content && $this->_redirect_to) {
      $main_content = $this->_response_redirect_to();
    }

    // main action için daha önce saklanan _locals ile _render verilerini çalıştır ve beklet
    // hiçbir şekilde _send_data, _redirect_to, _render için atanan veri yok ise varsayılan _render çalışmalı!
    // ya da
    // _render atanmış ise(return ile kesilmezse son atanan _render) çalışmalı!
    if (!$main_content) {
      $main_content = $this->_response_render();
    }

    // after_actions
    // eğer main_action'a bağlı bir after action çalışacaksa _locals'ı boşalt onu çalıştır
    // eğer _send_data, _redirect_to, _render herhangi biri atanmışsa çalıştır ve sonlandır
    if ($this->after_actions) {
      if ($this->_filter($this->_route->action, $this->after_actions)) {
        if ($this->_send_data)   return $this->_response_send_data();
        if ($this->_redirect_to) return $this->_response_redirect_to();
        if ($this->_render)      return $this->_response_render();
      }
    }

    // artık başka çaremiz kalmadı, Dünya'dan kaçıyoruz!
    return $main_content;
  }

}
?>
