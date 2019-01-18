<?php

namespace Libs;

/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2018/4/3
 * Time: 9:12
 */

class CurlRequest {

    const SECRET_KEY = 'abc123';// 秘钥

    public function __construct(){

    }

    public function getSecretKey(){
        return self::SECRET_KEY;
    }

    /**
     * 根据时间戳生成 签名字符串
     * @param string $timestamp 时间戳
     * @return array 输入的参数、签名结果
     */
    public function getToken($timestamp){
        $signature = $this->getSignature($timestamp);

        $data['timestamp'] = $timestamp;
        $data['signature'] = $signature;

        return $data;
    }

    /**
     * 验证 签名是否正确
     * @param $params
     * @return bool
     * @example
     *              $params = array(
     *              'timestamp' => 时间戳,
     *              'signature' => 签名
     *              )
     */
    public function validToken($params){
        $timestamp = isset($params['timestamp']) ? $params['timestamp'] : '';
        $signature = isset($params['signature']) ? $params['signature'] : '';

        if($signature === $this->getSignature($timestamp)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 计算签名
     * @param string $timestamp 时间戳
     * @return string 返回签名
     */
    public function getSignature($timestamp){
        $arr['timestamp'] = $timestamp;
        $arr['token']     = $this->getSecretKey();

        sort($arr, SORT_STRING);//按照首字母大小写顺序排序
        $str       = implode($arr);//拼接成字符串
        $signature = md5(sha1($str));//进行加密
        $signature = strtoupper($signature);//转换成大写

        return $signature;
    }

    /**
     * 验证json的合法性
     * @param $string
     * @return bool
     */
    public static function isJson($string){
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * GET 方式发送 CURL 请求
     * @param string $url        请求地址
     * @param array  $parameters 参数（包括token）
     * @return mixed
     */
    public function curlGet($url, $parameters = []){
        if($parameters){
            $url .= '?'.http_build_query($parameters);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($ch);
        $error  = curl_error($ch);
        curl_close($ch);

        if($error)
            return $error;

        return $result;
    }

    /**
     * POST 方式发送 CURL 请求
     * @param string $url   请求链接
     * @param array  $data  发送的数据
     * @param array  $token 授权参数
     * @return mixed|string
     */
    public function curlPost($url, $data = [], $token = []){
        if($token){
            $url .= '?'.http_build_query($token);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        if($data){// 传递参数
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if(0 === strpos(strtolower($url), 'https')){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //从证书中检查SSL加密算法是否存在
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($curl);
        $error  = curl_error($curl);

        if($error)
            return $error;

        return $result;
    }

}