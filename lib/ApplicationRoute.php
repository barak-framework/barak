<?php

class ApplicationRoute {

  const dynamical_segment = "_dynamical_segment_"; // change name for :id/:action
  const default_controller = "default";
  const default_action = "index";

  public $_locals = [];
  public $_path;
  public $_match;
  public $_match_rule;
  public $_method;
  public $_rule;

  public $_controller;
  public $_action;

  public function __construct($method, $rule, $target = false, $match = false, $path = null) {
    $this->_path = ($path) ? $path : "";

    if ($match) { // for :id/:action like

      if (!$target)
        throw new ConfigurationException("Dynamic route özelliğinde hedef (controller#action) belirtilmek zorundadır!", $rule);

      $option = explode("#", trim($target, "/")); // get("/users/show/:id", "users#show"); // controller: users, action:show
      self::set($method, $match, $this->_path . $rule, preg_replace("|:[\w]+|", self::dynamical_segment, $rule), $option[0], $option[1]);

    } elseif ($target) {

      $option = explode("#", trim($target, "/"));
      self::set($method, $match, "", $this->_path . $rule, $option[0], $option[1]);

    } elseif (strpos($rule, "/") !== false) { // dizgi içerisinde konum(indexi) yok değilse (yani varsa)

      $option = explode("/", trim($rule, "/"));
      if (count($option) != 2)
        throw new ConfigurationException("Route rule özelliğinde istek /controller/action şeklinde olmalıdır!", $rule);

      // Note: rule uzatmak için path'e ekleme yapılması gerek!
      self::set($method, $match, "", $this->_path . $rule, $option[0], $option[1]);

    } else {
      throw new ConfigurationException("/config/routes.php içinde beklenmedik kurallar", $rule);
    }
  }

  public function set($method, $match, $match_rule, $rule, $controller, $action) {
    $this->_method = strtoupper($method);
    $this->_match = $match;
    $this->_match_rule = $match_rule;
    $this->_rule = $rule;

    $this->_controller = $controller;
    $this->_action = $action;
  }

  public function run() {

    // unset($GLOBALS["success"]); unset($GLOBALS["danger"]); // TODO

    // run controller class and before_filter functions
    $controller_class = ucwords($this->_controller) . 'Controller';
    if (!class_exists($controller_class))
      throw new FileNotFoundException("Controller sınıfı/dosyası yüklenemedi", $controller_class);

    // translate for i18n
    if (isset($_SESSION["i18n"])) $_SESSION["i18n"]->run();

    $c = new $controller_class();

    // router'in localslarını(sayfadan :id, çekmek için), controller'dan gelen localslara yükle
    $c->_locals = $this->_locals;
    $c->run($this->_action);

    // controller vars fetch
    $vars = get_object_vars($c);

    // render controller choice
    $v = new ApplicationView();

    // render template
    if ($this->_path) { // have scope or path of resouce/resouces

      $v->set(["layout" => $this->_path]);
      $v->set(["view" => $this->_path . "/" . $this->_controller, "action" => $this->_action]);

    } else { // normal path

      $v->set(["view" => "/" . $this->_controller, "action" => $this->_action]);

    }

    // $vars["_locals"] : controllerin localsları
    if ($vars["_locals"])
      $v->set(["locals" => $vars["_locals"]]);

    // vars["_render"] : controllerın renderi
    if ($vars["_render"])
      $v->set($vars["_render"]);

    $v->run();
  }
}

?>
