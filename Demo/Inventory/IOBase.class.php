<?php

class IOBase
{
    public static $username     = null;
    public static $error        = null;
    public static $success      = null;


    public static function init(){
        if(empty(self::$username)){
            self::$username = $_SESSION['truename']?$_SESSION['truename']:'缺失';
        }
    }

    /**
     * 获取错误提示信息(操作失败才有)
     * @return string
     */
    public static function getError(){
        return self::$error;
    }

    /**
     * 获取成功提示信息(操作成功才有)
     * @return string
     */
    public static function getSuccess(){
        return self::$success;
    }

}