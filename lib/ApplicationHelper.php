<?php

class ApplicationHelper {

  public static function extract() {

    function scope() {

      // İzin verilmiş route'ları routes'a yükle
      $permitted_packages = func_get_args();
      $path = $permitted_packages[0];

      $permitted_packages = array_slice($permitted_packages, 1);

      $routes = [];
      foreach ($permitted_packages as $permitted_package) {
        foreach ($permitted_package as $permitted_route) {

          $permitted_route->_path = $path;

          if ($permitted_route->_match) {
            $permitted_route->_match_rule = $path . $permitted_route->_match_rule;
          }

          $permitted_route->_rule = $path . $permitted_route->_rule;
          $routes[] = $permitted_route;
        }
      }

      return $routes;
    }

    function resource($table, $path = null) {
      return [
        new ApplicationRoute("get",  "$table",         "$table#index", false, $path),  // all record
        new ApplicationRoute("get",  "$table/create",  false,          false, $path),  // new record form
        new ApplicationRoute("post", "$table/save",    false,          false, $path),  // new record create
        new ApplicationRoute("get",  "$table/show/",   false,          false, $path),  // display record
        new ApplicationRoute("get",  "$table/edit/",   false,          false, $path),  // edit record
        new ApplicationRoute("post", "$table/update",  false,          false, $path),  // update record
        new ApplicationRoute("post", "$table/destroy", false,          false, $path)   // destroy record
        ];
      }

      function resources($table, $path = null) {
        return [
      new ApplicationRoute("get",  "$table",          "$table#index", false, $path), // all record
      new ApplicationRoute("get",  "$table/create",   false,          false, $path), // new record form
      new ApplicationRoute("post", "$table/save",     false,          false, $path), // new record create
      new ApplicationRoute("get",  "$table/show/:id", "$table#show",  true,  $path), // display record
      new ApplicationRoute("get",  "$table/edit/:id", "$table#edit",  true,  $path), // edit record
      new ApplicationRoute("post", "$table/update",   false,          false, $path), // update record
      new ApplicationRoute("post", "$table/destroy",  false,          false, $path)  // destroy record
      ];
    }

    function root($target = false, $path = null) {
      if (!$target)
        throw new ConfigurationException("Root route özelliğinde hedef (controlller#action) belirtilmek zorundadır!", "root");

      return new ApplicationRoute("get", "/", $target, false, $path);
    }

    function post($rule, $target = false, $path = null) {
      return new ApplicationRoute("post", $rule, $target, (strpos($rule, ":") ? true : false), $path);
    }

    function get($rule, $target = false, $path = null) {
      return new ApplicationRoute("get",  $rule, $target, (strpos($rule, ":") ? true : false), $path);
    }

    function _404() {

      return "

      <!DOCTYPE html>
      <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='tr' lang='tr'>
      <head>
      <meta http-equiv='content-type' content='text/html; charset=utf-8' />
      <meta http-equiv='X-UA-Compatible' content='IE=edge' />
      <meta name='viewport' content='width=device-width, initial-scale=1' />
      <title>404 Page</title>
      <style>
      body { background-color:#eee; }
      div {
        text-align: center;
        max-width: 33em;
        margin: 4em auto 0;
        border-bottom: 3px solid #f07746;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.3);
        border-radius: 12px;
        background-color: #ddd;
      }
      h1 { color: #a94442; }
      h3, * { color: #31708f; }
      </style>
      </head>
      <body>

      <div>
      <h1>Oops!</h1>
      <h3>Maalesef bir hata oluştu, istenen sayfa bulunamadı!<br/><a href='/'>Anasayfa</a></h3>
      </div>

      </body>
      </html>
      ";
    }

    // LOCALES

    function t($_word) {
      $words = explode(".", $_word);
      $t_word = "";
      foreach ($words as $word) {
        $t_word = ($t_word == "") ? $_SESSION["i18n"]->$word : $t_word[$word];
      }

      return $t_word;
    }

    // LAYOUT and TEMPLATE
    // for app/views/VIEW/ACTION.php and app/views/layouts/VIEW_layout.php

    function render($options = null) {
      if ($options) {
        $v = new ApplicationView();
        $v->set($options);
        $v->run();
      }
    }

  }
}

?>
