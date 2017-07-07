<?php

class ApplicationController {

  const CONTROLLERPATH = "app/controllers/";

  public $_locals = [];
  public $_render;

  public function _filter($action, $filter_actions) {

    foreach ($filter_actions as $filter_action) {

      if (array_key_exists(0, $filter_action)) {
        $filter_action_name = $filter_action[0];
        if (method_exists($this, $filter_action_name)) {
          if (array_key_exists("only", $filter_action)) {
            if (in_array($action, $filter_action["only"]))
              $this->{$filter_action_name}();
          } elseif (array_key_exists("except", $filter_action)) {
            if (!in_array($action, $filter_action["except"]))
              $this->{$filter_action_name}();
          } elseif (!array_key_exists("only", $filter_action) and !array_key_exists("except", $filter_action)) {
            $this->{$filter_action_name}();
          }
        }
      }

    }
  }

  public function run($action) {

    if (isset($this->helper)) foreach ($this->helper as $helper) include "app/helpers/" . $helper . "Helper.php";

    if (isset($this->before_action)) $this->_filter($action, $this->before_action);

    if (method_exists($this, $action)) $this->$action();

    if (isset($this->after_action)) $this->_filter($action, $this->after_action);
  }

  public function render($options) {
    $this->_render = $options;
  }

  public function redirect_to($url) {
    exit(header("Location: http://" . $_SERVER['SERVER_NAME'] . "/" . trim($url, "/"), false, 303));
  }

  public function __get($local) {
    return $this->_locals[$local];
  }

  public function __set($local, $value) {
    $this->_locals[$local] = $value;
  }

  public static function load_file($file, $path = "") {
    $controller_class = ucwords($file) . "Controller";
    $controller_path  = self::CONTROLLERPATH . trim($path,"/") . "/" . $controller_class . '.php';
    if (!file_exists($controller_path))
      throw new FileNotFoundException("Controller dosyası mevcut değil", $controller_path);

    include $controller_path;

    if (!class_exists($controller_class))
      throw new FileNotFoundException("Controller sınıfı yüklenemedi", $controller_class);
  }
}
?>
