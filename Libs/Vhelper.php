<?php
namespace  app\config;

use app\api\v1\models\Token;
use app\models\OperatLog;
use app\models\PlatformSummary;
use app\models\Product;
use app\models\ProductImageUrl;
use app\models\ProductImgDownload;
use app\models\ProductLine;
use app\models\PurchaseOrderItems;
use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\imagine\Image;
use yii\web\UploadedFile;

class  Vhelper
{
    static $token = 'b24fe215-7a7b-4e83-85be-e917d59eef18';
    static $merchantId = '003498';

    /**
     * 调试代码
     */
    public static function dump()
    {
        $args = func_get_args();
        header('Content-type: text/html; charset=utf-8');
        echo "<pre>\n---------------------------------调试信息---------------------------------\n";
        foreach ($args as $value) {
            if (is_null($value)) {
                echo '[is_null]';
            } elseif (is_bool($value) || empty($value)) {
                var_dump($value);
            } else {
                print_r($value);
            }
            echo "\n";
        }
        $trace = debug_backtrace();
        $next = array_merge(
            array(
                'line' => '??',
                'file' => '[internal]',
                'class' => null,
                'function' => '[main]'
            ), $trace[0]
        );

        /* if(strpos($next['file'], ZEQII_PATH) === 0){
                 $next['file'] = str_replace(ZEQII_PATH, DS . 'library' . DS, $next['file']);
                 }elseif (strpos($next['file'], ROOT_PATH) === 0){
                 $next['file'] = str_replace(ROOT_PATH, DS . 'public' . DS, $next['file']);
                 } */
        echo "\n---------------------------------输出位置---------------------------------\n\n";
        echo $next['file'] . "\t第" . $next['line'] . "行.\n";
        if (in_array('debug', $args)) {
            echo "\n<pre>";
            echo "\n---------------------------------跟踪信息---------------------------------\n";
            print_r($trace);
        }
        echo "\n---------------------------------调试结束---------------------------------\n";
        exit();
    }


    /**
     * @param $model
     * @param $item
     * @return null|UploadedFile
     */
    public static function uploadedFile($model, $item)
    {
        $upload = UploadedFile::getInstance($model, $item);
        if (!empty($upload)) {
            $randName = time() . rand(1000, 9999) . '-' . md5(microtime()) . "." . $upload->extension; //重新编译文件名称

            $upload->name = $randName;
        }

        return $upload ? $upload : '';
    }

    /**
     * @param $uploadpath
     * @return mixed
     */
    public static function fileExists($uploadpath)
    {

        if (!file_exists($uploadpath)) {
            FileHelper::createDirectory($uploadpath);
        }

        return $uploadpath;
    }

    /**
     * 生成不带横杠的UUID
     * @return string
     */
    public static function genuuid()
    {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * 图片异步上传
     * $is_update_one 是否上传单个文件：默认是多个
     * @param $name
     */
    public static function ImageAsynUpolad($name, $is_update_one=true)
    {
        $images = UploadedFile::getInstancesByName($name);
        $res = [];
        $p1 = $p2 = [];
        if (count($images) > 0) {
            foreach ($images as $key => $image) {
                $baseName = $image->baseName; //文件名
                $extension = $image->extension; //后缀名
                $microtime = substr(microtime(), 10, 20);

                $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
                // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
                $dir = '/images/' . $uploadpath;
                //生成唯一uuid用来保存到服务器上图片名称
                $pickey = self::genuuid();
                $filename = $pickey . '.' . $image->getExtension();
                // $filename = $baseName . '_' . date('His') . '_' . $microtime . '.' . $extension;
                //如果文件夹不存在，则新建文件夹
                $filepath = self::fileExists(Yii::getAlias('@app') . '/web' . $dir);
                $file = $filepath . $filename;
                if ($image->saveAs($file)) {
                    $imgpath = $dir . $filename;
                    $p1[$key] = $imgpath;

                    $config = [
                        'caption' => $filename,
                        'width' => '120px',
                        'url' => '/supplier/delete-pic', // server delete action
                        'key' => $pickey,
                        'extra' => ['filename' => $filename]
                    ];
                    $p2[$key] = $config;

                    if ($is_update_one) {
                        # 多个文件
                        $imgFile = "<input name='" . $name . "[]' id='" . $pickey . "' type='hidden' value='" . $imgpath . "'/>";
                    } else {
                        # 单个文件
                        $imgFile = "<input name='" . $name . "' id='" . $pickey . "'  type='hidden' value='" . $imgpath . "'/>";
                    }

                    $res = [
                        "initialPreview" => $p1,
                        "initialPreviewConfig" => $p2,
                        "imgfile" => $imgFile
                    ];
                }


            }
        }

        echo Json::encode($res);
    }

    /**
     * 【供应商更新资料】 要展示的图片
     * @param $imgs
     * @return array
     */
    public static function extractFromImgForSupplierImg($imgs){
        if(empty($imgs)) return [];
        $return_img = [];

        if(is_array($imgs)){
            $return_img = $imgs;
        }elseif(is_object($imgs)){
            $imgs = json_decode(json_encode($imgs),true);
            if($imgs){
                $return_img = $imgs;
            }
        }else{
            $return_img = [$imgs];
        }
        return $return_img;
    }

    /**
     * 【供应商更新资料】展示 历史图片
     * @param $imagesInfo
     * @param $file_type_name
     * @param $now_type_now_file
     */
    public static function showHistoryImg($imagesInfo,$file_type_name,$now_type_now_file){
        foreach ($imagesInfo as $key => $value) {
            if ($now_type_now_file == $value) {
                if(strpos($value,';') !== false){
                    foreach(explode(';',$value) as $value2){
                        echo Html::a($file_type_name . "(" . $key . ")",['download','filename'=>[$key =>$value2]]) . "<br />";
                    }
                }else{
                    echo Html::a($file_type_name . "(" . $key . ")",['download','filename'=>[$key =>$value]]) . "<br />";
                }
            } else {
                if(strpos($value,';') !== false){
                    foreach(explode(';',$value) as $value2){
                        echo Html::a($file_type_name . "-历史文件(" . $key . ")",['download','filename'=>[$key =>$value2]]) . "<br />";
                    }
                }else{
                    echo Html::a($file_type_name . "-历史文件(" . $key . ")",['download','filename'=>[$key =>$value]]) . "<br />";
                }
            }
        }
    }

    /**
     * @desc 转换数组格式
     * 转换前：
     * 'BasicTactics' =>
     * array (size=5)
     * 'type' =>
     * array (size=2)
     * 0 => string 'wave_up' (length=7)
     * 1 => string 'wave_down' (length=9)
     * 'days_3' =>
     * array (size=2)
     * 0 => string '1' (length=1)
     * 1 => string '1' (length=1)
     * 'days_7' =>
     * array (size=2)
     * 0 => string '1' (length=1)
     * 1 => string '1' (length=1)
     * 'days_14' =>
     * array (size=2)
     * 0 => string '11' (length=2)
     * 1 => string '1' (length=1)
     * 'days_30' =>
     * array (size=2)
     * 0 => string '1' (length=1)
     * 1 => string '1' (length=1)
     *
     * 转换后
     * 'BasicTactics' =>
     * array (size=2)
     * 0 =>
     * array (size=5)
     * 'type' => string 'wave_up' (length=7)
     * 'days_3' => string '1' (length=1)
     * 'days_7' => string '1' (length=1)
     * 'days_14' => string '1' (length=1)
     * 'days_30' => string '1' (length=1)
     * 1 =>
     * array (size=5)
     * 'type' => string '1' (length=7)
     * 'days_3' => string '1' (length=1)
     * 'days_7' => string '1' (length=1)
     * 'days_14' => string '1' (length=1)
     * 'days_30' => string '1' (length=1)
     *
     * @author Jimmy
     * @date 2017-04-01 15:53:11
     */
    public static function changeData($arr = [])
    {
        $data = [];//拼装后的数组
        foreach ($arr as $key => $val) {
            foreach ($val as $k => $v) {
                $data[$k][$key] = $v;
            }
        }
        return $data;
    }

    /**
     * 三维数组转换为二维数组
     * @param array $arr
     * @return array
     */
    public static function ThereArrayTwo($arr = [])
    {
        $newArr = array();
        foreach ($arr as $key => $val) {
            foreach ($val as $k => $v) {
                $newArr[] = $v;
            }
        }
        return $newArr;
    }

    /**
     * 数字转换为中文
     * @param  string|integer|float $num 目标数字
     * @param  integer $mode 模式[true:金额（默认）,false:普通数字表示]
     * @param  boolean $sim 使用小写（默认）
     * @return string
     */
    public static function number2chinese($num, $mode = true, $sim = true)
    {
        if (!is_numeric($num)) return '含有非数字非小数点字符！';
        $char = $sim ? array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九')
            : array('', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        $unit = $sim ? array('', '十', '百', '千', '', '万', '亿', '兆')
            : array('', '拾', '佰', '仟', '', '萬', '億', '兆');
        $retval = $mode ? '元' : '点';
        //小数部分
        if (strpos($num, '.')) {
            list($num, $dec) = explode('.', $num);
            $dec = strval(round($dec, 2));
            if ($mode) {
                $retval .= "{$char[$dec]}角";
            } else {
                for ($i = 0, $c = strlen($dec); $i < $c; $i++) {
                    $retval .= $char[$dec[$i]];
                }
            }
        }
        //整数部分
        $str = $mode ? strrev(intval($num)) : strrev($num);
        for ($i = 0, $c = strlen($str); $i < $c; $i++) {
            $out[$i] = $char[$str[$i]];
            if ($mode) {
                $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
                if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                    $out[$i] = '';
                }
                if ($i % 4 == 0) {
                    $out[$i] .= $unit[4 + floor($i / 4)];
                }
            }
        }
        $retval = join('', array_reverse($out)) . $retval;
        return $retval;
    }

    /**
     * 截取字符串显示
     * @param $str
     * @param $len
     * @param $maxlen
     * @return string
     */
    public static function toSubStr($str, $len, $maxlen)
    {

        if (empty($str) || !is_string($str)) return false;
        return strlen($str) < $len ? $str : mb_substr($str, 0, $maxlen, 'utf-8') . '......';
    }

    /**
     * 通过sku和图片路径显示图片小图
     * @param $sku
     * @param $uploadimgs
     * @return string
     */
    public static function toSkuImg($sku, $uploadimgs, $width = '110px')
    {
        $_s = json_decode($uploadimgs);

        $url = Yii::$app->params['SKU_BIG_IMG_PATH'] . $sku . '/' . $sku . '-1' . '.JPG';
        $urls = Yii::$app->params['SKU_BIG_IMG_PATH'] . $sku . '/' . $sku . '-1' . '.jpg';
        $url_s = Yii::$app->params['SKU_Development_IMG_PATH'] . $_s['0'];
        if (@fopen($url, 'r')) {

            $imgs = Html::img($url, ['alt' => '产品图片', 'width' => $width, 'class' => "img-rounded"]);
        } elseif (@fopen($url_s, 'r')) {
            $imgs = Html::img($url_s, ['alt' => '产品图片', 'width' => $width, 'class' => "img-rounded"]);
        } elseif (@fopen($urls, 'r')) {
            $imgs = Html::img($urls, ['alt' => '产品图片', 'width' => $width, 'class' => "img-rounded"]);
        } else {
            $imgs = Html::img(Yii::$app->request->hostInfo . '/images/timg.jpg', ['alt' => '产品图片', 'width' => $width, 'class' => "img-rounded"]);
        }


        return $imgs;
    }

    /**
     * 通过sku和图片路径显示图片大图
     * @param $sku
     * @param $uploadimgs
     * @return string
     */
    public static function toSkuImgBig($sku, $uploadimgs, $width = '500px')
    {
        $_s = json_decode($uploadimgs);
        $url = Yii::$app->params['SKU_BIG_IMG_PATH'] . $sku . '/' . $sku . '-1' . '.JPG';
        $urls = Yii::$app->params['SKU_BIG_IMG_PATH'] . $sku . '/' . $sku . '-1' . '.jpg';
        $url_s = Yii::$app->params['SKU_Development_IMG_PATH'] . $_s['0'];
        if (@fopen($url, 'r')) {
            $imgs = Html::img($url, ['alt' => '产品图片', 'width' => $width, 'class' => "img-big"]);
        } elseif (@fopen($urls, 'r')) {
            $imgs = Html::img($urls, ['alt' => '产品图片', 'width' => $width, 'class' => "img-big"]);
        } elseif (@fopen($url_s, 'r')) {
            $imgs = Html::img($url_s, ['alt' => '产品图片', 'width' => $width, 'class' => "img-big"]);
        } else {
            $imgs = Html::img(Yii::$app->request->hostInfo . '/images/timg.jpg', ['alt' => '产品图片', 'width' => $width, 'class' => "img-big"]);
        }

        return $imgs;
    }

    /**
     * @desc 同仓库数据交互的解密签名算法
     * @param unknown $param
     * @param string $sign
     */
    public static function stockUnAuth($param = array(), $sign = '')
    {
        $data = array('error' => -1);
        // 检查 key值
        if ($param['key'] != Yii::$app->params['UEB_STOCK_KEYID']) {
            $data['message'] = 'key有错';
            return $data;
        }
        // 查询时间
        if (abs(time() - $param['timestamp']) > Yii::$app->params['UEB_STOCK_TIMESTAMP']) {
            $data['message'] = '时间超时';
            return $data;
        }
        ksort($param, SORT_REGULAR);
        $urlStr = http_build_query($param, 'yibai_', '&', PHP_QUERY_RFC1738);
        $token = Token::getToken();
        //$token = '33028f058677ae41e00542d6b449932b';
        $securityStr = md5($token . $urlStr . $token, false);

        if ($securityStr != $sign) {
            $data['message'] = '签名出错';
            return $data;
        }
        $data = array('error' => 0, 'message' => 'success');
        return $data;
    }

    /**
     * @desc 同仓库数据交互的加密签名算法
     * @return array
     */
    public static function stockAuth()
    {
        $data = array('error' => -1);

        //设置param数组的值
        $param['key'] = Yii::$app->params['UEB_STOCK_KEYID'];
        $param['timestamp'] = time();
        $param['ip'] = '';

        ksort($param, SORT_REGULAR);
        $urlStr = http_build_query($param, 'yibai_', '&', PHP_QUERY_RFC1738);
        $token = Token::getToken();
       // $token ='33028f058677ae41e00542d6b449932b';
        $securityStr = md5($token . $urlStr . $token, false);
        if (!empty($securityStr)) {
            $data['param'] = $param;
            $data['sign'] = $securityStr;
            $data['error'] = 1;
        }

        return $data;
    }

    /**
     * 邮件发送
     * @param $content
     * @param $title
     * @param string $mails
     */
    public static function ToMail($content, $title, $mails = '1159240689@qq.com')
    {
        $mail = Yii::$app->mailer->compose();
        $mail->setTo($mails);
        $mail->setSubject($title);
        $mail->setHtmlBody($content);    //发布可以带html标签的文本
        $mail->send();
    }


    /**
     * 构造签名方法 用作于1688 token获取
     *
     */
    public static function Signature($appkey, $appSecret, $redirectUrl)
    {

        $appKey = $appkey;
        $appSecret = $appSecret;
        $redirectUrl = $redirectUrl;//填写自己的回调地址


        //生成签名，参数中state可不填
        $code_arr = array(
            'client_id' => $appKey,
            'redirect_uri' => $redirectUrl,
            'site' => 'china',
        );
        $aliParams = array();
        foreach ($code_arr as $key => $val) {
            $aliParams[] = $key . $val;
        }
        sort($aliParams);
        $sign_str = join('', $aliParams);
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appSecret, true)));
        return $code_sign;
    }

    /**
     * 1688方法参数签名
     * @param $app_key
     * @param $app_Secret
     * @param $apiinfo
     * @param $code_arr
     * @return string
     */
    public static function Signatures($app_key, $app_Secret, $apiinfo, $code_arr)
    {
        $appSecret = $app_Secret;
        $apiInfo = $apiinfo . $app_key;//此处请用具体api进行替换

        $aliParams = array();
        foreach ($code_arr as $key => $val) {
            $aliParams[] = $key . $val;
        }
        sort($aliParams);
        $sign_str = join('', $aliParams);
        $sign_str = $apiInfo . $sign_str;
        //Vhelper::dump($sign_str);
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appSecret, true)));
        //Vhelper::dump($code_sign);
        return $code_sign;
    }

    /**
     * 根据采购单号得到采购类型
     * @param $pur_number
     * @return int
     */
    public static function getNumber($pur_number)
    {
        $f = substr($pur_number, 0, 1);
        if ($f == 'A') {
            //海外
            $_rse = 2;

        } elseif ($f == 'P') {
            //国内
            $_rse = 1;
        } else {
            //FBA
            $_rse = 3;
        }
        return $_rse;
    }


    /**
     * 验证类型
     * @param $str
     * @return bool
     */
    public static function AuthType($str)
    {
        if (preg_match("/^[A-Za-z]/", $str)) {
            return true;
        } else {
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
     * 文件上传
     * @param $name 文件域name eg：PlatformSummary[file_execl]
     * @return bool|string
     */
    public static function upload($name, $path = 'files/')
    {
        $up_file = UploadedFile::getInstancesByName($name);
        // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
        $dir = $path . date('Ymd') . '/';  //上传路径

        //生成唯一uuid用来保存到服务器上图片名称
        $pickey = Vhelper::genuuid();
        $filename = $pickey . '.' . $up_file[0]->getExtension();

        //如果文件夹不存在，则新建文件夹
        $filepath = Vhelper::fileExists(Yii::getAlias('@app') . '/web/' . $dir);
        $file = $filepath . $filename;

        if ($up_file[0]->saveAs($file)) {
            return $file;
        } else {
            return false;
        }

    }

    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param str $path 待删除目录路径
     * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
     * @return bool 返回删除状态
     */
    public static function delDirAndFile($path, $delDir = true)
    {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? self::delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        } else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }

        return false;
    }

    public static function postResult($url, $data)
    {
//		$q = array(
//				'token'=>self::$token,
//				'data'=>array(
//						'merchantId'=>self::$merchantId,
//// 						'pageNo'=>1,
//// 						'warehouseName'=>'易佰东莞仓库',中东虚拟仓
//				),
//		);
//		$q['data'] = array_merge($q['data'],$data);
//        var_dump($q);
//        var_dump($data);
        // var_dump($q);die;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'q' => json_encode($data)
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        //print_r(json_decode($output));
        return $output;
    }

    /*
     * 不下载图片，只获取erp产品图链接,没有产品图则显示默认图片
     * */
    public static function getSkuImage($sku)
    {
        if (Yii::$app->cache->get($sku . '_imagecache')) {
            return Yii::$app->cache->get($sku . '_imagecache');
        }
        //接口获取图片链接
        $url = Yii::$app->params['ERP_URL'].'/services/api/system/index/method/getimages/sku/' . $sku;
        $s = Vhelper::imageCurl($url);
        $jsonDatas = json_decode($s);
        if (self::is_json($s) && !empty($s) && property_exists($jsonDatas, 'data') && !empty($jsonDatas->data)) {
            $imgDatas = $jsonDatas->data;
            reset($imgDatas); //  将数组的内部指针指向第一个单元
            $image = current($imgDatas);
            Yii::$app->cache->set($sku . '_imagecache', $image);
            return $image;
        }
        $productimg = Product::find()->select('uploadimgs')->where(['sku'=>$sku])->scalar();
        $uploadimgs = !empty($productimg) ? json_decode($productimg) : '';
        if(!empty($uploadimgs)){
            foreach ($uploadimgs as $v){
                if(@fopen(Yii::$app->params['SKU_Development_IMG_PATH'].$v,'r')){
                    return Yii::$app->params['SKU_Development_IMG_PATH'].$v;
                }
            }
        }
        return Yii::$app->request->hostInfo . '/images/timg.jpg';
    }

    /**
     * 下载sku图片并返回存储路径
     * @param $sku
     * @param $uploadimgs
     * @param string $path
     * @return bool|string
     */
    public static function downloadImg($sku, $uploadimgs, $type = 1, $path = '/images/sku/')
    {
        $dir = iconv("UTF-8", "GBK", "./images/sku/");
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        //优先从缓存获取，产品列表点击图片重置会清除缓存并更新图片下载状态
        if (Yii::$app->cache->get($sku . '_cache' . $type)) {
            return Yii::$app->cache->get($sku . '_cache' . $type);
        }
        //缓存没有会去查表获取图片地址
        $model = ProductImgDownload::find()->where(['sku' => $sku, 'status' => 1])->one();
        if (!empty($model) && @fopen('.' . $model->image_url, 'r')) {
            Yii::$app->cache->set($sku . '_cache1', '.' . $model->image_url, 86400);
            Yii::$app->cache->set($sku . '_cache2', Yii::$app->request->hostInfo . $model->image_url, 86400);
            if ($type == 1) {
                return '.' . $model->image_url;
            } else {
                return Yii::$app->request->hostInfo . $model->image_url;
            }
        }
        //接口获取图片链接
        $url = Yii::$app->params['ERP_URL'].'/services/api/system/index/method/getimages/sku/' . $sku;
        $s = Vhelper::imageCurl($url);
        $jsonDatas = json_decode($s);
        if (self::is_json($s) && !empty($s) && property_exists($jsonDatas, 'data') && !empty($jsonDatas->data)) {
            $imgDatas = $jsonDatas->data;
            reset($imgDatas);
            $image = current($imgDatas);
            $img = Vhelper::imageCurl($image);
            $resultUrl = $image;
        }
        //接口获取不到产品大图则选择开发图
        if (!isset($resultUrl) || empty($resultUrl)) {
            $imgArray = json_decode($uploadimgs);
            if (!empty($imgArray) && is_array($imgArray)) {
                reset($imgArray);
                $kfimage = current($imgArray);
                $img = Vhelper::imageCurl(Yii::$app->params['SKU_Development_IMG_PATH'] . $kfimage);
                $resultUrl = Yii::$app->params['SKU_Development_IMG_PATH'] . $kfimage;
            }
        }
        //既没有开发图也没有实拍图则选择默认空图
        if (isset($resultUrl) && !empty($resultUrl) && $img) {
            $filename = pathinfo($resultUrl, PATHINFO_BASENAME);
            $fanhuistr = @file_put_contents('.' . $path . $filename, $img);
            if ($fanhuistr) {
                if(getimagesize('.' . $path . $filename)){
                    Image::thumbnail('.' . $path . $filename, 110, 110)->save('.' . $path . $filename, ['quality' => 100]);
                }else{
                    if ($type == 1) {
                        return './images/timg.jpg';
                    } else {
                        return Yii::$app->request->hostInfo . '/images/timg.jpg';
                    }
                }
            }
            $model = new ProductImgDownload();
            $model->sku = $sku;
            $model->image_url = $path . $filename;
            $model->status = 1;
            if ($fanhuistr == false || $model->save() == false) {
                if ($type == 1) {
                    return './images/timg.jpg';
                } else {
                    return Yii::$app->request->hostInfo . '/images/timg.jpg';
                }
            }
            Yii::$app->cache->set($sku . '_cache1', '.' . $model->image_url, 86400);
            Yii::$app->cache->set($sku . '_cache2', Yii::$app->request->hostInfo . $path . $filename, 86400);
            if ($type == 1) {
                return '.' . $path . $filename;
            } else {
                return Yii::$app->request->hostInfo . $path . $filename;
            }
        }
        if ($type == 1) {
            return './images/timg.jpg';
        } else {
            return Yii::$app->request->hostInfo . '/images/timg.jpg';
        }
    }

//请求接口图片获取图片流数据
    public static function imageCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 获取访问者公网IP
     * @return string
     */
    public static function getOutIp()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = gethostbyname($_ENV['COMPUTERNAME']); //获取本机的局域网IP
        }
        return $cip;
    }

    /**
     * 写入操作日志
     * @param $data
     */
    public static function setOperatLog($data)
    {
        $model = new OperatLog();
        $model->type = !empty($data['type']) ? $data['type'] : '';
        $model->pid = !empty($data['pid']) ? $data['pid'] : '';
        $model->username = isset(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:'';
        $model->module = !empty($data['module']) ? $data['module'] : '';
        $model->content = !empty($data['content']) ? $data['content'] : '';
        $model->pur_number = !empty($data['pur_number']) ? $data['pur_number'] : '';
        $model->uid = isset(Yii::$app->user->id)?Yii::$app->user->id:'';
        $model->ip = self::getOutIp();
        $model->create_date = date('Y-m-d H:i:s');
        $model->save();
    }

    public static function toSkuImgUrl($sku, $uploadimgs)
    {
        $_s = json_decode($uploadimgs);

        $url = Yii::$app->params['SKU_BIG_IMG_PATH'] . $sku . '/' . $sku . '-1' . '.JPG';
        $urls = Yii::$app->params['SKU_BIG_IMG_PATH'] . $sku . '/' . $sku . '-1' . '.jpg';
        $url_s = Yii::$app->params['SKU_Development_IMG_PATH'] . $_s['0'];
        if (@fopen($url, 'r')) {

            $imgs = $url;
        } elseif (@fopen($urls, 'r')) {
            $imgs = $urls;
        } elseif (@fopen($url_s, 'r')) {
            $imgs = $url_s;
        } else {
            $imgs = Yii::$app->request->hostInfo . '/images/timg.jpg';
        }
        return $imgs;
    }

    /*
    *function：计算两个日期相隔多少年，多少月，多少天
    *param string $date1[格式如：2011-11-5]
    *param string $date2[格式如：2012-12-01]
    *return array array('年','月','日');
    */
    public static function diffDate($date1, $date2)
    {
        if (strtotime($date1) > strtotime($date2)) {
            $tmp = $date2;
            $date2 = $date1;
            $date1 = $tmp;
        }
        list($Y1, $m1, $d1) = explode('-', $date1);
        list($Y2, $m2, $d2) = explode('-', $date2);
        $Y = $Y2 - $Y1;
        $m = $m2 - $m1;
        $d = $d2 - $d1;
        if ($d < 0) {
            $d += (int)date('t', strtotime("-1 month $date2"));
            $m--;
        }
        if ($m < 0) {
            $m += 12;
            $Y--;
        }
        return array('year' => $Y, 'month' => $m, 'day' => $d);
    }

    //获取供应商累计合作金额
    public static function getSupplierPurchaseNum($supplierCode, $startTime = null, $endTime = null)
    {
        $data = PurchaseOrderItems::find();
        $data->select('SUM(a.ctq*a.price) as purchase_num');
        $data->alias('a');
        $data->leftJoin('pur_purchase_order as b', 'a.pur_number = b.pur_number');
        $data->andFilterWhere(['NOT IN', 'b.purchas_status', [1, 2, 4, 10]]);
        $data->andFilterWhere(['b.supplier_code' => $supplierCode]);
        if (!empty($startTime))
            $data->andFilterWhere(['>=', 'b.created_at', $startTime]);
        if (!empty($endTime))
            $data->andFilterWhere(['<', 'b.created_at', $endTime]);
        $data->groupBy('b.supplier_code');
        $sum = $data->one();
        return !empty($sum) ? $sum->purchase_num : 0;
    }


    public static function isSameData($arr)
    {
        if (is_array($arr)) {
            $flag = true;
            $first = $arr[0];
            foreach ($arr as $v) {
                if ($first !== $v) {
                    $flag = false;
                    break;
                }
            }
            return $flag;
        } else {
            return false;
        }
    }

    // 异步上传图片
    public static function UploadIamgeAnsy($name, $inputName)
    {
        $img = UploadedFile::getInstancesByName($name);
        if (empty($img)) {
            return false;
        }
        $res = [];
        $obj = $img[0];
        $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
        $dir = '/images/' . $uploadpath;
        $pickey = self::genuuid();
        $filename = $pickey . '.' . $obj->getExtension();
        $filepath = self::fileExists(Yii::getAlias('@app') . '/web' . $dir);
        $file = $filepath . $filename;
        if ($obj->saveAs($file)) {
            $imgPath = $dir . $filename;
            $res = [
                /*'initialPreview' => [
                     "<img src = '".$imgPath."' class='file-preview-image' style='width:auto;height:160px;'>"
                 ],
                 'initialPreviewConfig' => [
                     'caption' => $filename,
                     'width' => '120px',
                     'url' => 'order-cashier-pay/del-img', // server delete action
                     'key' => $pickey,
                     'extra' => ['filename' => $filename]
                 ],*/
                "imgfile" => "<input name='" . $inputName . "' id='" . $pickey . "' type='hidden' value='" . $imgPath . "'/>"
            ];
        }
        return $res;
    }

    /**
     * @param $num 金额
     * @param int $pre 保存几位小数
     */
    public static function num_to_rmb($num,$pre='')
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "厘分角元拾佰仟万拾佰仟亿";
        if( $pre == '') {
            $numstr = strrpos($num, '.');
            if ($numstr === false) {
                $pre = 2;
            } else {
                $pres = substr($num, $numstr + 1);
                $pre = strlen($pres);
            }
        }
        // 将数字转化为整数
        if($pre == 3){
            $num = $num * 1000;
        }else{
            $num = $num * 100;
        }
        if (strlen($num) > 10) {
            return "金额太大，请检查";
        }
        // 精确到分后面就不要了，所以只留两个小数位
        $num = round($num, $pre);

        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                // 获取最后一位数字
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            // 每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            // 去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            // 结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            // utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            // 处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }
        // 这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        // 将处理的汉字加上“整”
        if (empty($c)) {
            return "零元";
        } else {
            if(preg_match('/分|角|厘/', $c)) {
                return $c;
            } else {
                return $c."整";
            }
        }
    }
    public static function getAliDateTime($date){
        $times=strstr($date, '+', TRUE);
        $a=substr($times,0,-3);
        $tt=strtotime($a);
        return date("Y-m-d H:i:s",$tt);
    }
    
    /**
     * 返回 是否可退税 退税率-税点≥1% ? '可退税' : '不可'
     * @param float $tax_rate 出口退税率
     * @param float $pur_ticketed_point 税点
     * @return number 
     */
    public static function getProductIsBackTax($tax_rate, $pur_ticketed_point) {
        if (empty($tax_rate) ||$pur_ticketed_point==0 || $tax_rate==0 || empty($pur_ticketed_point)) {
            return 0;
        }
        return $tax_rate - $pur_ticketed_point >= 1 ? 1 : 2;
    }
    public static function getProductLineTreeDatas(){
        $datas = [];
        $firstsLevel = ProductLine::find()->select('product_line_id,linelist_parent_id,linelist_cn_name')->where(['linelist_parent_id'=>0])->asArray()->all();
        foreach ($firstsLevel as $key=>$value){
            $datas[$key] = ['product_line_id'=>$value['product_line_id'],'linelist_cn_name'=>$value['linelist_cn_name']];
            $secondLevel = ProductLine::find()->select('linelist_parent_id,product_line_id,linelist_cn_name')->where(['linelist_parent_id'=>$value['product_line_id']])->asArray()->all();
            foreach ($secondLevel as $k=>$v){
                $thirdLevel = ProductLine::find()->select('product_line_id,linelist_parent_id,linelist_cn_name')->where(['linelist_parent_id'=>$v['product_line_id']])->asArray()->all();
                $datas[$key]['items'][$k] = ['product_line_id'=>$v['product_line_id'],'linelist_cn_name'=>$v['linelist_cn_name'],'items'=>$thirdLevel];
            }
        }
        return $datas;
    }
    
    /**
     * @desc send multiple thread
     * @param unknown $url
     * @param unknown $params
     * @param string $type
     * @param number $timeout
     * @return boolean
     */
    public static function throwTheader($url, $params = array(), $type = 'GET', $timeout = 60)
    {
        $urlInfo = parse_url($url);
        if (!isset($urlInfo['host']) || empty($urlInfo['host']))
            $urlInfo = parse_url($_SERVER['HTTP_HOST']);
        $host = isset($urlInfo['host']) ? $urlInfo['host'] : $_SERVER['HTTP_HOST'];
        $scheme = isset($urlInfo['scheme']) ? $urlInfo['scheme'] : '';
        $hostStr = $scheme . "://" . $host;
        $port = isset($urlInfo['port']) ? $urlInfo['port'] : '80';
        $hostStr .= ':' . $port;
        $uri = str_replace([$hostStr], '', $url);
        if (empty($host))
            return false;
        $socket = fsockopen($host, $port, $errno, $error, $timeout);
        if (!$socket)
            return false;
        stream_set_blocking($socket, false);
        $data = '';
        $body = '';
        if (is_array($params)) {
            foreach ($params as $key => $value)
                $data .= strval($key) . '=' . strval($value) . '&';
        } else
            $data = $params;
        $header = '';
        if ($type == 'GET') {
            if (strpos($uri, '?') !== false) {
                $uri .= '&' . $data;
            } else {
                $uri .= '?' . $data;
            }
            $header .= "GET " . $uri . ' HTTP/1.0' . "\r\n";
        } else {
            $header .= "POST " . $uri . ' HTTP/1.0' . "\r\n";
            $header .= "Content-length: " . strlen($data) . "\r\n";
            $body = $data;
            //$header .=
        }
        $header .= "Host: " . $host . (empty($port) ? '' : ':' . $port) . "\r\n";
        $header .= 'Cache-Control:no-cache' . "\r\n";
        $header .= 'Connection: close' . "\r\n\r\n";
        $header .= $body;
        fwrite($socket, $header, strlen($header));
        usleep(300);   //解决nginx服务器连接中断的问题
        fclose($socket);
        return true;
    }

    public static function getSqlArrayString($arr){
        return "'".implode("','", $arr)."'";
    }
    
    /**
     * 取出数组中对应的键值，组成新的数组,可以转换成字符串
     */
    public static function pluck($array, $keys, $string=true){
        $res = [];
        foreach ($keys as $k=>$v) {
            $res[] = $k+1 . $array[$v];
        }
        if ($string) return implode('<br />', $res);
        return $res;
    }


    /**
     * 采购员姓名 如果有数字则拆分出不带数字的姓名
     * @param $buyer_name
     * @return array
     */
    public static function chunkBuyerByNumeric($buyer_name){
        if( $buyer_name AND !is_numeric($buyer_name) AND preg_match('/\d/',$buyer_name)){
            $buyer_name2 = preg_replace('/[0-9A-Za-z]/','',$buyer_name);

            return [$buyer_name,$buyer_name2];
        }else{
            return $buyer_name;
        }
    }
}