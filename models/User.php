<?php

require_once './data/DB.php';
include_once MODEL . "Model.php";

class User extends DB implements Model
{
    public static function all() {
        $users = [];

        $conn = DB::connect();

        $sql = "SELECT id, name, email FROM users";
        $result = $conn->query($sql);
        if ($result->num_rows != 0) {
            while ($row = $result->fetch_assoc()) {
                $users[count($users)] = [
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "email" => $row["email"],
                ];
            }

            return $users;
        }

        return false;        
    }
    public static function findByEmail(string $email) {
        $conn = DB::connect();
        $sql = "SELECT id, name, email FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows != 0) {
                    $stmt->bind_result(
                        $id,
                        $name,
                        $email,
                    );
                    $stmt->fetch();

                    return [
                        "id" => $id, "name" => $name, "email" => $email
                    ];
                }
            }
        }

        return false;
    }
    public static function find($id) {
        $conn = DB::connect();
        $sql = "SELECT id, name, email FROM users WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", intval($id));

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows != 0) {
                    $stmt->bind_result(
                        $id,
                        $name,
                        $email,
                    );
                    $stmt->fetch();

                    return [
                        "id" => $id, "name" => $name, "email" => $email
                    ];
                }
            }
        }

        return false;        
    }
    public static function validateCreds(array $credentials): array|false {
        if(!isset($credentials['email'], $credentials['password'])) return false;
        $conn = DB::connect();
        $sql = "SELECT * FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $credentials['email']);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows != 0) {
                    $stmt->bind_result(
                        $id,
                        $name,
                        $email,
                        $password
                    );
                    $stmt->fetch();

                    if($password != md5($credentials['password'])) return false;

                    return [
                        "id" => $id, "name" => $name, "email" => $email, 'password' => $password
                    ];
                }
            } 
        }
        return false;
    }

    public static function save($name, $email, $password) {
        if(self::findByEmail($email) !== false) return false;

        $conn = DB::connect();
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $pw = md5($password);
            $stmt->bind_param("sss", $name, $email, $pw);
            if ($stmt->execute()) return true;
        }

        return false;
    }
    public static function delete() {}
}