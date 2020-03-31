<?php
namespace DevelopModel;

/**
 * Mongo 操作类 封装 单例模式
 * Class MongoHandle
 */
class MongoHandle{

    private static $_handler = null;// Mongo 操作手柄
    private static $_message = null;// 提示信息
    private static $_error   = null;// 错误信息

    private static $_server = "47.107.183.46:27017";

    private function __construct()
    {

    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 获得 Mongo 连接对象
     * @return bool|\MongoDB\Driver\Manager|null
     */
    public static function getMongo(){
        // php7与php其他版本运行mongodb的方式不同，php5的格式可以参照
        if(self::$_handler instanceof \MongoDB\Driver\Manager){
            return self::$_handler;
        }else{

            try{// 判断连接是否成功
                // 实例化对象
                self::$_handler = new \MongoDB\Driver\Manager("mongodb://".self::$_server);
                return self::$_handler;
            }catch(\Exception $e){
                self::setError($e->getMessage());// ERROR
                return false;
            }
        }

        // http://www.runoob.com/mongodb/mongodb-php.html
        // PHP 5 的连接方式
        if(self::$_handler instanceof \MongoClient){
            return self::$_handler;
        }else{
            try{// 判断连接是否成功
                // 实例化对象
                self::$_handler = new \MongoClient();
                $res = self::$_handler->connect('47.107.183.46', 27017);
                if($res){
                    self::setMessage('SUCCESS');
                }else{
                    self::setError('Failed!');// ERROR
                }

                return self::$_handler;
            }catch(\Exception $e){
                self::setError($e->getMessage());// ERROR
                return false;
            }
        }
    }

    /**
     * 设置 提示信息
     * @param $message
     */
    public static function setMessage($message){
        self::$_message = $message;
    }

    /**
     * 获取 提示信息
     * @return null
     */
    public static function getMessage(){
        return self::$_message;
    }

    /**
     * 设置 错误信息
     * @param $error
     */
    public static function setError($error){
        self::$_error = $error;
    }

    /**
     * 获取 错误信息
     * @return null
     */
    public static function getError(){
        return self::$_error;
    }

}
