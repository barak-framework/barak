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
        throw new Exception("Dynamic route özelliğinde hedef (controller#action) belirtilmek zorundadır! → " . $rule);

      list($controller, $action) = explode("#", trim($target, "/")); // get("/users/show/:id", "users#show"); // controller: users, action:show
      self::set($method, $match, $this->path . $rule, preg_replace("|:[\w]+|", self::dynamical_segment, $rule), $controller, $action);

    } elseif ($target) {

      list($controller, $action) = explode("#", trim($target, "/"));
      self::set($method, $match, "", $this->path . $rule, $controller, $action);

    } elseif (strpos($rule, "/") !== false) { // dizgi içerisinde konum(indexi) yok değilse (yani varsa)

      list($controller, $action) = array_pad(explode("/", trim($rule, "/")), 2, null);
      if ($action == null)
        throw new Exception("Route rule özelliğinde istek /controller/action şeklinde olmalıdır! → " . $rule);

      self::set($method, $match, "", $this->path . $rule, $controller, $action);

    } else {
      throw new Exception("/config/routes.php içinde beklenmedik kurallar → " . $rule);
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

  // Helper Methods

  public static function scope($permitted_packages) {

    // İzin verilmiş route'ları routes'a yükle
    $path = $permitted_packages[0];

    $permitted_packages = array_slice($permitted_packages, 1);

    $routes = [];
    foreach ($permitted_packages as $permitted_package) {
      foreach ($permitted_package as $permitted_route) {

        $permitted_route->path = $path;

        if ($permitted_route->match) {
          $permitted_route->match_rule = $path . $permitted_route->match_rule;
        }

        $permitted_route->rule = $path . $permitted_route->rule;
        $routes[] = $permitted_route;
      }
    }

    return $routes;
  }

  public static function resource($table, $path = null) {
    return [
    new ApplicationRoute("get",  "$table",         "$table#index", false, $path),
    new ApplicationRoute("get",  "$table/create",  false,          false, $path),
    new ApplicationRoute("post", "$table/save",    false,          false, $path),
    new ApplicationRoute("get",  "$table/show/",   false,          false, $path),
    new ApplicationRoute("get",  "$table/edit/",   false,          false, $path),
    new ApplicationRoute("post", "$table/update",  false,          false, $path),
    new ApplicationRoute("post", "$table/destroy", false,          false, $path)
    ];
  }

  public static function resources($table, $path = null) {
    return [
    new ApplicationRoute("get",  "$table",          "$table#index", false, $path),
    new ApplicationRoute("get",  "$table/create",   false,          false, $path),
    new ApplicationRoute("post", "$table/save",     false,          false, $path),
    new ApplicationRoute("get",  "$table/show/:id", "$table#show",  true,  $path),
    new ApplicationRoute("get",  "$table/edit/:id", "$table#edit",  true,  $path),
    new ApplicationRoute("post", "$table/update",   false,          false, $path),
    new ApplicationRoute("post", "$table/destroy",  false,          false, $path)
    ];
  }

  public static function root($target = false, $path = null) {
    if (!$target)
      throw new Exception("Root route özelliğinde hedef (controlller#action) belirtilmek zorundadır! → root");
    return new ApplicationRoute("get", "/", $target, false, $path);
  }

  public static function post($rule, $target = false, $path = null) {
    return new ApplicationRoute("post", $rule, $target, (strpos($rule, ":") ? true : false), $path);
  }

  public static function get($rule, $target = false, $path = null) {
    return new ApplicationRoute("get",  $rule, $target, (strpos($rule, ":") ? true : false), $path);
  }

}
?>
