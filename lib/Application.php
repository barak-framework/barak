<?php


class Application {

  public static function run() {

    // system class files and controller class files
    // $directories = ['lib/', 'app/controllers/','app/controllers/*/', 'app/models/', 'app/helpers/'];
    $directories = ['lib/', 'app/models/'];

    foreach ($directories as $directory) {
      foreach(glob($directory . "*.php") as $class) {
        include_once $class;
      }
    }

    // Database : connect and global share
    $GLOBALS['_db'] = new ApplicationDatabase();

    // Database : seed
    ApplicationDatabase::seed();

    // Helper : get global functions
    ApplicationHelper::extract();

    // Configuration : sets
    ApplicationConfig::sets();

    // Route : run configration of route
    ApplicationConfig::route();
  }

}

?>