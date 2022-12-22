<?php

include_once INC . "Env.php";

class DB
{
    protected static function connect()
    {
        $host = '127.0.0.1';
        $username = Env::get('MYSQL_USERNAME');
        $pw = Env::get('MYSQL_PASSWORD');
        $dbName = Env::get('MYSQL_DB');

        $conn = new \mysqli($host, $username, $pw, $dbName);

        return $conn;
    }
}
