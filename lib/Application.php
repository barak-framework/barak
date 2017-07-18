<?php


class Application {

  public static function run() {

    // system class files and controller class files
    $directories = ['lib/', 'app/models/', 'app/mailers/'];

    foreach ($directories as $directory) {
      foreach (glob($directory . "*.php") as $class) {
        include_once $class;
      }
    }

    // Database : connection
    ApplicationDatabase::connect();
    
    // Database : seed
    ApplicationDatabase::seed();

    // Helper : get global functions
    ApplicationHelper::extract();

    // Configuration : sets
    ApplicationConfig::sets();

    // Route : run configration of route
    ApplicationConfig::route();

    // Database : connection close
    ApplicationDatabase::close();
  }
}

?>
