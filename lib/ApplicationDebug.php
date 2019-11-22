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
    self::_render($exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTrace());
  }


  /*
  echo $a;

  or

  ApplicationDebug::error(123123, "Undefined variable: a", "/var/www/html/app/controllers/DefaultController.php", 10);
  */

  public static function error($errno, $message, $file, $line) {
    self::_render($message, $file, $line, debug_backtrace());
  }

  /*
  public function index() {}
  public function index() {}
  // Cannot redeclare HomeController::index()

  */

  public static function shutdown() {
    $error = error_get_last();
    if ($error) {
      ApplicationLogger::fatal("Sistem çalışmasını engelleyecek yanılgı → " . $error["message"]);
      self::error($error["type"], $error["message"], $error["file"], $error["line"]);
    }
  }

  private static function _render($message, $file, $line, $traces) {
    $header = $message;
    $footer = $file . " at line " . $line . PHP_EOL;

    $traces = self::_trace($traces);
    // yanılgı izleri
    ApplicationLogger::warning(implode(PHP_EOL, $traces));

    list($numbers, $rows) = self::_read_in_range_of_file($file, $line);

    $body = (self::$_debug) ? self::_layout($header, $numbers, $rows, $footer, $line, $traces) : NULL;

    $response = new ApplicationResponse();
    $response->status_code = 500;
    $response->body = $body;
    $response->run();
    // show response status
    ApplicationDispatcher::completed($response->status());
    // write error detail for log
    ApplicationLogger::error("$header → $footer");
    ApplicationLogger::warning(implode(PHP_EOL, $rows));
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

  private static function _trace($trace) {
    $traces = [];
    foreach ($trace as $key => $value) {

      $file = isset($value["file"]) ? $value["file"] : "";
      $line = isset($value["line"]) ? $value["line"] : "";
      $class = isset($value["class"]) ? $value["class"] : "";
      $type = isset($value["type"]) ? $value["type"] : "";
      $function = isset($value["function"]) ? $value["function"] : "";
      // args is a array, but values object or string
      // $args = isset($value["args"]) ? $value["args"] : "";

      $traces[] = "{$file}:{$line} in {$class}{$type}{$function}";
    }

    return $traces;
  }

  private static function _layout($header, $numbers, $rows, $footer, $line, $traces) {

    /* coloring debug and other rows */
    $debug_index = array_search($line, $numbers);
    $_rows = [];
    foreach ($rows as $index => $row)
      $_rows[$index] = ($debug_index == $index) ? "<div class='debugrow'> $row </div>" : "<div class='otherrow'> $row </div>";

    /* traces with index */
    $count_traces = count($traces);
    foreach ($traces as $index => $value)
      $traces[$index] = $count_traces-- . "→ {$value}";

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

      .traces { float: right;  width: 96%%; border-radius: 5px; background-color: #f4f2f8; }
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

      <div class='box'>

      <div class='header'>Yanılgı İzleri</div>
      <div class='content'><div class='traces'>%s</div></div>

      </div>

      </body>
      </html>
      ",
      $header, implode("<br/>", $numbers), implode("<br/>", $_rows), $footer, implode("<br/>", $traces));
  }

}
?>
