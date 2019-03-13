<?php

class ApplicationDispatcher {

  public static function dispatch() {

    // info for request/requester
    $request = new ApplicationRequest();
    $time_start = microtime(true);
    $date_now = date("Y-m-d H:i:s");

    ApplicationLogger::info("Started {$request->method} '{$request->rule}' for {$request->ip} at {$date_now}");

    // İstek url ile routes'ı içinden bul ve sevk et
    if ($route = ApplicationRoutes::get_route($request)) {

      ApplicationLogger::info("Processing by {$route->controller}#{$route->action}");

      // returned status code not including: 404, 500
      $response = ApplicationController::get_response($route);

    } else {

      ApplicationLogger::error("No route matches [{$request->method}] {$request->rule}");

      $response = new ApplicationResponse();
      $response->status_code = 404;
    }

    ApplicationLogger::info("Completed {$response->status()} in " . sprintf ("(%.2f ms)", (microtime(true) - $time_start) * 1000));
    $response->send();
  }

}
?>
