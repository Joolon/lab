<?php
include_once dirname(dirname(__FILE__))."/Help/DB.class.php";
include_once dirname(dirname(__FILE__)).'/Help/CNPostBind.class.php';
/*
 * @name 万邑通API操作类
 * @add date 2017-04-05 tian
 */

class WinitApi {

    private $url = 'http://openapi.winit.com.cn/openapi/service';
    private $test_url = 'http://openapi.sandbox.winit.com.cn/openapi/service'; //沙盒地址
    private $data; //请求数据
    private $action; //请求方法
    private $sign; //签名
    private $app_key = '379861939@qq.com'; //用户名
    private $token = '6D612F3111290FBB1BFE6303BE560618';
    private $timestamp = ''; //时间
    private $version = '1.0'; //版本号
    private $sign_method = 'md5'; //加密方式
    private $format = 'json'; //返回格式
    private $platform = ''; //平台
    private $language = 'zh_CN'; //语言

    //生成签名

    private function createSign() {
        $string = $this->token;
        $string.='action' . $this->action;
        $string.='app_key' . $this->app_key;
        $data = 'data' . urldecode(json_encode($this->data));
        $string.=$data;
        $string.='format' . $this->format;
        $string.='platform' . $this->platform;
        $string.='sign_method' . $this->sign_method;
        $string.='timestamp' . $this->timestamp;
        $string.='version' . $this->version;
        $string.=$this->token;
        return strtoupper(md5($string));
    }

    //
    private function arrToObj($data) {
        //------begin--------
        $qty = 0;
        $goodWeight = 0;
        $nameCh = '';
        $declaredValue = 0;
        $nowLocation = '';
        $isMix = false;
        $ssC = count($data);
        $nameEn = '(' . $ssC . ')';
        $data[0]['ebay_account'] = self::TransformAccount($data[0]['ebay_account']);
        $data[0]['ebay_account'] = strtolower(trim($data[0]['ebay_account']));
        $merchandiseList = array();
        for ($i = 0; $i < $ssC; $i++) {
            $merchandise = new stdClass(); //php5.4 报错 Creating default object from empty value
            $merchandise->declaredNameCn = urlencode(str_replace("/", "", $data[$i]['goods_zysbmc'])) . ""; // 商品中文名称
            $merchandise->declaredNameEn = urlencode(str_replace("/", "", $data[$i]['goods_ywsbmc'])) . ""; // 商品英文名称
            $merchandise->declaredValue = round(str_replace("/", "", $data[$i]['goods_sbjz']), 2) . ""; // 商品申报价格
            $merchandise->itemID = $data[$i]['ebay_itemid'] . ""; //
            $merchandise->transactionID = $data[$i]['ebay_tid'] . ""; // ebay transactionID
            $merchandiseList[] = $merchandise;
            $data[$i]['goods_location'] = strtoupper(trim($data[$i]['goods_location']));
            $qty += $data[$i]['ebay_amount'];
            if ($data[$i]['goods_weight'])
                $goodWeight += $data[$i]['goods_weight'];
            if ($data[$i]['goods_zysbmc'])
                $nameCh .= $data[$i]['goods_zysbmc'] . ',';
            $nameEn .= $data[$i]['goods_ywsbmc'] . '-' . $data[$i]['goods_zysbmc'] . '[' . $data[$i]['sku'] . '*' . $data[$i]['ebay_amount'] . ']{' . $data[$i]['goods_location'] . '}, ';
            $declaredValue += $data[$i]['goods_sbjz'] * $data[$i]['ebay_amount'];
            if ($data[$i]['goods_location']{0} == "U") {
                if ($nowLocation == '')
                    $nowLocation = 'yw';
                elseif ($nowLocation == 'sz') {
                    $isMix = true;
                    break;
                }
            } else {
                if ($nowLocation == '')
                    $nowLocation = 'sz';
                elseif ($nowLocation == 'yw') {
                    $isMix = true;
                    break;
                }
            }
        }
        //------end--------
        $package = new stdClass();
        $package->height = "5";
        $package->length = "21";
        $package->merchandiseList = $merchandiseList;
        $package->weight = "$goodWeight";
        $package->width = "15";
        $packList[] = $package;

        $data[0]['ebay_street'] = urlencode(str_replace("/", "", $data[0]['ebay_street'])) . "";
        $data[0]['ebay_street1'] = urlencode(str_replace("/", "", $data[0]['ebay_street1'])) . "";
        $data[0]['ebay_city'] = urlencode(str_replace("/", "", $data[0]['ebay_city'])) . "";

        $phone = addslashes($data[0]['ebay_phone']);
        $phone = str_replace("/", "", $phone);
        $phone = str_replace("-", "", $phone);
        $phone = substr($phone, 0, 20);
        $phone = $phone ? $phone : '0000000'; //没电话号码的用7个零代替
        //这些国家用简称
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Korea, South" ? "KR" : $data[0]['ebay_countryname'];
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Vietnam" ? "VN" : $data[0]['ebay_countryname'];
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Serbia and Montenegro" ? "CS" : $data[0]['ebay_countryname'];
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Salvador" ? "SV" : $data[0]['ebay_countryname'];
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Moldova" ? "MD" : $data[0]['ebay_countryname'];
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Macedonia" ? "MK" : $data[0]['ebay_countryname'];
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Tanzania" ? "TZ" : $data[0]['ebay_countryname'];
        $data[0]['ebay_countryname'] = $data[0]['ebay_countryname'] == "Brunei" ? "BN" : $data[0]['ebay_countryname'];
        $data[0]['ebay_usermail'] = $data[0]['ebay_usermail'] == "Invalid Request" ? "" : $data[0]['ebay_usermail'];
        $data[0]['ebay_state'] = str_replace("/", "", $data[0]['ebay_state']) . "";
        $data[0]['ebay_postcode'] = str_replace("/", "", $data[0]['ebay_postcode']) . "";
        $shipperAddrCode = $data[0]['system_shippingcarriername'] == '42' ? 'SZA' : 'YWU';
        $warehouseCode = $data[0]['system_shippingcarriername'] == '42' ? 'YW10000008' : 'YW10000007';
        $warehouseCode = $data[0]['system_shippingcarriername'] == '29' ? 'CPSZS' : $warehouseCode;
        $shipperAddrCode = $data[0]['system_shippingcarriername'] == '29' ? 'SZA' :$shipperAddrCode;
        $shippingCode = $this->getShippingCode(trim($data[0]['ebay_carrier'].$data[0]['system_shippingcarriername']));
        $this->data = new stdClass();
        $this->data->buyerAddress1 = $data[0]['ebay_street'];
        $this->data->buyerAddress2 = $data[0]['ebay_street1'];
        $this->data->buyerCity = $data[0]['ebay_city'];
        $this->data->buyerContactNo = $phone;
        $this->data->buyerCountry = $data[0]['ebay_countryname'];
        $this->data->buyerEmail = $data[0]['ebay_usermail'];
        $this->data->buyerHouseNo = "";
        $this->data->buyerName = urlencode(str_replace("/", "", $data[0]['ebay_username'])) . "";
        $this->data->buyerState = urlencode($data[0]['ebay_state']?$data[0]['ebay_state']:$data[0]['ebay_countryname']);
        $this->data->buyerZipCode = strlen($data[0]['ebay_postcode'])>10?(substr($data[0]['ebay_postcode'], 0,10)):$data[0]['ebay_postcode'];
        $this->data->dispatchType = $data[0]['system_shippingcarriername']==29?'C':"P";
        $this->data->ebaySellerId = $data[0]['ebay_account'] . "";
        $this->data->packageList = $packList;
        $this->data->refNo = $data[0]['ebay_id'] . '-1';
        $this->data->shipperAddrCode = $shipperAddrCode;
        $this->data->warehouseCode = $warehouseCode;
        
        $this->data->buyerZipCode = $this->data->buyerZipCode?$this->data->buyerZipCode:'00000000';
        $this->data->buyerCity = strlen($this->data->buyerCity)>32?(substr($this->data->buyerCity,0,32)):$this->data->buyerCity;
        //------测试环境------
        // $this->data->shipperAddrCode="A00004";
        // $this->data->warehouseCode="SZ0001";
        // $shippingCode="WP-HKP001";
        //------测试环境------
        $this->data->winitProductCode = $shippingCode;
    }

    //获取物流渠道编码
    private function getShippingCode($ebay_carrier) {
        $shippingCode = '';
        switch ($ebay_carrier) {
            case "eDS易递宝-马来西亚渠道(平邮)-eBay 内电42":
                $shippingCode = "WP-MYP101";
                break;
            case "eDS易递宝-香港渠道(平邮)-eBay42":
                $shippingCode = "WP-HKP101";
                break;
            case "eDS易递宝-DHL 跨境电商包裹(香港)-eBay42":
                $shippingCode = "WP-DEP102";
                break;
            case "eDS线上中国邮政平常小包+(上海口岸)42":
                $shippingCode = "ISP1014";
                break;
            case "eDS线上中国邮政平常小包+（深圳）42":
                $shippingCode = "ISP031279";
                break;
            case "eDS线上中国邮政平常小包+（深圳）29":
                $shippingCode = 'ISP031040';
                break;
        }
        return $shippingCode;
    }

    //查询订单
    public function QueryOrder() {
        $this->data = null;
        $this->data->orderNo = "ID18021154908335CN";

        $this->action = 'isp.order.query'; //请求的方法
        $this->sign = $this->createSign(); //签名
        $requestData = $this->sendData();
        $result = $this->requestMsg($requestData);
        var_dump($result);exit;
    }

    //获取跟踪号
    public function getTracknubmer($data) {
        $ebay_id = $data[0]['ebay_id'];
        $carrier = $data[0]['ebay_carrier'];
        $shippingcarriername = $data[0]['system_shippingcarriername'];
        DB::Update("ebay_order", array('location'=>'ready'), "ebay_id=$ebay_id");
        $returnStr = array('code' => '', 'msg' => '', 'trackNumber' => '', 'orderNo' => '');
        $this->arrToObj($data); //数据处理
        $this->action = 'isp.order.createOrder'; //请求的方法
        $this->sign = $this->createSign(); //签名
        $requestData = $this->sendData();
		//print_r(json_decode($requestData,true));exit;
        $result = $this->requestMsg($requestData);
        file_put_contents(dirname(__FILE__) . "/EDS.txt", var_export($requestData, true));
        if ($result['code'] == 0) {
            $returnStr['code'] = $result['code'];
            $returnStr['msg'] = $result['msg'];
            $returnStr['trackNumber'] = $result['data']['trackingNo'];
            $returnStr['orderNo'] = $result['data']['orderNo']; //这个订单号拿来打印面单
            if($carrier=='eDS线上中国邮政平常小包+（深圳）' && $shippingcarriername == '29'){
                $CNPostBind = new CNPostBind();
                $list = array(
                    array('ebay_tracknumber'=>$returnStr['trackNumber'])
                );
                $CNPostBind->RunMailBind($list);
                DB::Update("ebay_order", array('location'=>'complete'), "ebay_id=$ebay_id");
            }
        } else {
            $returnStr['code'] = $result['code'];
            $returnStr['msg'] = $result['msg'];
        }

        return $returnStr;
    }

    //保存面单pdf
    public function getTemplate($orderNo, $ebay_id) {
        $this->data = new stdClass();
        $this->action = 'winitLable.query';
        $this->data->orderNo = $orderNo;
        $this->sign = $this->createSign(); //签名
        $requestData = $this->sendData();
        $result = $this->requestMsg($requestData);
        $pdf = base64_decode($result['data']['files'][0]);
        $fileName = $ebay_id . '.pdf';
        $filePath = './jpgTemplate/';
        file_put_contents($filePath . $fileName, $pdf);
        return $filePath . $fileName;
    }

    //确认发货
    public function confirmShip($orderNo) {
        $this->data = new stdClass();
        $this->action = 'isp.delivery.confirm';
        $this->data->orderNo = $orderNo;
        $this->sign = $this->createSign(); //签名
        $requestData = $this->sendData();
        $result = $this->requestMsg($requestData);
        return $result;
    }

    //需要发送的数据
    private function sendData() {
        $requestData = array();
        $requestData['action'] = $this->action; //方法名
        $requestData['app_key'] = $this->app_key; //账号
        $requestData['data'] = $this->data; //订单数据
        $requestData['sign'] = $this->sign; //签名
        $requestData['timestamp'] = $this->timestamp;
        $requestData['version'] = $this->version;
        $requestData['sign_method'] = $this->sign_method;
        $requestData['format'] = $this->format;
        $requestData['platform'] = $this->platform;
        $requestData['language'] = $this->language;
        return urldecode(json_encode($requestData));
    }

    //curl请求
    private function requestMsg($requestData) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        $responseMsg = json_decode($content, true);
        return $responseMsg;
    }

    public static function TransformAccount($account) {
        $Str = strtolower($account);
        switch ($Str) {
            case 'smiliar': {
                    return 'smiliar';
                    break;
                }
            default : {
                    return $account;
                }
        }
    }

}
