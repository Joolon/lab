<?php

class FnacApi {


    private static $dbcon = '';
    private static $url='https://vendeur.fnac.com/api.php/';
    private static $partner_id='3320B92F-F2AA-D839-BD33-F03DFE5ECAE8' ;
    private static $shop_id     =  'CFD545EE-B807-90AE-7CF1-522A51A79D3D' ;
    private static $key         =  'FA456886-B04F-8104-B538-EFB9D4B133A3' ;

    public function __construct()
    {
        global $dbcon;
        self::$dbcon = $dbcon;
    }

    /**
     * getcarriers
     * 获得所有物联渠道代码
     */
    function getcarriers(){
        $token = $this->gettoken();
        $order_request_xml=<<<XML
<?xml version="1.0" encoding="utf-8"?>
<carriers_query partner_id="3320B92F-F2AA-D839-BD33-F03DFE5ECAE8" shop_id="CFD545EE-B807-90AE-7CF1-522A51A79D3D" token="$token" xmlns="http://www.fnac.com/schemas/mp-dialog.xsd">
  <query><![CDATA[all]]></query>
</carriers_query>
XML;
        $xmlAuthentication=simplexml_load_string($order_request_xml);

        //发送xml请求到webservice auth
        $response=self::do_post_request(self::$url."carriers_query",$xmlAuthentication->asXML());
        //去除cdata字符串
        $obj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        $eJSON = json_encode($obj);
        $carriers = json_decode($eJSON,true);
        print_r($carriers);
        die;
    }

    /**
     * getorder
     * 获得订单并插入数据库
     */
    function getorder(){
        //获取token值
        $token = $this->gettoken();
        $order_request_xml=<<<XML
<?xml version="1.0" encoding="utf-8"?>
<orders_query results_count="50" partner_id="3320B92F-F2AA-D839-BD33-F03DFE5ECAE8" shop_id="CFD545EE-B807-90AE-7CF1-522A51A79D3D" token="$token" xmlns="http://www.fnac.com/schemas/mp-dialog.xsd">
  <paging>1</paging>
  <state><![CDATA[ToShip]]></state>
</orders_query>
XML;
        $xmlAuthentication=simplexml_load_string($order_request_xml);

        //发送xml请求到webservice auth
        $response=self::do_post_request(self::$url."orders_query",$xmlAuthentication->asXML());
        //去除cdata字符串
        $obj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        $eJSON = json_encode($obj);
        $order = json_decode($eJSON,true);
        foreach ($order['order'] as $key=>$value){
            $aa=is_string($key);
            if ($aa){
                $orders_new[0]=$order['order'];
            }else{
                $orders_new[$key] = $value;
            }
        }
        $this->saveorder($orders_new);
    }

    /**
     * saveorder
     * 保存订单到数据库
     * @param $order
     */
    function saveorder($order){
        foreach ($order as $key => $value){
            if($this->judgeOrderExists($value['order_id'],'f-fangfangstore')){
                echo $value['order_id'].'已存在系统';
                echo '<br/>';
                continue;
            }
            //将数据详情中的数据都变为二维数组
            if(is_array(current($value['order_detail']))){
                $detail=$value['order_detail'];
            }else{
                $detail[0]=$value['order_detail'];
            }
            //算出订单价格
            foreach ($detail as $k => $v){
                $price[$k]=$detail[$k]['price']*$detail[$k]['quantity'];
            }

            if(!$value['shipping_address']['company']){
                $value['shipping_address']['company']='';
            }
            if(!$value['shipping_address']['address1']){
                $value['shipping_address']['address1']='';
            }else{
                $value['shipping_address']['address1']=mysql_real_escape_string($value['shipping_address']['address1']);
            }
            if(!$value['shipping_address']['address2']){
                $value['shipping_address']['address2']='';
            }else{
                $value['shipping_address']['address2']=mysql_real_escape_string($value['shipping_address']['address2']);
            }
            if(!$value['shipping_address']['phone']){
                $value['shipping_address']['phone']='';
            }
            if(!$value['shipping_address']['mobile']){
                $value['shipping_address']['mobile']='';
            }
            if(!$value['billing_address']['company']){
                $value['billing_address']['company']='';
            }
            if(!$value['billing_address']['address1']){
                $value['billing_address']['address1']='';
            }else{
                $value['billing_address']['address1']=mysql_real_escape_string($value['billing_address']['address1']);
            }
            if(!$value['billing_address']['address2']){
                $value['billing_address']['address2']='';
            }else{
                $value['billing_address']['address2']=mysql_real_escape_string($value['billing_address']['address2']);
            }
            if(!$value['billing_address']['phone']){
                $value['billing_address']['phone']='';
            }
            if(!$value['billing_address']['mobile']){
                $value['billing_address']['mobile']='';
            }

            //fnac平台表sql语句
            $fnac_order_sql = "insert into fnac_order(order_id,state,shop_id,client_id,client_firstname,client_lastname,created_at,
                            updated_at,fees,vat_rate,nb_messages,shipping_address_firstname,shipping_address_lastname,shipping_address_company,
                            shipping_address_address1,shipping_address_address2,shipping_address_zipcode,shipping_address_city,shipping_address_country,shipping_address_phone,shipping_address_mobile,
                            billing_address_firstname,billing_address_lastname,billing_address_company,billing_address_address1,billing_address_address2,billing_address_zipcode,billing_address_city,
                            billing_address_country,billing_address_phone,billing_address_mobile)
                            value ('".$value['order_id']."','".$value['state']."','".$value['shop_id']."','".$value['client_id']."','".$value['client_firstname']."','".$value['client_lastname']."','".$value['created_at']."',
                            '".$value['updated_at']."','".$value['fees']."','".$value['vat_rate']."','".$value['nb_messages']."','".$value['shipping_address']['firstname']."','".$value['shipping_address']['lastname']."','".$value['shipping_address']['company']."',
                            '".$value['shipping_address']['address1']."','".$value['shipping_address']['address2']."','".$value['shipping_address']['zipcode']."','".$value['shipping_address']['city']."','".$value['shipping_address']['country']."','".$value['shipping_address']['phone']."','".$value['shipping_address']['mobile']."',
                            '".$value['billing_address']['firstname']."','".$value['billing_address']['lastname']."','".$value['billing_address']['company']."','".$value['billing_address']['address1']."','".$value['billing_address']['address2']."','".$value['billing_address']['zipcode']."','".$value['billing_address']['city']."',
                            '".$value['billing_address']['country']."','".$value['billing_address']['phone']."','".$value['billing_address']['mobile']."')";
            //系统ebay_order表sql
            $status = trim($value['state']);//平台上订单状态
            if($status == 'Shipped'){
                $ebay_paystatus='SHIPPED';
                $ebay_status = '2'; //订单状态为发货
            }else{
                $ebay_paystatus='Complete';
                $ebay_status = '274';//订单状态为新导入
            }
            $ordersn = 'f-fangfangstore-'.$value['order_id'];// V2系统订单编号（账户+订单ID）
            $recordnumber = $value['order_id'];//订单id
            $createdtime = strtotime($value['created_at']);//创建时间
            $paidtime = $createdtime;//付款时间
            $userid='719fangfang@gmail.com';
            $username = $value['client_firstname'].' '.$value['client_lastname'];//客户姓名(FirstName和LastName的拼接)
            //地址1
            if($value['shipping_address']['address1']){
                $street=mysql_real_escape_string($value['shipping_address']['address1']);
            }else{
                $street='';
            }
            //地址2
            if($value['shipping_address']['address2']){
                $street1=mysql_real_escape_string($value['shipping_address']['address2']);
            }else{
                $street1='';
            }
            $city=$value['shipping_address']['city'];
//            $couny=$value['shipping_address']['country'];
//            $countryname=getcountrysn($value['shipping_address']['country']);
            $couny='FR';
            $countryname='France';
            $postcode=$value['shipping_address']['zipcode'];
            if($value['shipping_address']['mobile']){
                $phone=$value['shipping_address']['mobile'];
            }else{
                $phone='';
            }
            $currency = 'EUR';//币种

            $ebay_total = array_sum($price);//订单总金额
            $nowTime= date("Y-m-d H:i:s");
            $mcTime= strtotime($nowTime);//添加到系统的时间
            $ebay_account = 'f-fangfangstore';
            $paystatus ='';
            $defaultStoreId     = 32;// 默认深圳仓
            $order_no = $value['order_id'];
            $order_sql = "INSERT INTO `ebay_order`(`ebay_paystatus`,`ebay_ordersn` ,`ebay_orderid`,`ebay_createdtime` ,
                    `ebay_paidtime` ,`ebay_userid` ,`ebay_username` ,`ebay_usermail` ,`ebay_street` ,`ebay_street1` ,`ebay_city` ,
                    `ebay_state` ,`ebay_couny` ,`ebay_countryname` ,`ebay_postcode` ,`ebay_phone`,`ebay_currency` ,`ebay_total` ,
                    `ebay_status`,`ebay_user`,`ebay_shipfee`,`ebay_account`,`recordnumber`,`ebay_addtime`,
                    `eBayPaymentStatus`,`ebay_warehouse`,`order_no`,`ebay_ordertype`,`status`)
                    VALUES ('$ebay_paystatus','$ordersn', '$recordnumber' , '$createdtime' ,
                     '$paidtime' , '$userid' , '$username' , '' , '$street' , '$street1' , '$city',
                     '$city' , '$couny' , '$countryname' , '$postcode' , '$phone' , '$currency' , '$ebay_total' ,
                     '$ebay_status','otw','','$ebay_account','$recordnumber','$mcTime',
                     '$paystatus','$defaultStoreId','$order_no','FNAC','$status')";
            //循环详情插入到详情表
            foreach ($detail as $key1 => $value1){
                if(!$value1['description']){
                    $value1['description']='';
                }
                if(!$value1['internal_comment']){
                    $value1['internal_comment']='';
                }if(!$value1['tracking_number']){
                    $value1['tracking_number']='';
                }
                $itemid = $value1['order_detail_id'];
                $itemtitle =$value1['product_name'];
                $ebay_itemprice = $value1['price'];
                $ebay_amount = $value1['quantity'];
                $detail_createdtime = $value1['created_at'];
                $sku = $value1['offer_seller_id'];
                $shipingfee = $value1['shipping_price'];

                $fnac_detail_sql = "insert into fnac_order_detail(orderid,order_detail_id,state,product_name,quantity,price,fees,
                            product_fnac_id,offer_fnac_id,offer_seller_id,product_state,description,internal_comment,shipping_price,
                            shipping_method,created_at,product_url,image,tracking_number)
                            value ('".$value['order_id']."','".$value['order_id'].'-'.$value1['order_detail_id']."','".$value1['state']."','".$value1['product_name']."','".$value1['quantity']."','".$value1['price']."','".$value1['fees']."',
                            '".$value1['product_fnac_id']."','".$value1['offer_fnac_id']."','".$value1['offer_seller_id']."','".$value1['product_state']."','".$value1['description']."','".$value1['internal_comment']."','".$value1['shipping_price']."',
                            '".$value1['shipping_method']."','".$value1['created_at']."','".$value1['product_url']."','".$value1['image']."','".$value1['tracking_number']."')";
                $order_detail_sql = "INSERT INTO `ebay_orderdetail` (`recordnumber`,`ebay_ordersn` ,`ebay_itemid` ,`ebay_itemtitle` ,`ebay_itemprice`,
                        `ebay_amount` ,`ebay_createdtime`  ,`ebay_user`,`sku`,`shipingfee`,`ebay_account`,`addtime`,`ebay_tid`,`OrderLineItemID`)
                        VALUES ('$recordnumber','$ordersn', '$itemid' , '$itemtitle' , '$ebay_itemprice' ,
                         '$ebay_amount', '$detail_createdtime' , 'otw','$sku',$shipingfee,'$ebay_account','$mcTime','$itemid','$itemid')";
                if(self::$dbcon->execute($fnac_detail_sql)){
                    echo '--fnac_order_detail success--';
                }else{
                    echo '--fnac_order_detail failure--';
                    echo $fnac_detail_sql;
                }
                if(self::$dbcon->execute($order_detail_sql)){
                    echo '--order_detail success--';
                }else{
                    echo '--order_detail failure--';
                    echo $order_detail_sql;
                }
            }
            if(self::$dbcon->execute($fnac_order_sql)){
                echo '--fnac_order success--';
            }else{
                echo '--fnac_order failure--';
                echo $fnac_order_sql;
            }
            if(self::$dbcon->execute($order_sql)){
                echo 'order success--';
            }else{
                echo '--order failure--';
                echo $order_sql;
            }
            echo '<br/>';
        }

    }


    /**
     * 判断订单是否存在
     * function judgeOrderExists
     * @param $order_id
     * @param $ebay_account
     * @return bool
     */
    function judgeOrderExists($order_id,$ebay_account){
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
     * gettrankingorderinfo
     * 获得要交运的订单
     */
    function gettrankingorderinfo(){
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
				WHERE (ebay_order.ebay_ordertype='FNAC')
				AND ebay_order.ebay_tracknumber != ''
				AND (ebay_order.ebay_markettime = '' OR isnull(ebay_order.ebay_markettime))
				AND (ebay_orderdetail.ebay_shiptype='' OR  isnull(`ebay_orderdetail`.`ebay_shiptype`))
				AND ebay_order.ebay_status != 274";
        $shipArray = self::$dbcon->query($shipArray);
        $shipArray = self::$dbcon->getResultArray($shipArray);
        if (count($shipArray)>0){
            // 物流渠道交运代码
            $shipping_code_sql = 'select CONCAT(ebay_carrier,"-",shippingcarrierid) as name,fnac_name FROM system_shippingqudao where fnac_name!="" AND not isnull(fnac_name) ';
            $ebay_carrier = self::$dbcon->query($shipping_code_sql);
            $ebay_carrier = self::$dbcon->getResultArray($ebay_carrier);
//            $ebayCarrier = array();
//            foreach($ebay_carrier as $y){
//                $ebayCarrier[$y['name']] = $y['opensky_name'];
//            }
            //获取token值
            $token = $this->gettoken();
            foreach ($shipArray as $key => $value){
                $orderid=$value['ebay_orderid'];
                $code = 'CHINASHIPPING';
                $tracknumber=$value['ebay_tracknumber'];
                $ship_request_xml=<<<XML
<?xml version="1.0" encoding="utf-8"?>
<orders_update partner_id="3320B92F-F2AA-D839-BD33-F03DFE5ECAE8" shop_id="CFD545EE-B807-90AE-7CF1-522A51A79D3D" token="$token" xmlns="http://www.fnac.com/schemas/mp-dialog.xsd">
  <order order_id="$orderid" action="confirm_all_to_send">
    <order_detail>
      <action><![CDATA[Shipped]]></action>
    </order_detail>
  </order>
</orders_update>
XML;
                $update_request_xml=<<<XML
<?xml version="1.0" encoding="utf-8"?>
<orders_update partner_id="3320B92F-F2AA-D839-BD33-F03DFE5ECAE8" shop_id="CFD545EE-B807-90AE-7CF1-522A51A79D3D" token="$token" xmlns="http://www.fnac.com/schemas/mp-dialog.xsd">
<order order_id="$orderid" action="update">
    <order_detail>
        <order_detail_id>1</order_detail_id>
        <action><![CDATA[Updated]]></action>
        <tracking_number><![CDATA[$tracknumber]]></tracking_number>
        <tracking_company><![CDATA[$code]]></tracking_company>
    </order_detail>
</order>
</orders_update>
XML;
                $shipxmlAuthentication=simplexml_load_string($ship_request_xml);

                //发送xml请求到
                $shipresponse=self::do_post_request(self::$url."orders_update",$shipxmlAuthentication->asXML());
                //去除cdata字符串
                $shipobj = simplexml_load_string($shipresponse, 'SimpleXMLElement', LIBXML_NOCDATA);
                $shipeJSON = json_encode($shipobj);
                $shipcarriers = json_decode($shipeJSON,true);
                if($shipcarriers['order']['status']=='OK'){
                    $xmlAuthentication=simplexml_load_string($update_request_xml);

                    //发送xml请求到
                    $response=self::do_post_request(self::$url."orders_update",$xmlAuthentication->asXML());
                    //去除cdata字符串
                    $obj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
                    $eJSON = json_encode($obj);
                    $carriers = json_decode($eJSON,true);
                    if($carriers['order']['status']=='OK'){
                        self::$dbcon->execute('update ebay_order set ebay_markettime="'.time().'",ShippedTime="'.time().'" where ebay_id='.$value['ebay_id']);
                        echo '交运成功';
                    }else{
                        echo '交运失败-跟踪号更新失败';
                    }
                }else{
                    echo '交运失败-改变状态失败';
                }
            }
        }else{
            echo '系统中没有要交运的fnac订单';
        }
    }

    /**
     * gettoken
     * 根据参数获得token值
     */
    function gettoken(){
        //步骤1：验证API
        //生成认证请求
        $auth_request_xml=<<<XML
<?xml version="1.0" encoding="utf-8"?>
<auth xmlns='http://www.fnac.com/schemas/mp-dialog.xsd'>
<partner_id>3320B92F-F2AA-D839-BD33-F03DFE5ECAE8</partner_id>
<shop_id>CFD545EE-B807-90AE-7CF1-522A51A79D3D</shop_id>
<key>FA456886-B04F-8104-B538-EFB9D4B133A3</key>
</auth>
XML;
        $xmlAuthentication=simplexml_load_string($auth_request_xml);

        //在请求中加载认证参数
        $xmlAuthentication->partner_id=self::$partner_id;
        $xmlAuthentication->shop_id=self::$shop_id;
        $xmlAuthentication->key=self::$key;

        //发送xml请求到webservice auth
        $response=self::do_post_request(self::$url."auth",$xmlAuthentication->asXML());

//        //去除cdata字符串
//        $obj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
//        $eJSON = json_encode($obj);
//        $token = json_decode($eJSON,true);

        $xmlResponse=simplexml_load_string($response);
        //获得token
        $token=$xmlResponse->token;
        return $token;
    }


    /**
     * do_post_request
     * ===============
     * @param string $url contains the service url to call for the request.
     * @param string $data contains the request to send. In this purpose, XML data are sent.
     *
     *此功能通过POST发送请求。可以使用发送请求的任何方法，这里我们使用cURL会话。
     **/
    function do_post_request($url, $data)
    {
        $ch = curl_init();

        //根据您的系统，您可以添加其他选项或修改以下选项。.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}



