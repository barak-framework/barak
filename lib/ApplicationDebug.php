<?php
class ApplicationDebug {

  private static $_debug = false;

  public static function init($debug) {
    self::$_debug = $debug;
  }

  /*
  throw new Exception("OMG!");

  or

  ApplicationDebug::exception(new Exception("EXCEPTION!"));
  */

  public static function exception($exception) {
    $file = $exception->getFile();
    $line = $exception->getLine();

    $header = $exception->getMessage();
    $footer = $file . " at line " . $line . PHP_EOL;

    $file_steps = [];
    foreach ($exception->getTrace() as $number => $value) {
      $fileline = $value["file"] . ":" . $value["line"] . " in ";
      $file_steps [] = (isset($value["class"])) ? ($fileline . $value["class"] . "#" . $value["function"] ) : $fileline . $value["function"];
    }

    list($numbers, $rows) = self::_read_in_range_of_file($file, $line);
    self::_render($header, $numbers, $rows, $footer, $line);
  }

  /*
  echo $a;

  or

  ApplicationDebug::error(123123, "Undefined variable: a", "/var/www/html/app/controllers/DefaultController.php", 10);
  */

  public static function error($errno, $message, $file, $line) {
    $header = $message;
    $footer = $file . " at line " . $line . PHP_EOL;

    list($numbers, $rows) = self::_read_in_range_of_file($file, $line);
    self::_render($header, $numbers, $rows, $footer, $line);
  }

  // if a fatal error occurred

  public static function shutdown() {
    $error = error_get_last();
    if (!is_null($error)) {
      ApplicationLogger::fatal("Sistem çalışmasını engelleyecek hata → " . $error["message"]);
      self::error($error["type"], $error["message"], $error["file"], $error["line"]);
    }
  }

  private static function _render($header, $numbers, $rows, $footer, $line) {
    ApplicationLogger::error("$header → $footer");
    ApplicationLogger::warning(implode(PHP_EOL, $rows));

    $body = (self::$_debug) ? self::_layout($header, $numbers, $rows, $footer, $line) : NULL;

    $response = new ApplicationResponse();
    $response->status_code = 500;
    $response->body = $body;
    $response->send();
    exit();
  }

  private static function _read_in_range_of_file($filename, $line) {

    $range = 5; /* before and after brifing lines */
    $start = ($line > $range) ? $line - $range - 1 : 0;
    $stop  = $range * 2 + 1;

    $file = new SplFileObject($filename);
    $file_iterator = new LimitIterator($file, $start, $stop);

    $rows = [];
    $numbers = [];
    foreach ($file_iterator as $number => $row) {

      /* escaping a literal `<?=`, `<?php`, `?>` in a PHP script tags */
      $row = str_replace("<?=", "&lt;?=", $row);
      $row = str_replace("<?php", "&lt;?php", $row);
      $row = str_replace("?>", "?&gt;", $row);

      $numbers[] = $number + 1;
      $rows[] = $row;
    }

    return [$numbers, $rows];
  }

  private static function _layout($header, $numbers, $rows, $footer, $line) {

    /* coloring debug and other rows */
    $debug_index = array_search($line, $numbers);
    $_rows = [];
    foreach ($rows as $index => $row)
      $_rows[$index] = ($debug_index == $index) ? "<div class='debugrow'> $row </div>" : "<div class='otherrow'> $row </div>";

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
      .debugrow { background-color: #30D5C8; color: #ffffff; display: inline-block; width:100%%; }
      .otherrow { background-color: #ffffff; color: #665f75; display: inline-block; width:100%%; }
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
      $header, implode("<br/>", $numbers), implode("<br/>", $_rows), $footer);
}

}
?>
