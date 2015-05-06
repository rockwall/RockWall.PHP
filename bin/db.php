<?php

function db() {
    return db::getConnection();
}

class db {
    private static $db = null;

    private static
    function connect() {
        $db = config::db();
        $connection = new PDO('mysql:host='.$db->host.';dbname='.$db->name, $db->user, $db->pasw, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    }

    public static
    function getConnection() {
        if (self::$db === null) self::$db = self::connect();
        return self::$db;
    }
}