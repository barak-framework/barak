<?php

class ApplicationRoute {

  // change name for :id/:action
  const dynamical_segment = "_dynamical_segment_";
  public $locals = [];
  public $path;
  public $match_rule;
  public $method;
  public $rule;
  public $controller;
  public $action;

  public function __construct($method, $rule, $target = false, $path = null) {
    $this->path = ($path) ? $path : "";
    // is match_rule ? for :id/:action like
    if (strpos($rule, ":")) {
      if (!$target) { throw new Exception("Dynamic route özelliğinde hedef (controller#action) belirtilmek zorundadır! → " . $rule); }
      // get("/users/show/:id", "users#show"); // controller: users, action:show
      list($controller, $action) = explode("#", trim($target, "/"));
      self::set($method, $this->path . $rule, $this->path . preg_replace("|:[\w]+|", self::dynamical_segment, $rule), $controller, $action);
    } elseif ($target) {
      list($controller, $action) = explode("#", trim($target, "/"));
      self::set($method, "", $this->path . $rule, $controller, $action);
    // dizgi içerisinde konum(indexi) yok değilse (yani varsa)
    } elseif (strpos($rule, "/") !== false) {
      list($controller, $action) = array_pad(explode("/", trim($rule, "/")), 2, null);
      if ($action == null) { throw new Exception("Route rule özelliğinde istek /controller/action şeklinde olmalıdır! → " . $rule); }
      self::set($method, "", $this->path . $rule, $controller, $action);
    } else { throw new Exception("/Something is wrong/routes.php içinde beklenmedik kurallar → " . $rule); }
  }

  public function set($method, $match_rule, $rule, $controller, $action) {
    $this->method = strtoupper($method);
    $this->match_rule = $match_rule;
    $this->rule = $rule;
    $this->controller = $controller;
    $this->action = $action;
  }

}
?>
