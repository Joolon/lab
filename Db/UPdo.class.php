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

    /**
     * UPdo constructor.
     * 单例模式：不能实例化对象，构造方法必须私有化
     */
    private function __construct(){
        // TODO: Implement __construct() method.
    }

    /**
     * 单例模式：不能使用克隆
     */
    private function __clone(){
        // TODO: Implement __clone() method.
    }

    /**
     * 获得 实例的方法
     *      因为不能实例化对象 所以必须是静态方法
     * @return \PDO|null
     */
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