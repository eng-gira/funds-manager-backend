<?php
include_once dirname(__DIR__) . "\\funds-manager-backend\\core\\Application.php";

define("ROOT", dirname(__DIR__) . "\\funds-manager-backend" . DIRECTORY_SEPARATOR);

define("MODEL", ROOT . "models" . DIRECTORY_SEPARATOR);
define("DATA", ROOT . "data" . DIRECTORY_SEPARATOR);
define("CONTROLLER", ROOT . "controllers" . DIRECTORY_SEPARATOR);


new Application;
