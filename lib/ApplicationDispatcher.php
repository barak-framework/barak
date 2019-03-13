<?php

class ApplicationDispatcher {

  public static function dispatch() {

    // info for request/requester
    $request = new ApplicationRequest();

    ApplicationLogger::info("Started {$request->method} '{$request->rule}' for {$request->ip} at {$request->datetime}");

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

    $response->send();
  }

}
?>
