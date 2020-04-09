<?php

namespace Db;

/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/8/29
 * Time: 11:31
 */
class UPdo {

    private static $_dbms     = 'mysql';
    private static $_instance = null;

    private function __construct(){
        // TODO: Implement __construct() method.
    }

    private function __clone(){
        // TODO: Implement __clone() method.
    }

    public static function getInstance(){
        if(!self::$_instance instanceof \PDO){
            $host     = C('DB_HOST');
            $dbName   = C('DB_NAME');
            $username = C('DB_USER');
            $passwd   = C('DB_PWD');

            $dsn = self::$_dbms.":host=$host;dbname=$dbName";
            self::$_instance = new \PDO($dsn, $username, $passwd);
        }

        return self::$_instance;
    }

}