<?php

class ApplicationRoute {

  const dynamical_segment = "_dynamical_segment_"; // change name for :id/:action

  public $locals = [];
  public $path;
  public $match;
  public $match_rule;
  public $method;
  public $rule;

  public $controller;
  public $action;

  public function __construct($method, $rule, $target = false, $match = false, $path = null) {
    $this->path = ($path) ? $path : "";

    if ($match) { // for :id/:action like

      if (!$target)
        throw new ConfigurationException("Dynamic route özelliğinde hedef (controller#action) belirtilmek zorundadır!", $rule);

      list($controller, $action) = explode("#", trim($target, "/")); // get("/users/show/:id", "users#show"); // controller: users, action:show
      self::set($method, $match, $this->path . $rule, preg_replace("|:[\w]+|", self::dynamical_segment, $rule), $controller, $action);

    } elseif ($target) {

      list($controller, $action) = explode("#", trim($target, "/"));
      self::set($method, $match, "", $this->path . $rule, $controller, $action);

    } elseif (strpos($rule, "/") !== false) { // dizgi içerisinde konum(indexi) yok değilse (yani varsa)

      list($controller, $action) = array_pad(explode("/", trim($rule, "/")), 2, null);
      if ($action == null)
        throw new ConfigurationException("Route rule özelliğinde istek /controller/action şeklinde olmalıdır!", $rule);

      self::set($method, $match, "", $this->path . $rule, $controller, $action);

    } else {
      throw new ConfigurationException("/config/routes.php içinde beklenmedik kurallar", $rule);
    }
  }

  public function set($method, $match, $match_rule, $rule, $controller, $action) {
    $this->method = strtoupper($method);
    $this->match = $match;
    $this->match_rule = $match_rule;
    $this->rule = $rule;

    $this->controller = $controller;
    $this->action = $action;
  }
}

?>
