<?php
class DefaultController extends ApplicationController {

  public function index() {
    $this->title = "Browse the Guide";
    $this->description = "Read guides to learn!";
    $this->repo = "https://github.com/gdemir/barak";
    $this->guide = "https://github.com/gdemir/barak/blob/master/README.md";


    $this->guides = [
    [
    "title" => "Router",
    "directory" =>"config/routes.php",
    "link" => "https://github.com/gdemir/barak#router-configroutesphp",
    ],
    [
    "title" => "Controller",
    "directory" =>"app/controllers/*.php",
    "link" => "https://github.com/gdemir/barak#controller-appcontrollerphp",
    ],
    [
    "title" => "Views",
    "directory" =>"app/views/DIRECTORY/*.php",
    "link" => "https://github.com/gdemir/barak#views-appviewsdirectoryphp",
    ],
    [
    "title" => "Model",
    "directory" =>"app/models/TABLE.php",
    "link" => "https://github.com/gdemir/barak#model-appmodelstablephp",
    ],
    [
    "title" => "Configuration",
    "directory" =>"config/application.ini, config/database.ini",
    "link" => "https://github.com/gdemir/barak#configurations-configdatabaseini-configapplicationini",
    ],
    [
    "title" => "Seeds",
    "directory" =>"db/seeds.php",
    "link" => "https://github.com/gdemir/barak#seeds-dbseedsphp",
    ],
    ];


  }

}
?>