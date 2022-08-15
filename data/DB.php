<?php


class DB
{
    private static $host = "";
    private static $username = "";
    private static $pw = "";
    private static $dbName = "";

    protected static function connect()
    {
        $conn = new \mysqli(self::$host, self::$username, self::$pw, self::$dbName);


        return $conn;
    }
}
