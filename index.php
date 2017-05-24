<?php

// composer packages load

require "vendor/autoload.php";

// lib/*.php files load
// app/controllers/*.php  files load

// system class files and controller class files
$directories = ['lib/', 'app/controllers/','app/controllers/*/', 'app/models/', 'app/helpers/'];

foreach ($directories as $directory) {

  foreach(glob($directory . "*.php") as $class) {
    include_once $class;
  }
}

// Configuration : sets
ApplicationConfig::run();

// Database : connect and global share
$db = ApplicationConfig::database();
$GLOBALS['db'] = new ApplicationDatabase($db["host"], $db["name"], $db["user"], $db["pass"]);

// model create auto
// foreach (ApplicationSql::tablenames() as $tablename) {
//   eval("class $tablename extends ApplicationModel {}");
// }

// Helper : get global functions
ApplicationHelper::extract();

// Database : seed // OPTIONAL
ApplicationDatabase::seed();

// I18n : locale get // OPTIONAL
if (!isset($_SESSION['i18n']))
  $_SESSION['i18n'] = new ApplicationI18n("tr");

// Route : run configration of route
ApplicationConfig::route();
?>
