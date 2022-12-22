<?php


class Env {
    private static array $env = [];

    public static function readEnvFile($absFilePath) {
        $envArr = file($absFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $envAssoc = [];
        foreach($envArr as $line) $envAssoc[explode('=', $line)[0]] = explode('=', $line)[1];

        self::$env = $envAssoc;
    }

    public static function get($key) {
        return self::$env[$key];
    }

}