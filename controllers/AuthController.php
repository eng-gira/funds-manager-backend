<?php

include_once INC . "Env.php";
include_once SERVICE . "Auth.php";
include_once MODEL . "User.php";


class AuthController {
    public static function register() {
        $data = json_decode(file_get_contents("php://input"));
        echo file_get_contents('php://input');
        $name = $data->name;
        $email = $data->email;
        $password = $data->password;

        if(User::save($name, $email, $password)) {
            $token = Auth::getTokenForUser(User::findByEmail($email));
            echo json_encode(['data' => $token]);
        }
        else echo json_encode(['message' => 'failed', 'data' => 'Could not authenticate']);
    }
    public static function login() {
        $data = json_decode(file_get_contents("php://input"));
        $email = $data->email;
        $password = $data->password;

        $user = User::validateCreds(['email' => $email, 'password' => $password]);
        if($user) {
            $token = Auth::getTokenForUser($user);
            echo json_encode(['data' => $token]);
        }
        else echo json_encode(['message' => 'failed', 'data' => 'Could not authenticate']);
    }
}