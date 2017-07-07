<?php

// composer packages load
require "vendor/autoload.php";

// load application
include "lib/Application.php";

$time_start = microtime(true);

// kick application
Application::run();

$time_end = microtime(true);

$time = $time_end - $time_start;

// ApplicationLogger::info("{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} at strftime('%F %T') agent {$_SERVER['HTTP_USER_AGENT']} in {$time}");
?>