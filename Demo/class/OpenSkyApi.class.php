<?php

// 平台提供的变换函数
function urlsafe($data) {
    return rtrim(strtr($data, '+/', '-_'), '=');
}

/*  重建创建日志文件函数 */
function subOperationLog123($txtfilename,$txtlog){
    global $truename;
    $date   = date("Y-m-d H:i:s");								//取得系统时间
    $ip     = $_SERVER['REMOTE_ADDR']; 		//取得发言的IP地址
    $fp     = fopen("txt/".$txtfilename.".csv","a");				//以写方式打开文件如果不存在就创建
    $str    = $ip.",".$truename.",".$date.",".$txtlog."\r\n";				//将所有留言的数据赋予变量$str，"|"的目的是用来今后作数据分割时的数据间隔符号。
    fwrite($fp,$str);												//将数据写入文件
    fclose($fp);															//关闭文件
}

/**
 * Class OpenSkyApi
 * OpenSky 平台订单管理对接
 * API地址：http://bisapidocs.opensky.com/#partner-registration
 * @author:zwl
 */
class OpenSkyApi {

    private static $dbcon = '';

    private $api_url        = 'https://bisapi.opensky.com';// 订单处理URL
    private $account        = '';
    private $sellerId       = '';
    private $userkeyid      = '';
    private $appkeyid       = '';
    private $secretkey      = '';
    private $datetime       = '';

    public function __construct()
    {
        global $dbcon;
        self::$dbcon = $dbcon;
    }

    /**
     * 设置当前账户的用户权限认真信息
     * @param $account
     */
    public function setAccountInfo($account){
        $account_info = $this->getAccountInfo($account);

        // 国际时间时区(服务器有时差)，需要请求的header和签名中时间一致
        date_default_timezone_set("UTC");
        $this->datetime = date("Y-m-d\TH:i:s\Z",(strtotime(date("Y-m-d H:i:s"))));
//        $this->datetime = date("Y-m-d\TH:i:s\Z",(strtotime(date("Y-m-d H:i:s"))- 250));// 450秒是服务器时差，每个服务器时差可能不一样

        if($account_info){
            $this->account      = $account;
            $this->sellerId     = $account_info['opensky_sellerid'];
            $this->userkeyid    = $account_info['opensky_userkeyid'];
            $this->appkeyid     = $account_info['opensky_appkeyid'];
            $this->secretkey    = $account_info['opensky_secretkey'];
        }
    }

    /**
     * 获得账户的认证信息
     * @param string $account 销售账户
     * @return mixed
     */
    public function getAccountInfo($account){
        $sql = 'select id,ebay_account,opensky_sellerid,opensky_userkeyid,opensky_appkeyid,opensky_secretkey
                from ebay_account 
                where ebay_type="OpenSky" and ebay_account="'.$account.'" limit 1 ';
        $sql = self::$dbcon->query($sql);
        $sql = self::$dbcon->getResultArray($sql);

        if(count($sql)){
            return $sql[0];
        }else{
            return false;
        }
    }

    /**
     * 从平台上抓取订单导入V2
     * @param string $account
     * @return mixed
     */
    public function getOrders($account){
        $this->setAccountInfo($account);

        $sub_url = '/bis-api/public/api/v1/orders/search';
        $date   = date("Y-m-d\TH:i:s",strtotime(' -7 days'));
        $filters = array(
            array('field' => 'CHANNEL','operator' => 'EQUAL','value' => 'OPENSKY'),// 站点
//            array('field' => 'STATUS','operator' => 'EQUAL','value' => 'pending'),// 订单状态
            array('field' => 'CREATED_AT','operator' => 'GREATER_THAN','value' => $date.'.000+0000'),// 下单时间
        );
        $pageSize = 100;
        $data = array(
            'sellerId' => $this->sellerId,
            'page' => 1,// 当前页数
            'pageSize' => $pageSize,// 每页记录数
            'filters' => $filters,
        );

        $loadCount = 0;
        $success   = 0;
        do{
            $signature  = $this->getSignature($sub_url,$data);
            $result     = $this->curlPost($this->api_url.$sub_url,$data,$signature);
//            print_r($result);exit;

            if(isset($result['errors']) AND !empty($result['errors'])){
                $errorInfo = $result['errors'][0];
                subOperationLog123('OpenSky/someErrors'.date('Y-m'),$this->account.'-->getOrders(code:message):'.$errorInfo['code'].':'.$errorInfo['message']);
            }else{
                $success += $this->saveOrders($result['orders']);
                subOperationLog123('OpenSky/loadOrderStatistic'.date('Y-m'),$this->account.'获取订单成功-->总数('.count($result['orders']).')');
                $loadCount += count($result['orders']);
            }

            $totalCount     = isset($result['totalCount'])?$result['totalCount']:0;// 查询的记录总数
            $nowPage        = $data['page'];// 当前页数
            $data['page']   ++;// 下一页
        }while($pageSize*$nowPage < $totalCount);// 如果已经查询出的记录数大于等于记录总数则循环获取

        subOperationLog123('OpenSky/loadOrderStatistic'.date('Y-m'),$this->account.'获取订单成功-->查询总数('.$totalCount.')，保存总数('.$loadCount.')，新增数('.$success.')');
        return array($totalCount,$loadCount);
    }


    /**
     * 保存订单信息
     * @param array $orders 订单信息
     * @return mixed
     */
    public function saveOrders($orders){
        date_default_timezone_set('Asia/Shanghai');
        $nowTime	        = date("Y-m-d H:i:s");
        $mcTime		        = strtotime($nowTime);
        $defaultStoreId     = 32;// 默认深圳仓
        $totalDataNum	    = sizeof($orders);// 订单总个数
        $success            = 0;

        if($totalDataNum > 0){
            if(empty($this->account)){// 账户缺失则跳过保存数据，防止单号ID相同删除数据
                echo 'CheckAccount销售账户缺失：'.$this->account.'<br/>';
                subOperationLog123("OpenSky/someErrors".date('Y-m'),'销售账户缺失：'.$this->account);
                return false;
            }
            foreach($orders as $order){
                if($this->judgeOrderExists($order["channelOrderId"],$this->account)){// 判断订单是否存在，存在则跳过
                    $status             = $order['status'];// 平台上订单状态

                    $sql	= "select ebay_id,ebay_status,status from ebay_order where recordnumber='{$order["channelOrderId"]}' and ebay_account ='$this->account' limit 1";
                    $sql	= self::$dbcon->execute($sql);
                    $res	= self::$dbcon->getResultArray($sql);
                    $ebay_id = $res[0]['ebay_id'];
                    $order_status = $res[0]['status'];
                    if($order_status != $status){// 更新订单状态
                        if($status == 'canceled'){// 如果取消则更新订单状态为作废
                            if($res[0]['ebay_status'] != 236){
                                $updateSql = "UPDATE ebay_order SET ebay_status=236 WHERE recordnumber='{$order["channelOrderId"]}' and ebay_account ='$this->account' limit 1";
                                addOrderRemark($ebay_id,'订单['.$ebay_id.']在平台被取消');
                                self::$dbcon->execute($updateSql);
                            }
                        }else{
                            $updateSql = "UPDATE ebay_order SET status='$status' WHERE recordnumber='{$order["channelOrderId"]}' and ebay_account ='$this->account' and ebay_ordertype = 'OpenSky' limit 1";
                            echo $updateSql.'<br/>';
                            self::$dbcon->execute($updateSql);
                        }
                    }

                    continue;
                }

                // 订单信息
                $ordersn            = $this->account.'-'.$order["channelOrderId"];// V2系统订单编号（账户+订单ID）
                $createdtime        = strtotime($order['createdAt']);//  创建时间
                $paidtime			= $createdtime;// 付款时间
                $paystatus          = '';// 付款状态
                $recordnumber       = $order["channelOrderId"];
                $ebay_orderid       = $order['orderId'];
                $status             = trim($order['status']);// 平台上订单状态
                $ebay_total			= $order['subtotal'];//付款总金额(不含运费)
                $ebay_account       = $this->account;
                $shipfee            = $order['shippingPrice']; // 物流费用

                if(strtolower($status) == 'pending'){// 跳过其他状态的订单
                    $ebay_status    = '274';// 订单状态：新导入
                }elseif(strtolower($status) == 'processing'){
                    $ebay_status    = '1';// 订单状态：待处理
                }elseif(strtolower($status) == 'complete'){
                    $ebay_status    = '2';// 订单状态：已发货
                }else{
                    continue;
                }

                // 客户信息
                $userInfo       = $order['shippingAddress'];
                $userid         = $order['channelBuyerId'];// 客户ID
                $username       = mysql_real_escape_string($userInfo['fullName']);// 客户姓名
                $usermail       = '';// 邮箱
                $phone          = mysql_real_escape_string($userInfo['phoneNumber']);// 电话处理
                $state          = $userInfo['state'];// 洲
                $postcode       = $userInfo['zip'];// 邮编
                $street         = mysql_real_escape_string($userInfo['address1']);//
                $street1        = mysql_real_escape_string($userInfo['address2']);//
                $city           = $userInfo['city'];// 城市
                $countryname    = 'United States';// 国家名称
                $couny          = 'US';//getcountrysn($userInfo['country']);// 根据英文名获取国家简码
                $currency		= 'USD';
                $order_no		= mysql_real_escape_string($userInfo['order_id']);

                // 订单产品明细
                $orderDetails = $order['items'];

                $sql = "INSERT INTO `ebay_order`(`ebay_paystatus`,`ebay_ordersn` ,`ebay_orderid`,`ebay_createdtime` ,
                    `ebay_paidtime` ,`ebay_userid` ,`ebay_username` ,`ebay_usermail` ,`ebay_street` ,`ebay_street1` ,`ebay_city` ,
                    `ebay_state` ,`ebay_couny` ,`ebay_countryname` ,`ebay_postcode` ,`ebay_phone`,`ebay_currency` ,`ebay_total` ,
                    `ebay_status`,`ebay_user`,`ebay_shipfee`,`ebay_account`,`recordnumber`,`ebay_addtime`,
                    `eBayPaymentStatus`,`ebay_warehouse`,`order_no`,`ebay_ordertype`,`status`)
                    VALUES ('Complete','$ordersn', '$ebay_orderid' , '$createdtime' ,
                     '$paidtime' , '$userid' , '$username' , '$usermail' , '$street' , '$street1' , '$city',
                     '$state' , '$couny' , '$countryname' , '$postcode' , '$phone' , '$currency' , '$ebay_total' , 
                     '$ebay_status','otw','$shipfee','$ebay_account','$recordnumber','$mcTime',
                     '$paystatus','$defaultStoreId','$order_no','OpenSky','$status')";
                // print_r($sql);exit;

                self::$dbcon->execute('delete from ebay_orderdetail where ebay_ordersn="'.$ordersn.'"');// 可能导入订单失败，清除失败的数据

                $detailInsertAll    = true;
                $detailHasInsert    = false;
                foreach($orderDetails as $orderdetail){
                    $sku                = mysql_real_escape_string($orderdetail['SKU']);
                    if(strrpos($sku,'-')){// 读取 - 符号右边的编码
                        $index  = strrpos($sku,'-');
                        $sku    = substr($sku,$index+1);
                    }
                    $ebay_itemprice		= $orderdetail['price'];// 单价
                    $ebay_amount		= $orderdetail['quantity'];// 数量
                    $shipingfee         = $orderdetail['shippingPrice'];// 计算运费
                    $itemtitle          = mysql_real_escape_string($orderdetail['name']);
                    $itemid				= $orderdetail["orderItemId"];// 订单明细编号（用于交运）

                    $esql = "INSERT INTO `ebay_orderdetail` (`recordnumber`,`ebay_ordersn` ,`ebay_itemid` ,`ebay_itemtitle` ,`ebay_itemprice`,
                        `ebay_amount` ,`ebay_createdtime`  ,`ebay_user`,`sku`,`shipingfee`,`ebay_account`,`addtime`,`ebay_tid`,`OrderLineItemID`)
                        VALUES ('$recordnumber','$ordersn', '$itemid' , '$itemtitle' , '$ebay_itemprice' ,
                         '$ebay_amount', '$createdtime' , 'otw','$sku','$shipingfee','$ebay_account','$mcTime','$itemid','$itemid')";
                    // echo $esql;exit;
                    if(self::$dbcon->execute($esql)){
                        $detailHasInsert = true;
                        subOperationLog123("OpenSky/autoLoadOrder".date('Y-m-d'),$ebay_account."--产品明细添加成功--->".$ordersn);
                    }else{
                        $detailInsertAll = false;
                        subOperationLog123("OpenSky/autoLoadOrder".date('Y-m-d'),$ebay_account."--产品明细添加失败--->\r\n".$esql);
                    }
                }
                if($detailHasInsert && $detailInsertAll){
                    if(self::$dbcon->execute($sql)){
                        $success ++;// 记录保存成功的个数

                        $ebay_id = mysql_insert_id();
                        if($ebay_id){
                            include_once dirname(dirname(__FILE__)).'/Classes/RealPaidTime.class.php';
                            RealPaidTime::AddPaidTime($ebay_id, $ebay_account, 'OpenSky', $couny, $paidtime, $createdtime);
                        }

                        subOperationLog123("OpenSky/autoLoadOrder".date('Y-m-d'),$ebay_account."--订单添加成功--->".$ordersn);
                    }else{
                        subOperationLog123("OpenSky/autoLoadOrder".date('Y-m-d'),$ebay_account."--订单添加失败--->\r\n".$sql);
                    }
                }
            }
        }

        return $success;
    }


    /**
     * 判断订单是否存在
     * @param string $order_id     平台订单号(recordnumber字段值)
     * @param string $ebay_account 订单所属平台账户
     * @return bool         存在返回true,否则返回false
     */
    public function judgeOrderExists($order_id,$ebay_account){
        $sql	= "select ebay_id from ebay_order where recordnumber='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $sql	= self::$dbcon->execute($sql);
        $res	= self::$dbcon->getResultArray($sql);
        if(count($res) >= 1){
            return true;
        }
        $sql	="select ebay_id from ebay_order_HistoryRcd where recordnumber='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $sql	= self::$dbcon->execute($sql,false);
        $res	= self::$dbcon->getResultArray($sql);
        if(count($res) >= 1){
            return true;
        }
        return false;
    }


    /**
     * 计算签名
     * @param $sub_url
     * @param $data
     * @return string
     */
    public function getSignature($sub_url,$data){

        $stringToSignComponents[] = $sub_url;
        $stringToSignComponents[] = $this->datetime;
        $stringToSignComponents[] = $this->userkeyid;
        $stringToSignComponents[] = json_encode($data);

        $stringToSign = implode("\n", $stringToSignComponents);
        $signature  = urlsafe(base64_encode(hash_hmac("sha1", $stringToSign, $this->secretkey, true)));
        return $signature;
    }


    /**
     * 获得要交运的订单并交运
     * @param string $account 设置则只交运该账户的订单，否则交运所有账户
     */
    public function getDoTrackingOrdersInfo($account){
        $this->setAccountInfo($account);

        $sime   = strtotime("-0 days");// 付款时间
        $where  = '';
        if($account){
            $where  = ' AND ebay_order.ebay_account="'.$account.'"';
        }
        $shipArray = "SELECT
					ebay_order.ebay_id,
					ebay_order.recordnumber,
					ebay_order.ebay_orderid,
					ebay_order.ebay_account,
					ebay_order.ebay_carrier,
					ebay_order.system_shippingcarriername,
					ebay_order.ebay_tracknumber,
					ebay_orderdetail.ebay_itemid,
					ebay_order.ebay_combine,
					ebay_orderdetail.ebay_id as ebayid ,
					ebay_order.ebay_paidtime
				FROM ebay_order 
				INNER JOIN ebay_orderdetail on ebay_orderdetail.ebay_ordersn=ebay_order.ebay_ordersn 
				WHERE (ebay_order.ebay_ordertype='OpenSky')  ".$where." 
				AND ebay_order.ebay_tracknumber != ''
				AND (ebay_order.ebay_markettime = '' OR isnull(ebay_order.ebay_markettime))
				AND (ebay_orderdetail.ebay_shiptype='' OR  isnull(`ebay_orderdetail`.`ebay_shiptype`))
				AND ebay_order.ebay_status != 274
				AND ebay_order.ebay_paidtime<".$sime ;

        $shipArray = self::$dbcon->query($shipArray);
        $shipArray = self::$dbcon->getResultArray($shipArray);
//        print_r($shipArray);exit;

        if(count($shipArray) > 0){
            // 物流渠道交运代码
            $shipping_code_sql = 'select CONCAT(ebay_carrier,"-",shippingcarrierid) as name,opensky_name FROM system_shippingqudao where opensky_name!="" AND not isnull(opensky_name) ';
            $ebay_carrier = self::$dbcon->query($shipping_code_sql);
            $ebay_carrier = self::$dbcon->getResultArray($ebay_carrier);
            $ebayCarrier = array();
            foreach($ebay_carrier as $y){
                $ebayCarrier[$y['name']] = $y['opensky_name'];
            }
            unset($ebay_carrier);
            $nowTime    = time();
            $iii        = 0;
            $count      = count($shipArray);
            foreach($shipArray as $y){
                $orderShipInfo = array();
                if(empty($y['ebay_tracknumber'])){// 运单号不存在跳过
                    subOperationLog123('OpenSky/autoShipOrder'.date('Y-m-d'),'(AutoConfirm)订单跟踪号缺失:'.$y['ebay_id']);
                    continue;
                }
                $iii ++;
                self::$dbcon->execute("update system_task set TaskDetail='zFrodeAutoConfirmShipOpenSky[ ".$iii." / ".$count." ]' where ID=74 ");
                if(!array_key_exists($y['ebay_carrier'].'-'.$y['system_shippingcarriername'],$ebayCarrier)){// 交运编码不存在跳过
                    echo "order id: ".$y['ebay_id']." carrier error.\r\n<br />";
                    subOperationLog123('autoShipOrder'.date('Y-m-d'),'(AutoConfirm)'.$y['ebay_id'].'：订单发货渠道不对'.$y['ebay_carrier'].'-'.$y['system_shippingcarriername']);
                    continue;
                }

                $orderShipInfo['trackingNumber'] = $y['ebay_tracknumber'];
                $orderShipInfo['carrier']       = $ebayCarrier[$y['ebay_carrier'].'-'.$y['system_shippingcarriername']];
                $orderShipInfo['orderId']       = $y['ebay_orderid'];

//                print_r($orderShipInfo);exit;
                $result = $this->doTracking($orderShipInfo,$this->account);
                $result = $result['results'][0];
                if(isset($result['errors'])){
                    $errorInfo = $result['errors'][0];
                    subOperationLog123('OpenSky/autoShipOrder'.date('Y-m-d'),'(AutoConfirm)交运失败'.
                        $y['ebay_id'].'：'.$y['ebay_carrier'].'-'.$errorInfo['message'].':'.$errorInfo['techDetails']);
                    echo "order id: ".$y['ebay_id']." network error 1\r\n<br />";
                }

//                print_r($result);exit;
                if($result['status'] == 'SUCCESS'){
                    self::$dbcon->execute('update ebay_order set ebay_markettime="'.$nowTime.'",ShippedTime="'.$nowTime.'" where ebay_id='.$y['ebay_id']);
                    self::$dbcon->execute('update ebay_orderdetail set ebay_shiptype="SHIPPED" where ebay_id='.$y['ebayid']);
                    addOrderRemark($y['ebay_id'],'订单['.$y['ebayid'].']标记平台发货成功');
                    echo "V2 Order ID: ".$y['ebay_id']." [Tanga Order: ".$y['ebay_account']." - ".$y['ebay_itemid']."] Success.  \r\n<br />";
                    $sql_markship = 'INSERT INTO `zfrode_order_markship` 
                            SET ebay_id='.$y['ebay_id'].',sham_tracknumber="'.$y['ebay_tracknumber'].'",ebay_carrier="'.$y['ebay_carrier'].'",
                            shippingcarrier_name="'.$y['system_shippingcarriername'].'",ebay_markettime= "'.$nowTime.'"';
                    self::$dbcon->execute($sql_markship);
                }else{
                    subOperationLog123('OpenSky/autoShipOrder'.date('Y-m-d'),'(AutoConfirm)交运失败'.$y['ebay_id'].'：'.
                    $y['ebay_carrier'].'-'.$result['message'].':'.$result['techDetails']);
                }
            }
        }else{
            subOperationLog123('OpenSky/autoShipOrder'.date('Y-m-d'),'(AutoConfirm)查询总数为0，无需交运的订单');
        }
    }

    /**
     * 交运订单  标记发货
     * @param array $orderInfo 交运的订单信息
     * exp: $orderInfo = array(
     *      'trackingNumber' => '运单号',
     *      'orderId' => '订单ID',
     *      'carrier' => '渠道编码'
     *    )
     * @param array $accountInfo 订单账户信息
     * @return mixed
     */
    public function doTracking($orderInfo,$accountInfo){
        $sub_url = '/bis-api/public/api/v1/orders/ship';

        $shipments = array(
            'orderId' => $orderInfo['orderId'],
            'carrier' => $orderInfo['carrier'],
            'trackingNumber' => $orderInfo['trackingNumber']
        );

        $data = array(
            'sellerId' => $this->sellerId,
            'shipments' => array($shipments)
        );
        $signature  = $this->getSignature($sub_url,$data);
        $result     = $this->curlPost($this->api_url.$sub_url,$data,$signature);

        return $result;
    }

    /**
     * POST 方式发送 CURL 请求
     * @param string $url 请求链接
     * @param array $data  发送的数据
     * @param string $signature  签名
     * @return mixed|string
     */
    public function curlPost($url,$data = array(),$signature){
        $postHeader = array(
            "Content-type: application/json",
            'X-OPENSKY-PUBLIC-API-APP-KEY-ID:'.$this->appkeyid,
            'X-OPENSKY-PUBLIC-API-REQ-DATE:'.$this->datetime,
            'X-OPENSKY-PUBLIC-API-REQ-SIGN:'.$signature,
            'X-OPENSKY-PUBLIC-API-USER-KEY-ID:'.$this->userkeyid
        );// 注意设置header，设置出错会导致数据传输不成功

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $postHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($data));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($curl);
        $error = curl_error($curl);

        if($error){
            return $error;
        }
        return json_decode($result,true);
    }
}



