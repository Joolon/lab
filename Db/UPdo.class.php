<?php
namespace Db;

/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/8/29
 * Time: 11:31
 */

class UPdo extends \PDO
{

    private static $_dbms = 'mysql';
    private static $_instance = null;
    private $sql = null;

    public function __construct()
    {
        $host = C('DB_HOST');
        $dbName = C('DB_NAME');
        $username = C('DB_USER');
        $passwd = C('DB_PWD');

        $dsn = self::$_dbms.":host=$host;dbname=$dbName";

        parent::__construct($dsn, $username, $passwd);
    }

    public static function getInstance(){

        if( !self::$_instance instanceof self){
            self::$_instance = new self;
            var_dump(self::$_instance);

        }
        return self::$_instance;
    }

//    public function



}