<?php

class ApplicationRoutes {

  // http://stackoverflow.com/questions/165779/are-the-put-delete-head-etc-methods-available-in-most-web-browsers
  public $_routes = [ "GET" => [], "POST" => [] ]; // http put, delete is not support

  public static function draw() {

    // $request_route = new ApplicationRoute($_SERVER["REQUEST_METHOD"], $_SERVER['REQUEST_URI'], false);
    $request_route = [ "_rule" => $_SERVER['REQUEST_URI'], "_method" => $_SERVER["REQUEST_METHOD"] ];

    $routes = new ApplicationRoutes();

    // İzin verilmiş route'ları routes'a yükle
    $permitted_routes = func_get_args();
    foreach ($permitted_routes as $permitted_route) {

      if (is_array($permitted_route)) { // for resource(), resources(), scope();
        foreach ($permitted_route as $permitted_r)
          $routes->set_route($permitted_r);
      } else {
        $routes->set_route($permitted_route);
      }

    }

    // TEST $route list
    // print_r($routes->_routes);

    // foreach ($routes->_routes as $method => $_routes) {
    //   echo "<br/>";
    //   print_r($method);
    //   echo "<br/>";

    //   foreach ($_routes as $_route) {
    //    echo "<br/>";
    //     print_r($_route);
    //     echo "<br/>";
    //   }
    // }

    // İstek url ile routes'ı içinden bul ve sevk et
    if ($route = $routes->get_route($request_route)) {
      ApplicationController::dispatch($route);
    } else {
      $v = new ApplicationView();
      $v->set(["file" => "public/404.html"]);
      echo $v->run();
      exit();
    }
  }

  public function get_route($request_route) { // __get($request_route) // is not support object, only string

    if (array_key_exists($request_route["_method"], $this->_routes)) {

      if (array_key_exists($request_route["_rule"], $this->_routes[$request_route["_method"]])) {
        return $this->_routes[$request_route["_method"]][$request_route["_rule"]];
      } else { // search for match routes

        foreach ($this->_routes[$request_route["_method"]] as $_route) {

          if ($_route->match) {

            $request_rule = explode("/", trim($request_route["_rule"], "/"));
            $permit_rule = explode("/", trim($_route->rule, "/"));

            if (count($request_rule) == count($permit_rule)) {
              $match = true;
              foreach ($request_rule as $index => $value) {

                if (($request_rule[$index] != $permit_rule[$index]) and ($permit_rule[$index] != ApplicationRoute::dynamical_segment)) {
                  $match = false;
                  break;
                }
              }
              if ($match) {

                $permit_match_rule = explode("/", trim($_route->match_rule, "/"));
                preg_match_all('@:([\w]+)@', $_route->match_rule, $segments, PREG_PATTERN_ORDER);
                $segments = $segments[0];

                // get methodları için locals'a yükle : değişkenler
                foreach ($segments as $segment) {
                  if ($index = array_search($segment, $permit_match_rule)) {
                    $_route->locals[substr($segment, 1)] = $request_rule[$index];
                  }
                }

                return $_route;
              }
            }
          }
        }
      }
      return null;
      //throw new ConfigurationException("Böyle bir yönlendirme mevcut değil", $request_route["_method"] . ":" . $request_route["_rule"]);
    }
    throw new Exception("Uzay çağında bizim henüz desteklemediğimiz bir method → " . $request_route["_method"]);
  }

  public function set_route(ApplicationRoute $route) {
    if (array_key_exists($route->rule, $this->_routes[$route->method]))
      throw new Exception("Bu yönlendirme daha önceden tanımlanmış → " . $route->rule);
    $this->_routes[$route->method][$route->rule] = $route;
  }
}

?>
