<?php

class ApplicationHelper {

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

    // function get_real_ip_address() {
    //   if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    //   elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    //   else return $_SERVER['REMOTE_ADDR'];
    // }

    // LAYOUT and TEMPLATE
    // for app/views/VIEW/ACTION.php and app/views/layouts/VIEW_layout.php

    function render($options = null) {
      if ($options) {
        $v = new ApplicationView();
        $v->set($options);
        return $v->run();
      }
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

  }
}

?>
