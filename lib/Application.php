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

    // Fatal error handling
    register_shutdown_function('ApplicationDebug::shutdown');

    // Exception handling
    set_exception_handler('ApplicationDebug::exception');

    // Error handling
    set_error_handler('ApplicationDebug::error');

    // Configuration : sets
    ApplicationConfig::sets();

    // Database : connect
    ApplicationDatabase::connect();

    // Database : seed
    ApplicationDatabase::seed();

    // Alias : get global functions
    ApplicationAlias::extract();

    // Route : run configration of route
    ApplicationConfig::route();

    // Database : close
    ApplicationDatabase::close();
  }
}

?>
