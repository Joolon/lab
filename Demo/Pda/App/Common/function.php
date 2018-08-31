<?php
/**
 * Created by Lon
 * User: Lon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 22:03
 */

// 成功  API请求返回数据
function api_success($list = array()){
    global $apiErrType;
    $ret = array('code' => '0X0000','list' => $list);
    return $ret;
}

// 失败  API请求返回数据
function api_error($err_type){
    global $apiErrType;
    $ret = array('code' => '0X0001','msg' => isset($apiErrType[$err_type])?$apiErrType[$err_type]:$err_type);
    return $ret;
}

// API请求返回输出数据
function api_return($data){
    echo json_encode($data);
    exit;
}

// 生成 URL
function myUrl($url_str){
    return 'pda.php?'.$url_str;
}

// 包含页面模板文件
function containTmp($filename,$ext = '.php'){
    if(file_exists(PDA_TPL.$filename.$ext)){
        include_once PDA_TPL.$filename.$ext;
    }else{
        return false;
    }
}

// 解析URL（URL字符串 转成数组格式）
function urlStrToArray($url_str){
    $url_str = substr($url_str,strpos($url_str,'?')+1);
    $url_arr = explode('&',$url_str);
    $url_arr_tmp = array();
    if($url_arr){
        foreach ($url_arr as $value){
            if($value){
                list($key,$val) = explode('=',$value);
                $url_arr_tmp[$key] = $val;
            }
        }
    }
    unset($url_arr);

    return $url_arr_tmp;
}

// 根据时间戳创建一个 单据编号
function createOrderSn($prefix = 'PDA-'){
    $orderSn = $prefix.date('ymdHis').'-'. mt_rand(100, 999);
    return $orderSn;
}


// 包含公共方法
include_once APP_BASE.'include/tools/arrayfunction.php';