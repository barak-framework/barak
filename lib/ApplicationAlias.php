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

    function t($words) {
      return ApplicationI18n::translate($words);
    }

    // LAYOUT and TEMPLATE
    // for app/views/VIEW/ACTION.php and app/views/layouts/VIEW.php

    function render($options = NULL) {
      if ($options) {
        $v = new ApplicationView();
        $v->set($options);
        return $v->run();
      }
    }

  }

}
?>
