<?php
/**
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2017/10/23
 * Time: 10:17
 */
class User
{
    private static $dbcon = null;
    private static $truename = null;

    public static function init(){
        global $dbcon,$truename;
        self::$dbcon = $dbcon;
        self::$truename = $truename;
    }

    /**
     * 获取当前登录用户信息
     * @return array
     */
    public static function getActiveUser(){
        $userInfo = self::getUserInfoByUsername($_SESSION['username']);

        return empty($userInfo)?array():$userInfo;
    }

    /**
     * 获取用户名称列表
     * @param int|bool $active
     * @return array
     */
    public static function getUserList($active = false){
        self::init();
        $userList   = "SELECT distinct username  from ebay_user 
                  where 1 and username!='' ";
        if($active !== false){
            $userList .= " AND active='$active' ";
        }
        $userList .= " order by CONVERT(username USING GBK) asc";

        $userList   = self::$dbcon->execute($userList);
        $userList   = self::$dbcon->getResultArray($userList);

        return empty($userList)?array():$userList;
    }

    /**
     * 获取用户基础信息列表
     * @param int|bool $active 查看用户状态
     * @return array
     */
    public static function getUserInfoList($active = false){
        self::init();

        $userInfoList   = "SELECT username,username2,password,truename,deptname,country,provience,tel,mail,record,ruzhidate,active
                  from ebay_user 
                  where 1 ";
        if($active !== false){
            $userInfoList .= " AND active='$active' ";
        }
        $userInfoList .= " order by CONVERT(username USING GBK) asc";

        $userInfoList   = self::$dbcon->execute($userInfoList);
        $userInfoList   = self::$dbcon->getResultArray($userInfoList);

        return empty($userInfoList)?array():$userInfoList;
    }

    /**
     * 获取用户全信息列表
     * @param int $active 查看用户状态
     * @return array
     */
    public static function getUserAllInfoList($active = false){
        self::init();

        $userInfoList   = "SELECT *  from ebay_user 
                  where 1 ";
        if($active !== false){
            $userInfoList .= " AND active='$active' ";
        }
        $userInfoList .= " order by CONVERT(username USING GBK) asc ";

        $userInfoList   = self::$dbcon->execute($userInfoList);
        $userInfoList   = self::$dbcon->getResultArray($userInfoList);

        return empty($userInfoList)?array():$userInfoList;
    }

    /**
     * 根据用户名获取用户信息
     * @param string|array $username 查询的用户名称
     * @param string $orderBy 排序方式
     * @return array
     */
    public static function getUserInfoByUsername($username,$orderBy = ''){
        self::init();

        if(is_array($username)){
            $username = implode("','",$username);
        }

        $userInfo   = "SELECT username,username2,password,truename,deptname,country,provience,tel,mail,record,ruzhidate,active
                    from ebay_user where username IN('$username') ";
        if($orderBy) $userInfo .= $orderBy;
        $userInfo   = self::$dbcon->execute($userInfo);
        $userInfo   = self::$dbcon->getResultArray($userInfo);

        return empty($userInfo)?array():$userInfo;

    }


    /**
     * 获取 产品资料中开发员列表
     * @return array
     */
    public static function getKaiFaUser(){
        self::init();

        $kfList   = "SELECT distinct salesuser as username  
                    from ebay_goods where salesuser<>'' order by CONVERT(salesuser USING GBK) asc";
        $kfList   = self::$dbcon->execute($kfList);
        $kfList   = self::$dbcon->getResultArray($kfList);

        return empty($kfList)?array():$kfList;
    }


    /**
     * 获取 产品资料中采购员列表
     * @return array
     */
    public static function getCaiGouUser(){
        self::init();

        $cgList     = "SELECT distinct cguser as username  
                    from ebay_goods where cguser<>'' order by CONVERT(cguser USING GBK) asc";
        $cgList     = self::$dbcon->execute($cgList);
        $cgList     = self::$dbcon->getResultArray($cgList);

        return empty($cgList)?array():$cgList;
    }





}