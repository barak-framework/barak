<?php

class ApplicationAlias {

  public static function extract() {

    // ROUTE

    function scope() {
      $permitted_packages = func_get_args();
      return ApplicationRoute::scope($permitted_packages);
    }

    function resource($table, $path = null) {
      return ApplicationRoute::resource($table, $path);
    }

    function resources($table, $path = null) {
      return ApplicationRoute::resources($table, $path);
    }

    function root($target = false, $path = null) {
      return ApplicationRoute::root($target, $path);
    }

    function post($rule, $target = false, $path = null) {
      return ApplicationRoute::post($rule, $target, $path);
    }

    function get($rule, $target = false, $path = null) {
      return ApplicationRoute::get($rule, $target, $path);
    }

    // LOCALES

    function t($words) {
      return ApplicationI18n::translate($words);
    }

    // UTILS

    function h($content) {
      return ApplicationUtil::html_escape($content);
    }

    function u($content) {
      return ApplicationUtil::url_encode($content);
    }

    // LAYOUT and TEMPLATE
    // for app/views/VIEW/ACTION.php and app/views/layouts/VIEW_layout.php

    function render($options = null) {
      if ($options) {
        $v = new ApplicationView();
        $v->set($options);
        return $v->run();
      }
    }

  }
}

?>
