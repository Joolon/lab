<?php
namespace Libs;

/**
 * Class BaseApi
 * API 对接基类
 * @author:Lon
 */
class BaseApi {


    /**
     * 验证json的合法性
     * @param $string
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * GET 方式发送 CURL 请求
     * @param $url
     * @param array $parameters
     * @return mixed
     */
    public static function curlGet($url,$parameters = array()){
        if($parameters){
            $url .= '?'.http_build_query($parameters);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch) ;

        if($error) return $error;

        return $result;
    }

    /**
     * POST 方式发送 CURL 请求
     * @param string $url 请求链接
     * @param array $data  发送的数据
     * @return mixed|string
     */
    public static function curlPost($url,$data = array()){
        $postHeader = array("Content-type: application/json");// 注意设置header，设置出错会导致数据传输不成功
        $data = is_array($data)?json_encode($data):$data;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $postHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($data));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($curl);
        $error = curl_error($curl);

        if($error) return $error;

        return $result;
    }

}