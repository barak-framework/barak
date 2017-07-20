<?php

class Application {

  public static function run() {

    // system class, models, mailers
    $directories = ['lib/', 'app/models/', 'app/mailers/'];

    foreach ($directories as $directory) {
      foreach (glob($directory . "*.php") as $class) {
        require_once $class;
      }
    }

    // Database : connect
    ApplicationDatabase::connect();

    // Database : seed
    ApplicationDatabase::seed();

    // Alias : get global functions
    ApplicationAlias::extract();

    // Configuration : sets
    ApplicationConfig::sets();

    // Route : run configration of route
    ApplicationConfig::route();

    // Database : close
    ApplicationDatabase::close();
  }
}

?>
