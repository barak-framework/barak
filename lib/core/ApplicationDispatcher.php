<?php

class ApplicationDispatcher {

  // working time in milliseconds
  public static $time;

  public static function run() {

    self::$time = microtime(true);

    // info for request/requester
    $request = new ApplicationRequest();

    ApplicationLogger::info("Started {$request->method} '{$request->rule}' for {$request->ip} at {$request->datetime}");

    // İstek url ile routes'ı içinden bul ve sevk et
    if ($route = ApplicationRoutes::get_route($request)) {

      ApplicationLogger::info("Processing by " . ucfirst($route->controller) . "Controller#{$route->action} as HTML");

      // returned status code | not including: 404, 500
      $response = ApplicationController::get_response($route);

      if ($response->status_code == 302) { // only 302
        self::completed($response->status());
        $response->run();
      } else { // not including : 302, 404, 500 | including : 200, 201, 202, ... etc.
        $response->run();
        self::completed($response->status());
      }

    } else { // only 404

      ApplicationLogger::error("No route matches [{$request->method}] {$request->rule}");

      $response = new ApplicationResponse();
      $response->status_code = 404;
      $response->run();
      self::completed($response->status());

    }
  }

  // for ApplicationDebug access(500)
  public static function completed($status) {
    ApplicationLogger::info("Completed {$status} in " . sprintf ("(%.2f ms)", (microtime(true) - self::$time) * 1000));
  }

}
?>
