<?php

class ApplicationAlias {

  public static function extract() {

    // ROUTE

    function scope($path, $routes) {
      return ApplicationRoutes::scope($path, $routes);
    }

    function resource($table, $path = "") {
      return ApplicationRoutes::resource($table, $path);
    }

    function resources($table, $path = "") {
      return ApplicationRoutes::resources($table, $path);
    }

    function root($target = FALSE, $path = "") {
      return ApplicationRoutes::root($target, $path);
    }

    function post($rule, $target = FALSE, $path = "") {
      return ApplicationRoutes::post($rule, $target, $path);
    }

    function get($rule, $target = FALSE, $path = "") {
      return ApplicationRoutes::get($rule, $target, $path);
    }

    // LOCALES

    function t($words, $locals = NULL) {
      return ApplicationI18n::translate($words, $locals);
    }

    // LAYOUT and TEMPLATE
    // for app/views/VIEW/ACTION.php and app/views/layouts/VIEW.php

    function render($view_options = NULL) {
      if ($view_options) {
        $v = new ApplicationView();
        $v->set($view_options);
        return $v->run();
      }
    }

  }

}
?>
