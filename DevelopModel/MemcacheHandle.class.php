<?php
namespace DevelopModel;


/**
 * Redis 操作类 封装 单例模式
 * Class RedisHandle
 */
class MemcacheHandle{

    private static $_handler = null;// Redis 操作手柄
    private static $_message = null;// 提示信息
    private static $_error   = null;// 错误信息

    private function __construct()
    {

    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 获得 Redis 连接对象
     * @return bool|null|\Memcache
     */
    public static function getMemcache(){
        if(self::$_handler instanceof \Memcache){
            return self::$_handler;
        }else{
            // 实例化对象
            self::$_handler = new \Memcache();

            try{// 判断连接是否成功
                $res = self::$_handler->connect('127.0.0.1', 11211);
                if($res){
                    self::setMessage('SUCCESS');
                }else{
                    self::setError('连接失败');// ERROR
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
