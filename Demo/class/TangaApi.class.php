<?php

/**
 * Class TangaApi
 * Tanga平台订单管理对接
 * @author:zwl
 */
class TangaApi {

    private static $dbcon = '';

    private $api_url = 'https://vendors.tanga.com';// 订单处理URL
    private $vendor_id = '';
    private $access_token = '';
    public $account = '';
    public $password = '';

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
        if($account_info){
            $this->account = $account;
            $this->vendor_id = $account_info['tanga_vendor_id'];
            $this->access_token = $account_info['tanga_token'];
            $this->password = $account_info['tanga_password'];
        }
        subOperationLog123("tangaOrderLogin",$account.'-->setAccountInfo:'.json_encode(array_values($account_info)));
    }

    /**
     * 获得账户的认证信息
     * @param string $account 销售账户
     * @return mixed
     */
    public function getAccountInfo($account){
        $sql = 'select id,ebay_account,tanga_token,tanga_vendor_id,tanga_password
                from ebay_account 
                where tanga_vendor_id !="" and not isnull(tanga_vendor_id) and active=0 
                and ebay_user="otw" and ebay_account="'.$account.'" limit 1 ';
        $sql = self::$dbcon->query($sql);
        $sql = self::$dbcon->getResultArray($sql);
        // print_r($sql);exit;
        if(count($sql)){
            return $sql[0];
        }else{
            return false;
        }
    }

    /**
     * 登录权限认证（无用）
     */
    public function getAuthentication(){
        $sub_url = '/api/v1/drop_shippers/'.$this->vendor_id.'/products';
        $header = array(
            'Authorization: Basic '.$this->access_token,
            'credentials: '.$this->account.':'.$this->password
        );
        $res = $this->curlGet($this->api_url.$sub_url,'',$header);
        print_r($res);exit;
    }

    /**
     * 从平台上抓取订单导入V2
     * @return mixed
     */
    public function getOrders(){
		// echo 1;exit;
        $sub_url            = '/api/vendors/'.$this->vendor_id.'/unshipped_items';
        $end_date			= date('Y-m-d');
        $start_date			= date('Y-m-d',strtotime("$end_date - 5 days"));
        $params = array(
            'start_at'  => $start_date.'T00:00:00',// ISO-8601标准时间
            'end_at'    => $end_date.'T00:00:00'
        );

        $header = array('Authorization: Basic '.$this->access_token);

        $orders = $this->curlGet($this->api_url.$sub_url,$params,$header);

        // print_r($orders);exit;
        if($orders === false){
            subOperationLog123("tangaAutoLoadOrder".date('Y-m-d'),$this->account."获取订单失败-->接口请求错误");
			return '接口请求错误';
        }elseif(empty($orders['error'])){// 保存订单
            $nowTime	        = date("Y-m-d H:i:s");
            $mcTime		        = strtotime($nowTime);
            $defaultStoreId     = 32;// 默认深圳仓
            foreach ($orders as $order){
                if(empty($this->account)){// 账户缺失则跳过保存数据，防止单号ID相同删除数据
                    echo 'CheckAccount销售账户缺失：'.$this->account.'<br/>';
                    subOperationLog123("tangaAutoLoadOrder".date('Y-m-d'),'销售账户缺失：'.$this->account);
                    return false;
                }
                if($this->judgeOrderExists($order["order_id"],$this->account)){// 判断订单是否存在，存在则跳过
                    continue;
                }
                $ebay_ordersn = $this->account.'-'.$order['order_id'];
                $ebay_paystatus	 = 'Complete';
                $recordnumber = $order['purchase_order_id'];
                $ebay_orderid = $order['order_id'];
                $ebay_ptid = $order['purchase_order_id'];
                $ebay_createdtime = strtotime($order['ordered_at']);
                $ebay_paidtime	= $ebay_createdtime;// 付款时间
                $ebay_account = $this->account;
                $ebay_ordertype = 'TANGA';
                $ebay_warehouse = $defaultStoreId;
                $ebay_site = 'US';
                $ebay_couny = 'US';// 国家
                $ebay_countryname = 'United States';// 国家名称

                $ebay_username = mysql_escape_string(trim($order['shipping_name']));
                $ebay_street = mysql_escape_string(trim($order['shipping_address1']));
                $ebay_street1 = mysql_escape_string(trim($order['shipping_address2']));
                $ebay_city = $order['shipping_city'];
                $ebay_state = $order['shipping_state'];
                $ebay_postcode = $order['shipping_zip'];
                $ebay_phone = $order['shipping_phone'];
                $ebay_currency	= 'USD';
                $ebay_status = '274';

                self::$dbcon->execute('delete from ebay_orderdetail where ebay_ordersn="'.$ebay_ordersn.'"');// 可能导入订单失败，清除失败的数据

                $detailInsertAll    = true;
                $detailHasInsert    = false;

                $details = $order['line_items'];

                $ebay_total = 0;
                $ebay_shipfee = 0;
                // print_r($details);exit;
                foreach ($details as $detail){
                    $ebay_itemid    = $detail['line_item_id'];
                    $sku            = $detail['sku_code'];
                    $ebay_amount    = $detail['quantity'];
                    $ebay_itemtitle = mysql_escape_string(trim($detail['sku_name']));
                    $ebay_itemprice = $detail['cost'];// 单价
                    $total_cost     = $detail['total_cost'];
                    $shipingfee     = $detail['shipping_cost'];

                    $ebay_total     += $total_cost;// 订单总金额
                    $ebay_shipfee   += $shipingfee;//订单总运费

                    $esql = "INSERT INTO `ebay_orderdetail` (`recordnumber`,`ebay_ordersn` ,`ebay_itemid` ,`ebay_itemtitle` ,`ebay_itemprice`,
                        `ebay_amount` ,`ebay_createdtime`  ,`ebay_user`,`sku`,`shipingfee`,`ebay_account`,`addtime`,`ebay_tid`)
                        VALUES ('$recordnumber','$ebay_ordersn', '$ebay_itemid' , '$ebay_itemtitle' , '$ebay_itemprice' ,
                         '$ebay_amount', '$ebay_createdtime' , 'otw','$sku','$shipingfee','$ebay_account','$mcTime','')";
                    // echo $esql;exit;
                    if(self::$dbcon->execute($esql)){
                        $detailHasInsert = true;
                        subOperationLog123("tangaAutoLoadOrder".date('Y-m-d'),$ebay_account."--产品明细添加成功--->".$esql);
                    }else{
                        $detailInsertAll = false;
                        subOperationLog123("tangaAutoLoadOrder".date('Y-m-d'),$ebay_account."--产品明细添加失败--->".$esql);
                    }
                }
                if($detailInsertAll && $detailHasInsert){
                    $sql = "INSERT INTO `ebay_order`(`ebay_paystatus`,`ebay_ordersn` ,`ebay_orderid`,`ebay_ptid`,`ebay_createdtime` ,
                        `ebay_paidtime` ,`ebay_userid` ,`ebay_username` ,`ebay_usermail` ,`ebay_street` ,`ebay_street1` ,`ebay_city` ,
                        `ebay_state` ,`ebay_couny` ,`ebay_countryname` ,`ebay_postcode` ,`ebay_phone`,`ebay_currency` ,`ebay_total` ,
                        `ebay_status`,`ebay_user`,`ebay_shipfee`,`ebay_account`,`recordnumber`,`ebay_addtime`,`ebay_note`,
                        `eBayPaymentStatus`,`ShippedTime`,`RefundAmount`,`ebay_warehouse`,`ebay_site`,`order_no`,`ebay_ordertype`,`status`)
                        VALUES ('$ebay_paystatus','$ebay_ordersn', '$ebay_orderid','$ebay_ptid' , '$ebay_createdtime' ,
                         '$ebay_paidtime' , '' , '$ebay_username' , '' , '$ebay_street' , '$ebay_street1' , '$ebay_city',
                         '$ebay_state' , '$ebay_couny' , '$ebay_countryname' , '$ebay_postcode' , '$ebay_phone' , '$ebay_currency' , '$ebay_total' , 
                         '$ebay_status','otw','$ebay_shipfee','$ebay_account','$recordnumber','$mcTime','',
                         '','','','$ebay_warehouse','$ebay_site','','$ebay_ordertype','')";
                    // print_r($sql);exit;
                    if(self::$dbcon->execute($sql)){
                        
                        $ebay_id = mysql_insert_id();
                        if($ebay_id){
                            include_once dirname(dirname(__FILE__)).'/Classes/RealPaidTime.class.php';
                            RealPaidTime::AddPaidTime($ebay_id, $ebay_account, $ebay_ordertype, $ebay_couny, $ebay_paidtime, $order['ordered_at']);
                        }
                        
                        subOperationLog123("tangaAutoLoadOrder".date('Y-m-d'),$ebay_account."--订单添加成功--->".$sql);
                    }else{
                        subOperationLog123("tangaAutoLoadOrder".date('Y-m-d'),$ebay_account."--订单添加失败--->".$sql);
                    }
                }
            }
            return count($orders);
        }else{
            $error = isset($orders['error'])?$orders['error']:'';
            subOperationLog123("tangaAutoLoadOrder".date('Y-m-d'),$this->account."获取订单添加失败-->".$error);
			return '查询错误:'.$error;
        }
    }

    /**
     * 获得要交运的订单并交运
     * @param string $account 设置则只交运该账户的订单，否则交运所有账户
     */
    public function getDoTrackingOrdersInfo($account =''){
        $sime   = strtotime("-2 days");// 付款时间超过4天开始交运
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
				WHERE (ebay_order.ebay_ordertype='TANGA')  ".$where." 
				AND ebay_order.ebay_tracknumber != ''
				AND (ebay_order.ebay_markettime = '' OR isnull(ebay_order.ebay_markettime))
				AND (ebay_orderdetail.ebay_shiptype='' OR  isnull(`ebay_orderdetail`.`ebay_shiptype`))
				AND ebay_order.ebay_status != 274
				AND ebay_order.ebay_paidtime<".$sime;

        $shipArray = self::$dbcon->query($shipArray);
        $shipArray = self::$dbcon->getResultArray($shipArray);
//         print_r($shipArray);exit;
		
		
		// subOperationLog123('tangaAutoShipOrder'.date('Y-m-d'),'查询订单：'.count($shipArray).'个');exit;
        if(count($shipArray) > 0){
            // 获得交运的账号
            if($account) $where  = ' AND ebay_account="'.$account.'"';
            $acc_sql = "select id,ebay_account,tanga_token,tanga_vendor_id,tanga_password 
                    from ebay_account
                    where active=0 and ebay_user='otw' and tanga_vendor_id!=''  ".$where." 
                    and ebay_account like 'tga-%' ";
            $acc_sql = self::$dbcon->query($acc_sql);
            $ebay_account = self::$dbcon->getResultArray($acc_sql);
            $ebayAccounts = array();
            foreach ($ebay_account as $value) {
                $ebayAccounts[trim($value['ebay_account'])] = $value;
            }

            // 物流渠道交运代码
            $shipping_code_sql = 'select CONCAT(ebay_carrier,"-",shippingcarrierid) as name,tanga_name FROM system_shippingqudao where tanga_name!="" AND not isnull(tanga_name) ';
            $ebay_carrier = self::$dbcon->query($shipping_code_sql);
            $ebay_carrier = self::$dbcon->getResultArray($ebay_carrier);
            $ebayCarrier = array();
            foreach($ebay_carrier as $y){
                $ebayCarrier[$y['name']] = $y['tanga_name'];
            }
            unset($ebay_carrier);
            $nowTime = time();
            // print_r($ebayCarrier);exit;

            $iii = 0;
            $count = count($shipArray);
            foreach($shipArray as $y){
                $orderShipInfo = array();
                if(empty($y['ebay_tracknumber'])){// 运单号不存在跳过
                    subOperationLog123('tangaAutoShipOrder'.date('Y-m-d'),'(AutoConfirm)订单跟踪号缺失');
                    continue;
                }
                $iii ++;
                self::$dbcon->execute("update system_task set TaskDetail='zFrodeAutoConfirmShiTanga[ ".$iii." / ".$count." ]' where ID=62 ");
                if(!array_key_exists($y['ebay_carrier'].'-'.$y['system_shippingcarriername'],$ebayCarrier)){// 交运编码不存在跳过
                    echo "order id: ".$y['ebay_id']." carrier error.\r\n<br />";
                    subOperationLog123('tangaAutoShipOrder'.date('Y-m-d'),'(AutoConfirm)'.$y['ebay_id'].'：订单发货渠道不对'.$y['ebay_carrier'].'-'.$y['system_shippingcarriername']);
                    continue;
                }
                if(!$y['ebay_itemid']){// 销售平台订单明细ID
                    echo "order id: ".$y['ebay_id']." has partial error.\r\n<br />";
                    continue;
                }

                $orderShipInfo['tracking_number'] = $y['ebay_tracknumber'];
                $orderShipInfo['carrier'] = $ebayCarrier[$y['ebay_carrier'].'-'.$y['system_shippingcarriername']];

                $y['ebay_combine'] = trim($y['ebay_combine']);
                if($y['ebay_combine']){// 组合订单分别对每个子订单交运相同运单号
                    $ebayCombine = explode('##',$y['ebay_combine']);
                    foreach($ebayCombine as $m){
                        $m = trim($m);
                        if($m=='') continue;

                        $shipArrayChild = self::$dbcon->query('select ebay_orderdetail.ebay_id as ebayid,ebay_orderdetail.ebay_itemid from ebay_order inner join ebay_orderdetail on ebay_orderdetail.ebay_ordersn=ebay_order.ebay_ordersn where ebay_order.ebay_id='.$m.' and (ebay_orderdetail.ebay_shiptype="APPROVED" || ebay_orderdetail.ebay_shiptype="" ||  isnull(`ebay_orderdetail`.`ebay_shiptype`))');
                        $shipArrayChild = self::$dbcon->getResultArray($shipArrayChild);
                        foreach($shipArrayChild as $w){
                            $w['ebay_itemid'] = trim($w['ebay_itemid']);
                            if(!$w['ebay_itemid']){
                                echo "order id: ".$y['ebay_id']." has partial error.\r\n<br />";
                                continue;
                            }
                            $orderShipInfo['package_id'] = $w['ebay_itemid'];
                            $result = $this->doTracking($orderShipInfo,$ebayAccounts[$y['ebay_account']]);
                            if(isset($result['error']) AND trim($result['error'])){
                                subOperationLog123('tangaAutoShipOrder'.date('Y-m-d'),'(AutoConfirm)'.$y['ebay_id'].'：'.$y['ebay_carrier'].'-'.$result['error']);
                                echo "order id: ".$m." network error.\r\n<br />";
                                for($f=0;$f<3;$f++){
                                    $result = $this->doTracking($orderShipInfo,$ebayAccounts[$y['ebay_account']]);
                                    if(!isset($result['error'])) break;
                                    else echo "order id: ".$m." network error 1\r\n<br />";
                                }
                            }
                            if(isset($result['shipment']) AND empty($result['error'])){
                                self::$dbcon->execute('update ebay_order set ebay_markettime="'.$nowTime.'",ShippedTime="'.$nowTime.'" where ebay_id='.$m);
                                self::$dbcon->execute('update ebay_orderdetail set ebay_shiptype="SHIPPED" where ebay_id='.$w['ebayid']);
                                addOrderRemark($m,'子订单['.$w['ebayid'].']标记平台发货成功');
                                echo "V2 Order ID: ".$m." [Tanga Order: ".$y['ebay_account']." - ".$w['ebay_itemid']."] Success. \r\n<br />";
                                $sql_markship = 'INSERT INTO `zfrode_order_markship` 
                                    SET ebay_id='.$m.',sham_tracknumber="'.$y['ebay_tracknumber'].'",ebay_carrier="'.$y['ebay_carrier'].'",
                                    shippingcarrier_name="'.$y['system_shippingcarriername'].'",ebay_markettime= "'.$nowTime.'"';
                                self::$dbcon->execute($sql_markship);
                            }
                            else echo "V2 Order ID: ".$m." [Tanga Order: ".$y['ebay_account']." - ".$w['ebay_itemid']."] <b>Error</b>. ".$result['error']." \r\n<br />";
                        }
                    }
                }

                $orderShipInfo['package_id'] = $y['ebay_itemid'];
                $result = $this->doTracking($orderShipInfo,$ebayAccounts[$y['ebay_account']]);
                if(isset($result['error']) AND trim($result['error'])){
                    subOperationLog123('tangaAutoShipOrder'.date('Y-m-d'),'(AutoConfirm)'.$y['ebay_id'].'：'.$y['ebay_carrier'].'-'.$result['error']);
                    echo "order id: ".$y['ebay_id']." network error 1\r\n<br />";
                    for($f=0;$f<3;$f++){
                        $result = $this->doTracking($orderShipInfo,$ebayAccounts[$y['ebay_account']]);
                        if(!isset($result['error'])) break;
                        else echo "order id: ".$y['ebay_id']." network error 1\r\n<br />";
                    }
                }
                if(isset($result['shipment']) AND empty($result['error'])){
                    self::$dbcon->execute('update ebay_order set ebay_markettime="'.$nowTime.'",ShippedTime="'.$nowTime.'" where ebay_id='.$y['ebay_id']);
                    self::$dbcon->execute('update ebay_orderdetail set ebay_shiptype="SHIPPED" where ebay_id='.$y['ebayid']);
                    addOrderRemark($y['ebay_id'],'订单['.$y['ebayid'].']标记平台发货成功');
                    echo "V2 Order ID: ".$y['ebay_id']." [Tanga Order: ".$y['ebay_account']." - ".$y['ebay_itemid']."] Success.  \r\n<br />";
                    $sql_markship = 'INSERT INTO `zfrode_order_markship` 
                            SET ebay_id='.$y['ebay_id'].',sham_tracknumber="'.$y['ebay_tracknumber'].'",ebay_carrier="'.$y['ebay_carrier'].'",
                            shippingcarrier_name="'.$y['system_shippingcarriername'].'",ebay_markettime= "'.$nowTime.'"';
                    self::$dbcon->execute($sql_markship);
                }
                else{
                    echo "V2 Order ID: ".$y['ebay_id']." [Tanga Order: ".$y['ebay_account']." - ".
                        $y['ebay_itemid']."] <b>Error</b>. ".$result['error']." \r\n<br />";
                }
            }
        }
    }

    /**
     * 交运订单  标记发货
     * Valid : carriers are ChinaPost YunExpress SingaporePost TNTExpress OnTrac Norco PostNL LaserShip DHLGlobalMail
     * DHLGlobalmailInternational DHLExpress USPS FedEx FedExSmartPost ChinaEMS FlytExpress UPS UPSMailInnovations POSMalaysia
     * @param array $orderInfo 交运的订单信息
     * exp: $orderInfo = array(
     *      'tracking_number' => '运单号',
     *      'package_id' => 'order_id',
     *      'carrier' => '渠道编码'
     *    )
     * @param array $accountInfo 订单账户信息
     * exp: $accountInfo = array(
     *      'tanga_vendor_id' => '', //客户ID
     *      'tanga_token' => '',// 用户秘钥
     *    )
     * @return mixed
     */
    public function doTracking($orderInfo,$accountInfo){
        $vendor_id      = $accountInfo['tanga_vendor_id'];
        $access_token   = $accountInfo['tanga_token'];
        $sub_url        = '/api/vendors/'.$vendor_id.'/set_tracking';

        if(!($orderInfo['tracking_number'] && $orderInfo['package_id'] && $orderInfo['carrier']) ){
            return array('error' => true,'message' => '运单号/物流代码/订单号缺失');
        }

        $header = array('Authorization: Basic '.$access_token);// 权限认证

        $params = array(
            'id'                => $vendor_id,
            'tracking_number'   => $orderInfo['tracking_number'],
            'package_id'        => $orderInfo['package_id'],// order_id or package_id or the line_item_id
            'carrier'           => $orderInfo['carrier']
        );

        $res = $this->curlGet($this->api_url.$sub_url,$params,$header);
//         var_dump($res);exit;
        return $res;
    }

    /**
     * 判断订单是否存在
     * @param string $order_id     平台订单号(recordnumber字段值)
     * @param string $ebay_account 订单所属平台账户
     * @return bool         存在返回true,否则返回false
     */
    public function judgeOrderExists($order_id,$ebay_account){
        $sql	= "select ebay_id from ebay_order where ebay_orderid='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $sql	= self::$dbcon->execute($sql);
        $res	= self::$dbcon->getResultArray($sql);
        if(count($res) >= 1){
            return true;
        }
        $sql	="select ebay_id from ebay_order_HistoryRcd where ebay_orderid='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $sql	= self::$dbcon->execute($sql,false);
        $res	= self::$dbcon->getResultArray($sql);
        if(count($res) >= 1){
            return true;
        }
        return false;
    }

    public function curlGet($url,$parameters = array(),$header = array()){
        if($parameters){
            $url .= '?'.http_build_query($parameters);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        if($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($ch);
        $error = curl_error($ch);
//		 var_dump($result);var_dump($error);sleep(12);exit;
        curl_close($ch) ;
        if($error){
            subOperationLog123('loadTangaOrder'.date('Y-m-d'),$this->account.'获取订单错误信息('.$error.')');
        }
        return json_decode($result,true);
    }

}


