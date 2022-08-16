<?php
ini_set('display_errors', 1);

include_once dirname(__DIR__) . "core" . DIRECTORY_SEPARATOR . "Application.php";

define("ROOT", dirname(__DIR__) . DIRECTORY_SEPARATOR);

define("MODEL", ROOT . "models" . DIRECTORY_SEPARATOR);
define("DATA", ROOT . "data" . DIRECTORY_SEPARATOR);
define("CONTROLLER", ROOT . "controllers" . DIRECTORY_SEPARATOR);


new Application;
