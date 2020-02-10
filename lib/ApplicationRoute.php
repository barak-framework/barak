<?php

class ApplicationRoute {

  // change name for :id/:action
  const dynamic_segment = "_dynamic_segment_";

  // router'in localslarını(sayfadan :id, çekmek için),
  // controller'dan gelen localslara yüklemek için sakla
  public $locals;

  public $path;
  public $match_rule;
  public $method;
  public $rule;
  public $controller;
  public $action;

  final public function __construct($method, $rule, $target = NULL, $path = "") { // genişletilemez fonksiyon

    // Dinamik denetleyici tanımlaması mı ? :id/:action gibi
    if (strpos($rule, ":")) {

      if ($target == NULL)
        throw new Exception("Dinamik route özelliğinde hedef (controller#action) belirtilmek zorundadır! → " . $rule);

      // Ör.: get("users/show/:id", "users#show"); // controller: users, action:show

      list($controller, $action) = self::_spliter_struct($target, "#");
      $this->_set($method, "/{$path}{$rule}", "/$path" . preg_replace("|:[\w]+|", self::dynamic_segment, $rule), $controller, $action, $path);

    } else {

      // Hedefi olan denetleyici mi ? controller#action gibi
      if ($target) {

        // Ör.: get("users",       "home#index"); // controller: users, action:index
        // Ör.: get("users/index", "home#about"); // controller: users, action:about

        list($controller, $action) = self::_spliter_struct($target, "#");
        $this->_set($method, "", "/{$path}{$rule}", $controller, $action, $path);

      } else {

        // Ör.: get("users/index"); // controller: users, action:index

        list($controller, $action) = self::_spliter_struct($rule, "/");
        $this->_set($method, "", "/{$path}{$rule}", $controller, $action, $path);

      }

    }
  }

  // route şablon yapılarını, parçalama yapar
  private static function _spliter_struct($subject, $delimiter) {
    // düzenli karakterler için  `\\` karakteri ile öncele
    $delimiter = "\\" . $delimiter;
    if (!preg_match('/^(.*)'. $delimiter . '(.*)$/', $subject, $rota))
      throw new Exception("Route yapılandırmasında çözümlenemeyen yapı → " . $subject);
    return [$rota[1], $rota[2]];
  }

  private function _set($method, $match_rule, $rule, $controller, $action, $path) {
    $this->method = strtoupper($method);
    $this->match_rule = $match_rule;
    $this->rule = $rule;
    $this->controller = $controller;
    $this->action = $action;
    $this->path = $path;
  }

}
?>
