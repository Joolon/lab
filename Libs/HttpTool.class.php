<?php
namespace Libs;

/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2018/4/3
 * Time: 9:12
 */

class HttpTool{


    /**
     * 判断请求URL是否有效
     * @param $url
     * @return bool
     */
    public static function checkUrlIsValid($url){
        $result = get_headers($url,1);
        if(preg_match('/200/',$result[0])){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 验证json的合法性
     * @param $string
     * @return bool
     */
    public static function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


    /**
     * 执行 CURL 请求
     * @param string $curl   地址
     * @param string $method GET|POST
     * @param array  $Data   传输的数据
     * @param array  $header 消息头
     * @param string $type   是否验证账号密码
     * @return mixed
     */
    public static function requestCurlData($curl,$method = 'post', $Data = null , $header = null, $type = null){
        $ch = curl_init(); //初始化
        curl_setopt($ch, CURLOPT_URL, $curl); //设置访问的URL
        curl_setopt($ch, CURLOPT_HEADER, false); // false 设置不需要头信息 如果 true 连头部信息也输出
        curl_setopt($ch, CURLE_FTP_WEIRD_PASV_REPLY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置成秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if($type){
            curl_setopt($ch, CURLOPT_USERPWD, "service:service"); //auth 验证  账号及密码
        }
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //只获取页面内容，但不输出
        if(strtolower($method) == 'post'){
            curl_setopt($ch, CURLOPT_POST, true); //设置请求是POST方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $Data); //设置POST请求的数据
        }
        $response = curl_exec($ch); //执行访问，返回结果

        if(empty($response) and $response === false){
            $error = curl_error($ch);
            var_dump($error);
            exit;
        }

        curl_close($ch); //关闭curl，释放资源

        return $response;
    }


}