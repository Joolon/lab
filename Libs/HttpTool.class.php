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



    /**
     * 根据参数 生成 token
     * @param $sku_list
     * @return array|string
     */
    public function create_access_token($params){
        $params    = $this->format_params();
        $params    = $this->ascSort($params);
        $new_token = strtolower(md5($this->createLinkString($params).$this->_api_secret));

        $params['token'] = $new_token;

        return $params;
    }


    public function ascSort($para = ''){
        if(is_array($para)){
            ksort($para);
            reset($para);
        }

        return $para;
    }

    /**
     * 格式化参数
     * @return array
     */
    private function format_params(){

        $params = [


        ];

        return $params;
    }

    /**
     * 参数加密拼接方法
     * @param $para
     * @return bool|string
     */
    private function createLinkString($para){
        $arg = "";
        foreach($para as $key => $val){
            if($val === '' || $val === null)
                continue;
            if(is_array($val))
                $val = json_encode($val);
            $arg .= $key."=".urlencode($val)."&";
        }

        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){
            $arg = stripslashes($arg);
        }

        return $arg;
    }


}