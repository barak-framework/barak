<?php

class ApplicationDispatcher {

  public static function dispatch() {

    // info for REQUEST_ROUTE and REQUESTER
    $request = new ApplicationRequest();
    $time_start = microtime(true);
    $date_now = date("Y-m-d H:i:s");

    ApplicationLogger::info("Started {$request->method} '{$request->rule}' for {$request->ip} at {$date_now}");

    // İstek url ile routes'ı içinden bul ve sevk et
    if ($route = ApplicationRoutes::get_route($request)) {

      ApplicationLogger::info("Processing by {$route->controller}#{$route->action}");

      // route action dispatch in controller and view
      $content = ApplicationController::get_content($route);
      foreach ($content as $key => $value) { $statuscode = $key; $content = $value; }

      // maybe status code : 200, 302, NULL
      $response = new ApplicationResponse($statuscode, $content);

    } else {

      ApplicationLogger::error("No route matches [{$request->method}] {$request->rule}");

      $response = new ApplicationResponse(404);
    }

    ApplicationLogger::info("Completed {$response->status()} in " . sprintf ("(%.2f ms)", (microtime(true) - $time_start) * 1000));
    $response->send();
  }

}
?>