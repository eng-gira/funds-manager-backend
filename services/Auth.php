<?php

include_once INC . "Env.php";

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    /**
     * @param array $user - associative array of user data.
     * 
     * @return string JWT containing $user as payload.
     */
    public static function getTokenForUser(array $user): string {
        return JWT::encode(['user' => $user], Env::get('JWT_SECRET_KEY'), 'HS512');
    }

    /**
     * @param string $token - the JWT.
     * 
     * @return bool
     */
    public static function verifyToken(string $token): bool {
        try {
            
            return (array) JWT::decode($token, new Key(Env::get('JWT_SECRET_KEY'), 'HS512'));

        } catch (ExpiredException $eE) {
            echo json_encode(['message' => 'failed', 'data' => 'token expired. ' . $eE->getMessage()]);

            return false;
        } catch (Exception $e) {
            echo json_encode(['message' => 'failed', 'data' => $e->getMessage()]);

            return false;
        }
    }
}