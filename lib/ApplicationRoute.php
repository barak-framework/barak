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

    // Dinamik denetleyici tanımlaması mı ? :id/:action gibi
    if (strpos($rule, ":")) {

      if ($target) {

        // Ör.: get("/users/show/:id", "users#show"); // controller: users, action:show

        list($controller, $action) = self::_spliter_struct($target, "#");
        self::set($method, $this->path . $rule, $this->path . preg_replace("|:[\w]+|", self::dynamical_segment, $rule), $controller, $action);

      } else {
        throw new Exception("Dinamik route özelliğinde hedef (controller#action) belirtilmek zorundadır! → " . $rule);
      }

    } elseif (strpos($rule, "/") !== false) {

      // Hedefi olan denetleyici mi ? controller#action gibi
      if ($target) {

        // Ör.: get("/users/index", "home#about"); // controller: users, action:about

        list($controller, $action) = self::_spliter_struct($target, "#");
        self::set($method, "", $this->path . $rule, $controller, $action);

      } else {

        // Ör.: get("/users/index"); // controller: users, action:index
        $_rule = trim($rule, "/");
        list($controller, $action) = self::_spliter_struct($_rule, "/");
        self::set($method, "", $this->path . $rule, $controller, $action);

      }

    } else {
      throw new Exception("Route yapılandırmasında beklenmedik kural → " . $rule);
    }
  }

  private static function _spliter_struct($subject, $delimiter) {
    // düzenli karakterler için  `\\` karakteri ile öncele
    $delimiter = "\\" . $delimiter;
    if (!preg_match('/^(.*)'. $delimiter . '(.*)$/', $subject, $rota))
      throw new Exception("Route yapılandırmasında çözümlenemeyen yapı → " . $subject);
    return [$rota[1], $rota[2]];
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
