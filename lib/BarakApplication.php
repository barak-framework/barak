<?php

class BarakApplication {

  const KERNELPATH = "lib/kernel/";
  const MODULESPATH = "lib/modules/";

  public static function run() {

    // Kernel classes load
    self::_import_dir(self::KERNELPATH);

    // Fatal error handling
    register_shutdown_function('ApplicationDebug::shutdown');

    // Exception handling
    set_exception_handler('ApplicationDebug::exception');

    // Error handling
    set_error_handler('ApplicationDebug::error');

    // Config - start
    // Application, Routers alias functions
    self::_alias_extract_configs_of_application();

    // Include config of application
    // Set Application::$options variable
    // Application::$options["timezone"] etc.
    ApplicationConfig::application();

    // Config init - options
    self::_init_option_kernel();

    // Config init - modules
    self::_init_option_modules();
    // Config - end

    // Alias : get global functions
    ApplicationAlias::extract();

    // Route : load routes in configuration file
    ApplicationConfig::routes();

    // Dispatcher : request dispatch to controller
    ApplicationDispatcher::run();

    // Config close - modules
    self::_close_option_modules();

  }

  private static function _init_option_kernel() {

    // Session options
    if (!strlen(session_id())) {

      // COOKIE: httponly ile JS'in ilgili cookie'yi okuması engelleme ayarı, JS'yi engelle
      ini_set('session.cookie_httponly', 1);

      // for $_SESSION hash kick!
      session_start();

    }

    date_default_timezone_set(Application::$options["timezone"]);
    ApplicationLogger::init(Application::$options["logger"]);
    ApplicationDebug::init(Application::$options["debug"]);
    ApplicationI18n::init(Application::$options["locale"]);
  }

  private static function _init_option_modules() { // ok
    if (Application::$options["model"]) {
      self::_import_dirs([self::MODULESPATH . 'model/', 'app/models/']);
      ApplicationDatabase::init();
    }

    if (Application::$options["mailer"]) {
      self::_import_dirs([self::MODULESPATH . 'mailer/', 'app/mailers/']);
      ApplicationMailer::init();
    }

    if (Application::$options["cacher"]) {
      self::_import_dir(self::MODULESPATH . 'cacher/');
      ApplicationCacher::init();
    }

    if (Application::$options["http"]) {
      self::_import_dir(self::MODULESPATH . 'http/');
    }
  }

  private static function _close_option_modules() { // ok
    // Cacher : close
    if (Application::$options["cacher"]) ApplicationCacher::close();

    // Database : close
    if (Application::$options["model"]) ApplicationDatabase::close();
  }

  private static function _import_dirs($directories) { // ok
    foreach ($directories as $directory)
      self::_import_dir($directory);
  }

  private static function _import_dir($directory) { // ok
    foreach (glob($directory . "*.php") as $class)
      require_once $class;
  }

  private static function _alias_extract_configs_of_application() { // ok

    // APPLICATION

    function set($key, $value) {
      Application::set($key, $value);
    }

    function modules($keys) {
      foreach ($keys as $index => $key) {
        Application::set($key, true);
      }
    }

    // ROUTES

    function scope($path, $routes) {
      return ApplicationRoutes::scope($path, $routes);
    }

    function resource($table, $path = "") {
      return ApplicationRoutes::resource($table, $path);
    }

    function resources($table, $path = "") {
      return ApplicationRoutes::resources($table, $path);
    }

    function root($target = FALSE, $path = "") {
      return ApplicationRoutes::root($target, $path);
    }

    function post($rule, $target = FALSE, $path = "") {
      return ApplicationRoutes::post($rule, $target, $path);
    }

    function get($rule, $target = FALSE, $path = "") {
      return ApplicationRoutes::get($rule, $target, $path);
    }
  }

}
?>
