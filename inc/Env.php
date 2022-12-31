<?php


class Env {
    private static array $envAssoc = [];
    private static bool $envSaved = false;
    private static string $envFilePath = '.' . DIRECTORY_SEPARATOR . '.env';

    public static function get($key) {
        if(self::$envSaved === false) 
        {
            self::saveEnvFile(self::$envFilePath);
        }

        // return getenv($key);
        // return apache_getenv($key);
        return self::$envAssoc[$key];
    }
    
    private static function saveEnvFile($absFilePath) {
        $envArr = file($absFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($envArr as $line) self::$envAssoc[explode('=', $line)[0]] = explode('=', $line)[1];
        // foreach($envArr as $line) {
        //     // putenv(explode('=', $line)[0] . '=' . explode('=', $line)[1]);
        //     // apache_setenv(explode('=', $line)[0], explode('=', $line)[1]);
        // }

        self::$envSaved = true;
    }


}