<?php
/**
 * Class AliBaseApi
 * 阿里巴巴 1688批发平台 对接基本API
 * @author:zwl
 * @date 2018-03-05
 */
class AliBaseApi {
    public static $username = '';

    public $base_url    = 'http://gw.open.1688.com/openapi/';
    public $code_sign   = null;
    public $request_url = null;


    public function __construct()
    {
        global $truename;
        self::$username = $truename;
    }

    /**
     * 生成签名
     * @param $urlPath
     * @param $appKey
     * @param $appSecret
     * @param array $params
     * @return null|string
     */
    public function setSignature($urlPath,$appKey,$appSecret,$params = array()){
        $urlPath .= $appKey;//此处请用具体api进行替换

        $sign_str = '';
        //配置参数，请用apiInfo对应的api参数进行替换
        if($params){
            $aliParams = array();
            foreach ($params as $key => $val) {
                $aliParams[] = $key . $val;
            }
            sort($aliParams);
            $sign_str = join('', $aliParams);
        }

        $sign_str = $urlPath . $sign_str;
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appSecret, true)));

        $this->code_sign = $code_sign;
        return $this->code_sign;
    }

    /**
     * 拼接生成请求的URL
     * @param $urlPath
     * @param $appKey
     * @param array $systemData
     * @return null|string
     */
    public function getRequestUrl($urlPath,$appKey,$systemData = array()){

        $systemData['_aop_signature'] = $this->code_sign;
        $url_sub_str    = http_build_query($systemData);
        $this->request_url = $this->base_url.$urlPath.$appKey."?".$url_sub_str;

        return $this->request_url;
    }


    /**
     * POST 方式发送 CURL 请求
     * @param string $url 请求链接
     * @param array $appData  发送的数据
     * @return mixed|string
     */
    public function curlPost($url,$appData = array()){
        $postHeader = array(
            "Content-type: application/json"
        );// 注意设置header，设置出错会导致数据传输不成功
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $postHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        if($appData){
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($appData));
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($curl);
        $error = curl_error($curl);
        if($error){
            return $error;
        }

        // 解析数据
        $resultTmp = json_decode($result,true, 512, JSON_BIGINT_AS_STRING);
        if(empty($resultTmp)){// JSON 解析失败则用 XML解析
            $resultTmp = simplexml_load_string($result);
        }
        unset($result);

        return $resultTmp;
    }

}