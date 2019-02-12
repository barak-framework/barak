<?php
class DefaultController extends ApplicationController {
  public function index() {
    $this->title = "Öğrenmek için dokümantasyonu oku!";
    $this->repo = "https://github.com/barak-framework/barak";
    $this->site = "http://barak-framework.github.io";
  }
}
?>
