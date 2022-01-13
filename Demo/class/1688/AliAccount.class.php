<?php

/**
 * Class Ali1688Account
 * 阿里巴巴1688批发采购平台 账号
 * @author:zwl
 * @date 2018-03-05
 */
class AliAccount {

    public static $account          = null;
    public static $accountInfo      = null;
    public static $appKey           = null;
    public static $appSecret        = null;
    public static $accessToken      = null;

    /**
     * 获得当前激活的账号名称
     * @return null
     */
    public static function getActiveAccount(){
        return self::$account;
    }

    /**
     * 设置当前操作的目标账号
     * @param $account
     */
    public static function setAccount($account){
        self::$account = $account;
        self::setAccountInfo();
    }

    /**
     * 设置当前激活的账号的用户基本信息
     * @return bool
     */
    public static function setAccountInfo(){
        $where = "ebay_account='".self::$account."'";
        $field = 'ebay_account,1688_client_id,1688_client_secret,1688_access_token,1688_refresh_token,1688_access_token_timeout';
        $accountInfo = DB::Find('ebay_account',$where,$field);
//        print_r($accountInfo);exit;

        self::$accountInfo = $accountInfo;
        if(empty(self::$accountInfo)) return false;

        self::$appKey = self::$accountInfo['1688_client_id'];
        self::$appSecret = self::$accountInfo['1688_client_secret'];
        self::$accessToken = self::$accountInfo['1688_access_token'];


        //self::$accessToken = '03bb9a26-f781-4e07-9a25-5842881df6bb';

		return true;
    }






}