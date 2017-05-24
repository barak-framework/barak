<?php

class ApplicationView {

  const LAYOUTPATH = "app/views/layouts/";
  const VIEWPATH   = "app/views/";

  private $_layout;
  private $_template;

  private $_view;
  private $_action;

  private $_text;
  private $_file;
  private $_partial;

  private $_locals;

  public function __construct() {}

  public function set($_render) {

    if (is_array(($_render))) {

      foreach ($_render as $key => $value) {
        switch ($key) {
          case "partial":  $this->_partial  = $value; break;
          case "locals":   $this->_locals   = $value; break;
          case "file":     $this->_file     = $value; break;
          case "text":     $this->_text     = $value; break;
          case "layout":   $this->_layout   = $value; break;
          case "view":     $this->_view     = $value; break; // default kesin
          case "action":   $this->_action   = $value; break; // default kesin
          case "template": $this->_template = $value; break;
          default:
          throw new ViewNotFoundException("Render fonksiyonunda bilinmeyen parametre", $key);
        }
      }

    } elseif (is_string($_render)) {

      $url = explode("/", trim($_render, "/"));
      $this->_template = (isset($url[1])) ? $_render : $this->_view . "/" . $url[0];

    } else {
      throw new ViewNotFoundException("Render fonksiyonun bilinmeyen değişken tipi", $this->_render);
    }

  }

  public function run() {

    // sets contiune - start
    if (!isset($this->_template)) { // is not set ?
      $this->_template = $this->_view . "/" . $this->_action;
    }

    if (!isset($this->_layout)) { // is not set ?
      $this->_layout = $this->_view;
    }

    if (!isset($this->_locals)) { // is not set ?
      $this->_locals = null;
    }
    // sets contiune - end

    // take content!
    if (isset($this->_text)) {

      $content = $this->_text;

    } elseif (isset($this->_file)) {

      $content = self::render_file($this->_file, $this->_locals);

    } elseif (isset($this->_partial)) {

      $content = self::render_file(self::VIEWPATH . preg_replace("~/(?!.*/)~", "/_", $this->_partial) . ".php", $this->_locals);

    } elseif ($this->_layout) { // layout : is not false?

      $content = self::render_file(self::layout_file(), ["yield" => self::render_file(self::template_file(), $this->_locals)]);

    } else { // layout : is false?

      $content = self::render_file(self::template_file(), $this->_locals);

    }

    // show content!
    self::display($content, $this->_locals);
  }

  private function layout_file() {

    $layout_path = self::LAYOUTPATH . trim($this->_layout, "/") . ".php";

    if (!file_exists($layout_path))
      throw new FileNotFoundException("Layout dosyası mevcut değil", $layout_path);

    return $layout_path;
  }

  private function template_file($path = self::VIEWPATH) {

    $template_path = $path . trim($this->_template, "/") . ".php";

    if (!file_exists($template_path))
      throw new FileNotFoundException("Template dosyası mevcut değil", $template_path);

    return $template_path;
  }

  private function render_file($file = null, $locals = null) {

    // https://github.com/betephp/framework/blob/master/src/Bete/View/View.php#L100
    if (!file_exists($file))
      throw new FileNotFoundException("Render dosyası mevcut değil", $file);

    ob_start();
    ob_implicit_flush(false);

    // controller'in localsları var ise yükle
    if (!is_null($locals)) {
      extract($locals, EXTR_OVERWRITE);
    }

    include($file);
    return ob_get_clean();
  }

  private function display($content) {
    echo $content;
  }
}

?>
