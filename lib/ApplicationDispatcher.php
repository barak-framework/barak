<?php

class ApplicationDispatcher extends ApplicationAtimer {

  public static function dispatch() {
    $d = new ApplicationDispatcher();
    $d->run();
  }

  public function run() {

    // info for request/requester
    $request = new ApplicationRequest();
    // $time_start = microtime(true);

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

    // run!
    $response->send();

    // for ApplicationTimer class
    $this->_timer_message = "Completed {$response->status()}";
    // ApplicationLogger::info("Completed {$response->status()}" . sprintf ("(%.2f ms)", (microtime(true) - $time_start) * 1000));

  }

}
?>
