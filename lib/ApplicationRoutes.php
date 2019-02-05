<?php

class ApplicationRoutes {
  private static $_path = "";
  private static $_draws = NULL;
  private static $_routes = [];

  public static function __callStatic($method, array $args) {
    if (in_array($method, [ 'get', 'post' ])) {
      // args ([0] => rule, [1] => controller#action, [2] => path)
      $args[2] =  ($args[2]) ? static::$_path . $args[2] : static::$_path;
      // call private method _get, _post
      $route = ApplicationRoutes::{"_$method"}(...$args);
      self::set_route($route);
    } else { throw new Exception("Beklenmedik bir method → " .  $method); }
  }

  public static function draw(callable $_functions) {
    // do not make dublicate draw function on config/routes.php
    if (!isset(self::$_draws)) {

      // router processing
      $_functions();
      // router processed

     // TEST
      // foreach (static::$_routes as $method => $routes) {
      //   echo "## <|> METHOD: $method<br/>";
      //   foreach ($routes as $route) {
      //     print_r($route); echo "<br/><br/>";
      //   }
      // }
      self::$_draws = TRUE;
    }

    $request_route = [ "_rule" => $_SERVER['REQUEST_URI'], "_method" => $_SERVER["REQUEST_METHOD"] ];

    $hour = intval(date("H"));
    if ($hour > 2 && $hour < 5) {
      $v = new ApplicationView();
      $v->set(["file" => "public/1071.html"]);
      echo $v->run();
      exit();
    }

    // İstek url ile routes'ı içinden bul ve sevk et
    if ($route = self::get_route($request_route)) {
      ApplicationController::dispatch($route);
    } else {
      $v = new ApplicationView();
      $v->set(["file" => "public/404.html"]);
      echo $v->run();
      exit();
    }
  }

  // __get($request_route) // is not support object, only string
  private static function get_route(array $request_route) {
    if (array_key_exists($request_route["_method"], static::$_routes)) {
      if (array_key_exists($request_route["_rule"], static::$_routes[$request_route["_method"]])) {
        return static::$_routes[$request_route["_method"]][$request_route["_rule"]];
      } else {
        // search for match routes
        foreach (static::$_routes[$request_route["_method"]] as $route) {
          if ($route->match_rule != "") {
            $request_rule = explode("/", trim($request_route["_rule"], "/"));
            $permit_rule = explode("/", trim($route->rule, "/"));
            if (count($request_rule) == count($permit_rule)) {
              $match = true;
              foreach ($request_rule as $index => $value) {
                if (($request_rule[$index] != $permit_rule[$index]) and ($permit_rule[$index] != ApplicationRoute::dynamical_segment)) {
                  $match = false;
                  break;
                }
              }
              if ($match) {
                $permit_match_rule = explode("/", trim($route->match_rule, "/"));
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
      return null;
    }
    throw new Exception("Uzay çağında bizim henüz desteklemediğimiz bir method → " . $request_route["_method"]);
  }

  private static function set_route(ApplicationRoute $route) {
    if (array_key_exists($route->method, static::$_routes)) {
      if (array_key_exists($route->rule, static::$_routes[$route->method])) {
        throw new Exception("Bu yönlendirme daha önceden tanımlanmış → " . $route->rule);
      }
    }
    static::$_routes[$route->method][$route->rule] = $route;
  }

  public static function scope($path, callable $_functions) {
    // path daha önce ilklendirildiyse (scope içinde scope varsa gibi) pathe ekleme yap yoksa path'i ata
    static::$_path = static::$_path . $path;
    // scope içindeki fonksiyonları çalıştır
    $_functions();
    // https://stackoverflow.com/questions/2430208/php-how-to-remove-last-part-of-a-path
    // var olan path'de son parçayı sil (scope ile işimiz bitti)
    static::$_path = preg_replace("/\/\w+$/i", "", static::$_path);
  }

  public static function resource($table, $path = null) {
    ApplicationRoutes::get("$table",          "$table#index", $path);
    ApplicationRoutes::get("$table/create",   false, $path);
    ApplicationRoutes::post("$table/save",    false, $path);
    ApplicationRoutes::get("$table/show/",    false, $path);
    ApplicationRoutes::get("$table/edit/",    false, $path);
    ApplicationRoutes::post("$table/update",  false, $path);
    ApplicationRoutes::post("$table/destroy", false, $path);
  }

  public static function resources($table, $path = null) {
    ApplicationRoutes::get("$table",          "$table#index", $path);
    ApplicationRoutes::get("$table/create",   false,          $path);
    ApplicationRoutes::post("$table/save",    false,          $path);
    ApplicationRoutes::get("$table/show/:id", "$table#show",  $path);
    ApplicationRoutes::get("$table/edit/:id", "$table#edit",  $path);
    ApplicationRoutes::post("$table/update",  false,          $path);
    ApplicationRoutes::post("$table/destroy", false,          $path);
  }

  public static function root($target = false, $path = null) {
    if (!$target)
      throw new Exception("Root route özelliğinde hedef (controller#action) belirtilmek zorundadır! → root");
    ApplicationRoutes::get("/", $target, $path);
  }

  private static function _post($rule, $target = false, $path = null) {
    return new ApplicationRoute("post", $rule, $target, $path);
  }

  private static function _get($rule, $target = false, $path = null) {
    return new ApplicationRoute("get",  $rule, $target, $path);
  }
}
?>
