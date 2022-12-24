<?php


class Env {
    private static array $env = [];
    private static string $envFilePath = 'C:\xampp\htdocs\funds-manager-backend\.env';

    public static function get($key) {
        if(count(self::$env) < 1) self::readEnvFile(self::$envFilePath);
        return self::$env[$key];
    }
    
    private static function readEnvFile($absFilePath) {
        $envArr = file($absFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $envAssoc = [];
        foreach($envArr as $line) $envAssoc[explode('=', $line)[0]] = explode('=', $line)[1];

        self::$env = $envAssoc;
    }


}