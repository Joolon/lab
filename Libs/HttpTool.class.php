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
    public static function requestCurl($curl,$method = 'post', $Data = null , $header = null, $type = null){
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
     * 异步并发  批量执行 CURL 请求
     * @param array $url_array 请求数组
     * @param int   $wait_usec 每个 connect 要间隔时间，单位微妙，500000=0.5秒
     * @return array
     * @usage $url_array = array(
     *      [
     *          'url'    => 'http://www.lab.net/test.php',
     *          'method' => 'get/post',
     *          'data'  => ['user_name' => 'a','address' => 'a1'],
     *          'timeout' => 30,// 等待时间
     *          'connect_timeout' => 30,// 连接时间
     *      ],
     *      [
     *          'url'    => 'http://www.lab.net/test.php',
     *          'method' => 'get'
     *      ],
     *      ......
     *   );
     */
    public static function asyncRequestMultiCurl($url_array,$wait_usec = 0){
        $mh            = curl_multi_init(); // multi curl handler
        $i             = 0;
        $handle        = array();
        $response_data = array();

        foreach($url_array as $url_value){
            $url             = $url_value['url'];
            $method          = isset($url_value['method'])?$url_value['method']:'GET';
            $Data            = isset($url_value['data'])?$url_value['data']:[];
            $timeout         = isset($url_value['timeout']) ? $url_value['timeout'] : 30;
            $connect_timeout = isset($url_value['connect_timeout']) ? $url_value['connect_timeout'] : 30;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return don't print
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);// 请求超时时间
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);// 请求连接时间

            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');// 声明用什么浏览器来打开目标网页(区分手机或PC)
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 302 redirect
            curl_setopt($ch, CURLOPT_MAXREDIRS, 7);

            if(strtolower($method) == 'post'){
                curl_setopt($ch, CURLOPT_POST, true); //设置请求是POST方式
                curl_setopt($ch, CURLOPT_POSTFIELDS, $Data); //设置POST请求的数据
            }

            curl_multi_add_handle($mh, $ch); // 把 curl resource 放进 multi curl handler 里
            $handle[$i++] = $ch;
        }

        // 执行，获取资料数据
        do{
            $status = curl_multi_exec($mh, $active);
            if($wait_usec > 0) /* 每个 connect 要间隔多久 */
                usleep($wait_usec); // 500000 = 0.5 sec

        }while($status === CURLM_CALL_MULTI_PERFORM || $active > 0);

        // 读取资料
        foreach($handle as $i => $ch){
            $content           = curl_multi_getcontent($ch);
            $response_data[$i] = (curl_errno($ch) == 0) ? $content : false;
            curl_close($ch);
        }

        // 移除 handle
        foreach($handle as $ch){
            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);

        return $response_data;
    }

    /**
     * 根据参数 生成 token
     * @return array|string
     */
    public function create_access_token(){
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


    /** curl的用途
     * 制作一个简单的网页爬虫（抓取网页信息）
     * 调用WebService API获取信息
     * 模拟用户登录并获取用户中心信息（设置COOKIE来保存模拟登录的信息）
     * 从FTP服务器上下载文件到本地,上传文件到FTP
     * 从HTTPS服务器获取资源,设置 CURLOPT_SSL_VERIFYPEER=0即可（只需设置跳过HTTPS校验）
     */
    function curl_get_userInfo(){
        // Cookie相关设置，这部分设置需要在所有会话开始之前设置
        date_default_timezone_set("PRC");// 设置COOKIE时，必须先设置时区

        $data = 'username=18503051504&password=Xml654321$remember=1';

        $curlObj = curl_init();
        curl_setopt($curlObj, CURLOPT_URL, "http://www.imooc.com/user/login");// 登录页面
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, true);


        // 这四行是固定的
        curl_setopt($curlObj, CURLOPT_COOKIESESSION, true);
        curl_setopt($curlObj, CURLOPT_COOKIEFILE, "cookiefile");// cookie缓存文件位置(不是要自己设置 cookie的具体内容，是要设置 cookie 的存储和读取的文件名)
        curl_setopt($curlObj, CURLOPT_COOKIEJAR, "cookiefile");
        curl_setopt($curlObj, CURLOPT_COOKIE, session_name() . '=' . session_id());

        curl_setopt($curlObj, CURLOPT_HEADER, 0);// 禁止输出头信息
        curl_setopt($curlObj, CURLOPT_FOLLOWLOCATION, 1);// 设置让CURL支持页面链接跳转
        curl_setopt($curlObj, CURLOPT_POST, 1);// POST方式访问
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $data);

        $header = array(
            "appliaction/x-www-form-urlencoded;charset=utf-8",
            "Content-length:" . strlen($data)
        );
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, $header);
        curl_exec($curlObj);// 执行

        $curl_1 = "https://www.imooc.com/u/index/allcourses";
        //$curl_1 = "http://www.imooc.com/space/index";
        $curl_1 = "http://www.imooc.com/u/7617803";
        curl_setopt($curlObj, CURLOPT_URL, $curl_1);
        curl_setopt($curlObj, CURLOPT_POST, 0);// 非POST方式，转换为GET方式

        $header = array("Content-type:text/html");
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, $header);

        $output = curl_exec($curlObj);
        curl_close($curlObj);
        echo $output;

    }

}