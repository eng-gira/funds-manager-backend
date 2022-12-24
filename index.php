<?php
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');

include_once "core" . DIRECTORY_SEPARATOR . "Application.php";
include_once 'inc' . DIRECTORY_SEPARATOR . 'Env.php';

// Define some constants
define("MODEL",  "models" . DIRECTORY_SEPARATOR);
define("DATA",  "data" . DIRECTORY_SEPARATOR);
define("CONTROLLER",  "controllers" . DIRECTORY_SEPARATOR);
define("INC",  "inc" . DIRECTORY_SEPARATOR);
define("SERVICE",  "services" . DIRECTORY_SEPARATOR);

// Set the timezone
date_default_timezone_set("Africa/Cairo");

// Read the env. variables
Env::readEnvFile('C:\xampp\htdocs\funds-manager-backend\.env.local');

// Setup autoloading explicilty for firebase/php-jwt lib.
spl_autoload_register(function ($class) {
    $classNamespaceArr = explode('\\', $class);
    require __DIR__ . '\vendor\firebase\php-jwt\src\\' . $classNamespaceArr[count($classNamespaceArr)-1] . '.php';
});

// Start the application
new Application;
