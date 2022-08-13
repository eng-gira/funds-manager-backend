<?php

class Application
{
    private $defaultController = "FundController";
    private $defaultAction = "index";
    private $defaultParams = [];

    private $controller = "FundController";
    private $action = "index";
    private $params = [];

    public function __construct()
    {
        include_once CONTROLLER . $this->defaultController . ".php";

        $this->readEndPoint();

        if (file_exists(CONTROLLER . $this->controller . ".php")) {
            include_once CONTROLLER . $this->controller . ".php";

            if (method_exists($this->controller, $this->action)) {
                call_user_func_array([$this->controller, $this->action], $this->params);
            } else {
                echo json_encode(["message" => "action doesn't exist"]);
            }
        } else {
            echo json_encode(["message" => "controller (" . $this->controller . ") doesn't exist"]);
        }
    }

    private function readEndPoint()
    {
        $url = $_SERVER["REQUEST_URI"];

        $indexOfPublic = strpos($url, "api/");

        if ($indexOfPublic === false) return false;

        $publicLengthPlusOne = strlen("api/");

        $endPoint = trim(substr($url, $indexOfPublic + $publicLengthPlusOne), "/");
        $endPointArr = explode("/", $endPoint);
        if (count($endPointArr) > 0) $this->controller = strlen($endPointArr[0]) > 0 ? $endPointArr[0] . "Controller" : $this->defaultController;
        if (count($endPointArr) > 1) $this->action = strlen($endPointArr[1]) > 0 ? $endPointArr[1] : $this->defaultAction;
        if (count($endPointArr) > 2) $this->params = array_values(array_slice($endPointArr, 2));
    }
}
