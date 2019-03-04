<?php

class ApplicationRoutes {

  CONST METHODS = ["get", "post"];

  private static $_path = "";
  private static $_draws = NULL;
  private static $_routes = [];

  public static function __callStatic($method, array $args) {
    if (!in_array($method, self::METHODS))
      throw new Exception("Routes yapılandırma dosyasında bilinmeyen method → " .  $method);

    // args ([0] => rule, [1] => controller#action, [2] => path)
    $args[2] =  ($args[2]) ? static::$_path . $args[2] : static::$_path;
    // call private method _get, _post
    $route = ApplicationRoutes::{"_$method"}(...$args);
    self::_set_route($route);
  }

  public static function draw(callable $_functions) {
    // do not make dublicate draw function on config/routes.php
    if (!isset(self::$_draws)) {

      // router processing
      $_functions();
      // router processed

      // bir daha ::draws fonksiyonu çağrılmaması için
      self::$_draws = TRUE;
    }

    /* TEST */
    // foreach (static::$_routes as $method => $routes) {
    //   echo "## <|> METHOD: $method<br/>";
    //   foreach ($routes as $route) {
    //     print_r($route); echo "<br/><br/>";
    //   }
    // }
    // exit();
  }

  public static function get_route(ApplicationRequest $request) {
    if (array_key_exists($request->method, static::$_routes)) {
      if (array_key_exists($request->rule, static::$_routes[$request->method])) {
        return static::$_routes[$request->method][$request->rule];
      } else {
        // search for match routes
        foreach (static::$_routes[$request->method] as $route) {
          if ($route->match_rule != "") {
            $request_rule = explode("/", $request->rule);
            $route_rule = explode("/", $route->rule);
            if (count($request_rule) == count($route_rule)) {
              $match = TRUE;
              foreach ($request_rule as $index => $value) {
                if (($request_rule[$index] != $route_rule[$index]) and ($route_rule[$index] != ApplicationRoute::dynamic_segment)) {
                  $match = FALSE;
                  break;
                }
              }
              if ($match) {
                $permit_match_rule = explode("/", $route->match_rule);
                preg_match_all('@:([\w]+)@', $route->match_rule, $segments, PREG_PATTERN_ORDER);
                $segments = $segments[0];
                // get methodları için locals'a yükle : değişkenler
                foreach ($segments as $segment) {
                  if ($index = array_search($segment, $permit_match_rule)) {
                    $route->locals[substr($segment, 1)] = $request_rule[$index];
                  }
                }
                return $route;
              }
            }
          }
        }
      }
      return NULL;
    }
    throw new Exception("Uzay çağında bizim henüz desteklemediğimiz bir method → " . $request->method);
  }

  private static function _set_route(ApplicationRoute $route) {
    if (array_key_exists($route->method, static::$_routes)) {
      if (array_key_exists($route->rule, static::$_routes[$route->method])) {
        throw new Exception("Bu yönlendirme daha önceden tanımlanmış → " . $route->rule);
      }
    }
    static::$_routes[$route->method][$route->rule] = $route;
  }

  public static function scope($path, callable $_functions) {
    // path daha önce ilklendirildiyse (scope içinde scope varsa gibi) pathe ekleme yap yoksa path'i ata
    static::$_path =  static::$_path . "{$path}/";
    // scope içindeki fonksiyonları çalıştır
    $_functions();
    // https://stackoverflow.com/questions/2430208/php-how-to-remove-last-part-of-a-path
    // var olan path'de son parçayı sil (scope ile işimiz bitti)
    static::$_path = preg_replace("/\w+\/$/i", "", static::$_path);
  }

  public static function resource($table, $path = "") {
    ApplicationRoutes::get("$table",          "$table#index", $path);
    ApplicationRoutes::get("$table/create",   NULL,           $path);
    ApplicationRoutes::post("$table/save",    NULL,           $path);
    ApplicationRoutes::get("$table/show/",    NULL,           $path);
    ApplicationRoutes::get("$table/edit/",    NULL,           $path);
    ApplicationRoutes::post("$table/update",  NULL,           $path);
    ApplicationRoutes::post("$table/destroy", NULL,           $path);
  }

  public static function resources($table, $path = "") {
    ApplicationRoutes::get("$table",          "$table#index", $path);
    ApplicationRoutes::get("$table/create",   NULL,           $path);
    ApplicationRoutes::post("$table/save",    NULL,           $path);
    ApplicationRoutes::get("$table/show/:id", "$table#show",  $path);
    ApplicationRoutes::get("$table/edit/:id", "$table#edit",  $path);
    ApplicationRoutes::post("$table/update",  NULL,           $path);
    ApplicationRoutes::post("$table/destroy", NULL,           $path);
  }

  public static function root($target = NULL, $path = "") {
    ApplicationRoutes::get("", $target, $path);
  }

  private static function _get($rule, $target = NULL, $path = "") {
    return new ApplicationRoute("get",  $rule, $target, "$path");
  }

  private static function _post($rule, $target = NULL, $path = "") {
    return new ApplicationRoute("post", $rule, $target, $path);
  }

}
?>
