<?php


class DB
{
    protected static function connect()
    {
        $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
        $host = $url["host"];
        $username = $url["user"];
        $pw = $url["pass"];
        $dbName = substr($url["path"], 1);

        var_dump("$url<br>$host<br>$username<br>$pw<br>$dbName");

        $conn = new \mysqli($host, $username, $pw, $dbName);


        return $conn;
    }
}
