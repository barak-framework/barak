<?php
class DefaultController extends ApplicationController {

  public function index() {
    $this->title = "Browse the Guide";
    $this->description = "Read guides to learn!";
    $this->repo = "https://github.com/barak-framework/barak";
    $this->guide = "https://github.com/barak-framework/barak/blob/master/README.md";
    $this->site = "http://gdemir.me/barak-framework";
  }

}
?>