<?php

class ApplicationView {

  const LAYOUTPATH = "app/views/layouts/";
  const VIEWPATH   = "app/views/";

  // main template for I18n
  public static $main_template = NULL;
  public static $main_layout = NULL;
  public static $main_locals = NULL;

  // locals & layout
  public $locals = NULL;
  public $layout;

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

  // working time in milliseconds
  private $_time;

  final public function __construct() {
    $this->_time = microtime(true);
  }

  final public function set($options) {

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

  final public function run($main_render = null) {

    // sets contiune - start
    if (!isset($this->template)) { // is not set ?
      $this->template = $this->view . "/" . $this->action;
    }

    if (!isset($this->layout)) { // is not set ?
      $this->layout = $this->view;
    }
    // sets contiune - end

    if ($main_render) {
      $this->locals["flash"] = ApplicationFlash::gets();
      self::$main_template = $this->template;
      self::$main_layout = $this->layout;
      self::$main_locals = $this->locals;
      ApplicationLogger::info("  Rendering " . self::$main_template . ".php within layouts/" . self::$main_layout . ".php");
    }

    // take content!
    if (isset($this->text)) {

      $content = $this->text;

    } elseif (isset($this->file)) {

      $content = self::_render_for_file($this->file, $this->locals);

    } elseif (isset($this->partial)) {

      $content = self::_render_for_file(self::VIEWPATH . preg_replace("~/(?!.*/)~", "/_", $this->partial) . ".php", $this->locals);

    } elseif ($this->layout) { // layout : is not false?

      $template_content = self::_render_for_file(self::_template_file_path(), $this->locals);
      // $yield değişkeni atanmış ise layout'da bunu kullandırma var ise de üzerine yaz
      $this->locals["yield"] = $template_content;
      $content = self::_render_for_file(self::_layout_file_path(), $this->locals);

    } else { // layout : is false?

      $content = self::_render_for_file(self::_template_file_path(), $this->locals);

    }

    // show content!
    return $content;
  }

  private function _layout_file_path() {

    $layout_file = self::LAYOUTPATH . $this->layout . ".php";

    if (!file_exists($layout_file))
      throw new Exception("Layout dosyası mevcut değil → " . $layout_file);

    return $layout_file;
  }

  private function _template_file_path() {

    $template_file = self::VIEWPATH . $this->template . ".php";

    if (!file_exists($template_file))
      throw new Exception("Template dosyası mevcut değil → " . $template_file);

    return $template_file;
  }

  private function _render_for_file($file = null, $locals = null) {

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

    ApplicationLogger::info("  Rendered $file " . sprintf ("(%.2f ms)", (microtime(true) - $this->_time) * 1000));

    return ob_get_clean();
  }

}
?>
