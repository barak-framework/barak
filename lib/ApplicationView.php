<?php

class ApplicationView extends ApplicationAtimer {

  const LAYOUTPATH = "app/views/layouts/";
  const VIEWPATH   = "app/views/";

  public $layout;
  public $locals;

  // (view & action) || template
  public $template;
  public $view;
  public $action;

  // file
  public $file;

  // partial
  public $partial;

  // text
  public $text;

  // private $_time_start;

  // public function __construct() {
  //   $this->_time_start = microtime(true);
  // }

  public function set($options) {

    if (is_array($options)) {

      foreach ($options as $key => $value) {
        switch ($key) {
          case "partial":  $this->partial  = $value; break;
          case "locals":   $this->locals   = $value; break;
          case "file":     $this->file     = $value; break;
          case "text":     $this->text     = $value; break;
          case "layout":   $this->layout   = $value; break;
          case "view":     $this->view     = $value; break; // default kesin
          case "action":   $this->action   = $value; break; // default kesin
          case "template": $this->template = $value; break;
          default:
          throw new Exception("Render fonksiyonunda bilinmeyen parametre → " . $key);
        }
      }

    } elseif (is_string($options)) {

      $url = explode("/", trim($options, "/"));
      $this->template = (isset($url[1])) ? $options : $this->view . "/" . $url[0];

    } else {
      throw new Exception("Render fonksiyonun bilinmeyen değişken tipi → " . $options);
    }

  }

  public function run() {

    // sets contiune - start
    if (!isset($this->template)) { // is not set ?
      $this->template = $this->view . "/" . $this->action;
    }

    if (!isset($this->layout)) { // is not set ?
      $this->layout = $this->view;
    }

    if (!isset($this->locals)) { // is not set ?
      $this->locals = null;
    }
    // sets contiune - end

    // take content!
    if (isset($this->text)) {

      $content = $this->text;

    } elseif (isset($this->file)) {

      $content = self::_render_file($this->file, $this->locals);

    } elseif (isset($this->partial)) {

      $content = self::_render_file(self::VIEWPATH . preg_replace("~/(?!.*/)~", "/_", $this->partial) . ".php", $this->locals);

    } elseif ($this->layout) { // layout : is not false?

      $content = self::_render_file(self::_layout_file(), ["yield" => self::_render_file(self::_template_file(), $this->locals)]);

    } else { // layout : is false?

      $content = self::_render_file(self::_template_file(), $this->locals);

    }

    // show content!
    return $content;
  }

  private function _layout_file() {

    $layout_file = self::LAYOUTPATH . $this->layout . ".php";

    if (!file_exists($layout_file))
      throw new Exception("Layout dosyası mevcut değil → " . $layout_file);

    return $layout_file;
  }

  private function _template_file() {

    $template_file = self::VIEWPATH . $this->template . ".php";

    if (!file_exists($template_file))
      throw new Exception("Template dosyası mevcut değil → " . $template_file);

    return $template_file;
  }

  private function _render_file($file = null, $locals = null) {

    // https://github.com/betephp/framework/blob/master/src/Bete/View/View.php#L100
    if (!file_exists($file))
      throw new Exception("Render dosyası mevcut değil → " . $file);

    ob_start();
    ob_implicit_flush(false);

    // controller'in localsları var ise yükle
    if (!is_null($locals)) {
      extract($locals, EXTR_OVERWRITE);
    }

    include $file;

    // for ApplicationTimer class
    $this->_timer_message = "  Rendered $file";
    // ApplicationLogger::info("  Rendered $file " . sprintf ("(%.2f ms)", (microtime(true) - $this->_time_start) * 1000));

    return ob_get_clean();
  }

}
?>
