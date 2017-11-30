<?php

class ApplicationDebug {

  private static $_debug;

  public static function init($debug) {
    self::$_debug = $debug;
  }

  /*
  throw new Exception("OMG!");
  
  or
  
  ApplicationDebug::exception(new Exception("EXCEPTION!"));
  */

  public static function exception($exception) {
    $header = $exception->getMessage();
    $footer = $exception->getFile() . " at line " . $exception->getLine();

    $numbers = "";
    $rows = "";
    foreach ($exception->getTrace() as $number => $value) {
      $numbers .= $number . "<br/>";
      $rows .= "<b>" . (isset($value["class"]) ? ($value["class"] . "→" . $value["function"]) :  $value["function"]) . "</b>" .
      " in " . $value["file"] . " at line " . $value["line"] . "<br/>";
    }

    self::_render($header, $numbers, $rows, $footer);
  }

  /*
  ApplicationDebug::error(123123, "Undefined variable: a", "/var/www/html/app/controllers/DefaultController.php", 10);
  */

  public static function error($errno, $error, $file, $line) {

    $_rows = explode(PHP_EOL, file_get_contents($file));

    $range = 5;
    $start = ($line > $range) ? $line - $range : 0;
    $stop  = $line + $range - 2;

    $numbers = "";
    $rows    = "";
    $footer  = "<b>$file</b> at line <b>$line</b>" . PHP_EOL;
    $header  = "$error" . PHP_EOL;

    for ($number = $start; $number <= $stop; ++$number) {
      if (array_key_exists($number, $_rows)) {

        // escaping a space
        $row = str_replace(' ', '&nbsp;', $_rows[$number]);

        /* escaping a literal <?php and ?> in a PHP script */
        if ($row == "<?php") $row = "&lt;?php";
        if ($row == "?>") $row = "?&gt;";

        if ($number == $line - 1)
          $rows .= "<code style='background-color: #30D5C8; color: #ffffff; display: inline-block; width: 100%'> $row </code><br/>";
        else
          $rows .= "<code style='background-color: #ffffff; color: #665f75;'> $row </code><br/>";

        $numbers .= ($number + 1) . "<br/>";
      }
    }

    self::_render($header, $numbers, $rows, $footer);
  }

  // if a fatal error occurred

  public static function shutdown() {
    $error = error_get_last();
    if (!is_null($error))
      self::error($error['type'], $error['message'], $error['file'], $error['line']);
  }

  // TODO Mailer içersinde sorun olunca buraya düşmüyor :-'(

  private static function _render($header, $numbers, $rows, $footer) {
    ApplicationLogger::debug("$header in $footer");

    ob_get_length() > 0 && ob_get_level() && ob_end_clean();

    $v = new ApplicationView();

    (self::$_debug) ? $v->set(["text" => self::_layout($header, $numbers, $rows, $footer)]) : $v->set(["file" => "public/500.html"]);

    echo $v->run();
    exit();
  }

  private static function _layout($header, $numbers, $rows, $footer) {
    return sprintf("
      <!DOCTYPE html>
      <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='tr' lang='tr'>
      <head>
      <meta http-equiv='content-type' content='text/html; charset=utf-8' />
      <meta http-equiv='X-UA-Compatible' content='IE=edge' />
      <meta name='viewport' content='width=device-width, initial-scale=1' />
      <title>Error Page</title>
      <style>
      body { background-color: #eee; }
      .box {
        color: #241c2c;
        margin: 4em auto 0;
        border: 2px solid #d9d2e8;
        border-radius: 12px;
        background-color: #f4f2f8;
      }
      .header { padding: 1em; }
      .footer { padding: 1em; clear:both; }
      .content {
        border-top: 2px solid #d9d2e8;
        border-bottom: 2px solid #d9d2e8;
        width: 100%%;
        overflow: auto;
      }
      .numbers { float: left; width: 4%%; text-align: center; }
      .rows { float: right;  width: 96%%; border-radius: 5px; background-color: white; }
      </style>
      </head>
      <body>

      <div class='box'>
      <div class='header'>%s</div>
      <div class='content'>
      <div class='numbers'><code>%s</code></div>
      <div class='rows'>%s</div>
      </div>
      <div class='footer'>%s</div>
      </div>

      </body>
      </html>
      ",
      $header, $numbers, $rows, $footer);
}

}
?>
