<?php


class DB
{
    private static $host = "localhost";
    private static $username = "root";
    private static $pw = "Root1234";
    private static $dbName = "funds_manager";

    protected static function connect()
    {
        $conn = new \mysqli(self::$host, self::$username, self::$pw, self::$dbName);


        return $conn;
    }
}
