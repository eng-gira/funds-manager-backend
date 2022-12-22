<?php
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');

include_once "core" . DIRECTORY_SEPARATOR . "Application.php";
include_once 'inc' . DIRECTORY_SEPARATOR . 'Env.php';
// define("ROOT", dirname(__DIR__) . DIRECTORY_SEPARATOR);

// define("MODEL", ROOT . "models" . DIRECTORY_SEPARATOR);
// define("DATA", ROOT . "data" . DIRECTORY_SEPARATOR);
// define("CONTROLLER", ROOT . "controllers" . DIRECTORY_SEPARATOR);

define("MODEL",  "models" . DIRECTORY_SEPARATOR);
define("DATA",  "data" . DIRECTORY_SEPARATOR);
define("CONTROLLER",  "controllers" . DIRECTORY_SEPARATOR);
define("INC",  "inc" . DIRECTORY_SEPARATOR);


date_default_timezone_set("Africa/Cairo");


Env::readEnvFile('C:\xampp\htdocs\funds-manager-backend\.env.local');

new Application;
