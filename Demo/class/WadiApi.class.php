<?php
/**
 * Class WadiApi
 * Wadi平台订单管理对接
 * @author:tl
 */
class WadiApi{

    private static $dbcon = '';

    public function __construct()
    {
        global $dbcon;
        self::$dbcon = $dbcon;
    }

    function getShipmentProviders(){
        $now=date("c");
        $userid = 'fangfang118@yahoo.com';
        $parameters = array(
            'UserID'=>$userid,
            'Version'=>'1.0',
            'Action'=>'GetShipmentProviders',
            'Timestamp'=>$now
        );
        $order_result=self::do_request($parameters);
        var_dump($order_result['result']['Body']);
        die;
    }

    /**
     * 获得单个订单号
     */
    function getorder(){
        $now=date("c");
        $userid = 'fangfang118@yahoo.com';
        $parameters = array(
            'UserID'=>$userid,
            'Version'=>'1.0',
            'Action'=>'GetOrderItems',
            'OrderId'=>'307883241',
            'Timestamp'=>$now
        );

        $order_result=$this->do_request($parameters);
        $orderitem=$order_result['result']['Body']['OrderItems'];
        foreach ($orderitem['OrderItem'] as $ke =>$va){
            $aa=is_string($ke);
            if ($aa){
                $orderitem_new[0]=$orderitem['OrderItem'];
            }else{
                $orderitem_new[$ke] = $va;
            }
        }
        //计算产品数量,同时根据sku去重复(将sku写入键)
        $k=0;
        foreach ($orderitem_new as $key1 => $value1){
            //判断订单是否为取消单
            if ($value1['Status']=='canceled'){
                continue;
            }
            if(strpos($value1['Sku'],' - ') !==false){
                $start = strpos($value1['Sku']," - ");
                $sku=substr($value1['Sku'],0,$start);
            }else{
                $sku = $value1['Sku'];
            }
            $k++;
            $sku_array[$k]=$sku;
            $key_out = $sku; //提取内部一维数组的key(name age)作为外部数组的键

            if(array_key_exists($key_out,$orderitem_new)){
                continue;
            }
            else{
                $new_order[$key_out] = $orderitem_new[$key1]; //以key_out作为外部数组的键
                $arr_wish[$k] = $orderitem_new[$key1];  //实现二维数组唯一性
            }
        }
        //最后结果为sku为键,数量为值
        $count_sku =array_count_values($sku_array);
        print_r($count_sku);
    }

    /**
     * 分页查询订单
     * getpageorders
     */
    function getpageorders($url,$apikey){
        $now=date("c");
        $userid = 'fangfang118@yahoo.com';
        $parameters = array(
            'UserID'=>$userid,
            'Version'=>'1.0',
            'Action'=>'GetOrders',
            'CreatedAfter'=>'2017-08-20',
//            'CreatedBefore'=>'2017-09-22',
            'Offset'=>0,
            'Limit'=>200,
            'Timestamp'=>$now,
            'Status'=>'pending'//ready_to_ship  pending
        );
        $order_result_page=$this->do_request($parameters,$url,$apikey);
        if($order_result_page['result']['Head']['TotalCount'] > 200){
            $Offset = $order_result_page['result']['Head']['TotalCount']/200;
            $page =$Offset - 1;
            for($i = 0; $i <= ceil($page); $i++){
                $parameters = array(
                    'UserID'=>$userid,
                    'Version'=>'1.0',
                    'Action'=>'GetOrders',
                    'CreatedAfter'=>'2017-08-20',
                    'Offset'=>$i,
                    'Limit'=>200,
                    'Timestamp'=>$now,
                    'Status'=>'pending'
                );
                $order_result = $this->do_request($parameters,$url,$apikey);
                $this->getorders($order_result,$url,$apikey);
            }
        }else{
            $this->getorders($order_result_page,$url,$apikey);
        }
    }

    /**
     * 获得订单
     * function getorder
     * @return mixed
     */
    function getorders($order_result,$url,$apikey){
        if (!$order_result['result']['Body']['Orders']){
            echo '没有新订单可以导入系统';
            return;
        }
        $orders=$order_result['result']['Body']['Orders']['Order'];
        foreach ($orders as $ke=>$va){
            $aa=is_string($ke);
            if ($aa){
                $orders_new[0]=$orders;
            }else{
                $orders_new[$ke] = $va;
            }
        }
        $k=0;
        foreach ($orders_new as $key=>$value){
            $k++;

            if ($value['Statuses']['Status'] =='pending' || $value['Statuses']['Status'] =='shipped' || $value['Statuses']['Status'] =='ready_to_ship'){
                $order_array[$k] = $value;
            }
        }
        foreach ($order_array as $key1 => $value111){
            if($this->judgeOrderExists($value111['OrderNumber'],'wadi-fangfang118@yahoo.com')){// 判断订单是否存在，存在则跳过
                continue;
            }
            //插入到wadi_order表
            $OrderId = $value111['OrderId'];
            $CustomerFirstName = $value111['CustomerFirstName'];
            $CustomerLastName = mysql_real_escape_string($value111['CustomerLastName']);
            $OrderNumber = $value111['OrderNumber'];
            $PaymentMethod = $value111['PaymentMethod'];
            if($value111['Remarks']){
                $Remarks= $value111['Remarks'];
            }else{
                $Remarks='';
            }
            if($value111['DeliveryInfo']){
                $DeliveryInfo= $value111['DeliveryInfo'];
            }else{
                $DeliveryInfo='';
            }
            $Price = $value111['Price'];
            $GiftOption = $value111['GiftOption'];
            if($value111['GiftMessage']){
                $GiftMessage= $value111['GiftMessage'];
            }else{
                $GiftMessage='';
            }
            if($value111['VoucherCode']){
                $VoucherCode= $value111['VoucherCode'];
            }else{
                $VoucherCode='';
            }
            $CreatedAt= $value111['CreatedAt'];
            $UpdatedAt= $value111['UpdatedAt'];
            $AddressUpdatedAt = $value111['AddressUpdatedAt'];
            $AddressBilling_FirstName = $value111['AddressBilling']['FirstName'];
            $AddressBilling_LastName = $value111['AddressBilling']['LastName'];
            if ($value111['AddressBilling']['Phone']){
                $AddressBilling_Phone = $value111['AddressBilling']['Phone'];
            }else{
                $AddressBilling_Phone = '';
            }
            if ($value111['AddressBilling']['Phone2']){
                $AddressBilling_Phone2 = $value111['AddressBilling']['Phone2'];
            }else{
                $AddressBilling_Phone2 = '';
            }
            if ($value111['AddressBilling']['Address1']){
                $AddressBilling_Address1 = mysql_real_escape_string($value111['AddressBilling']['Address1']);
            }else{
                $AddressBilling_Address1 = '';
            }
            if ($value111['AddressBilling']['Address2']){
                $AddressBilling_Address2 = mysql_real_escape_string($value111['AddressBilling']['Address2']);
            }else{
                $AddressBilling_Address2 = '';
            }
            if ($value111['AddressBilling']['CustomerEmail']){
                $AddressBilling_CustomerEmail = $value111['AddressBilling']['CustomerEmail'];
            }else{
                $AddressBilling_CustomerEmail = '';
            }
            $AddressBilling_City = $value111['AddressBilling']['City'];
            if ($value111['AddressBilling']['Ward']){
                $AddressBilling_Ward = $value111['AddressBilling']['Ward'];
            }else{
                $AddressBilling_Ward = '';
            }
            if ($value111['AddressBilling']['Region']){
                $AddressBilling_Region = $value111['AddressBilling']['Region'];
            }else{
                $AddressBilling_Region = '';
            }
            if ($value111['AddressBilling']['PostCode']){
                $AddressBilling_PostCode = $value111['AddressBilling']['PostCode'];
            }else{
                $AddressBilling_PostCode = '';
            }
            $AddressBilling_Country = $value111['AddressBilling']['Country'];

            $AddressShipping_FirstName = $value111['AddressShipping']['FirstName'];
            $AddressShipping_LastName = mysql_real_escape_string($value111['AddressShipping']['LastName']);
            if ($value111['AddressShipping']['Phone']){
                $AddressShipping_Phone = $value111['AddressShipping']['Phone'];
            }else{
                $AddressShipping_Phone = '';
            }
            if ($value111['AddressShipping']['Phone2']){
                $AddressShipping_Phone2 = $value111['AddressShipping']['Phone2'];
            }else{
                $AddressShipping_Phone2 = '';
            }
            if ($value111['AddressShipping']['Address1']){
                $AddressShipping_Address1 = mysql_real_escape_string($value111['AddressShipping']['Address1']);
            }else{
                $AddressShipping_Address1 = '';
            }
            if ($value111['AddressShipping']['Address2']){
                $AddressShipping_Address2 = mysql_real_escape_string($value111['AddressShipping']['Address2']);
            }else{
                $AddressShipping_Address2 = '';
            }
            if ($value111['AddressShipping']['CustomerEmail']){
                $AddressShipping_CustomerEmail = $value111['AddressShipping']['CustomerEmail'];
            }else{
                $AddressShipping_CustomerEmail = '';
            }
            $AddressShipping_City = $value111['AddressShipping']['City'];
            if ($value111['AddressShipping']['Ward']){
                $AddressShipping_Ward = $value111['AddressShipping']['Ward'];
            }else{
                $AddressShipping_Ward = '';
            }
            if ($value111['AddressShipping']['Region']){
                $AddressShipping_Region = $value111['AddressShipping']['Region'];
            }else{
                $AddressShipping_Region = '';
            }
            if ($value111['AddressShipping']['PostCode']){
                $AddressShipping_PostCode = $value111['AddressShipping']['PostCode'];
            }else{
                $AddressShipping_PostCode = '';
            }
            $AddressShipping_Country = $value111['AddressShipping']['Country'];
            if($value111['NationalRegistrationNumber']){
                $NationalRegistrationNumber = $value111['NationalRegistrationNumber'];
            }else{
                $NationalRegistrationNumber = '';
            }
            $ItemsCount =$value111['ItemsCount'];
            if($value111['PromisedShippingTime']){
                $PromisedShippingTime = $value111['PromisedShippingTime'];
            }else{
                $PromisedShippingTime = '';
            }
            if($value111['ExtraAttributes']){
                $ExtraAttributes =$value111['ExtraAttributes'];
            }else{
                $ExtraAttributes='';
            }
            $time = time();
            $wadi_sql="INSERT INTO `wadi_order`(`OrderId`,`CustomerFirstName` ,`CustomerLastName`,`OrderNumber` ,`PaymentMethod` ,`Remarks` ,`DeliveryInfo` ,
                    `Price` ,`GiftOption` ,`GiftMessage` ,`VoucherCode` ,`CreatedAt` ,`UpdatedAt` ,`AddressUpdatedAt` ,
                    `NationalRegistrationNumber` ,`ItemsCount` ,`PromisedShippingTime` ,`ExtraAttributes` ,`AddressBilling_FirstName`,`AddressBilling_LastName` ,`AddressBilling_Phone` ,
                    `AddressBilling_Phone2`,`AddressBilling_Address1`,`AddressBilling_Address2`,`AddressBilling_CustomerEmail`,`AddressBilling_City`,`AddressBilling_Ward`,`AddressBilling_Region`,
                    `AddressBilling_PostCode`,`AddressBilling_Country`,`AddressShipping_FirstName`,`AddressShipping_LastName`,`AddressShipping_Phone`,`AddressShipping_Phone2`,`AddressShipping_Address1`,
                    `AddressShipping_Address2`,`AddressShipping_CustomerEmail`,`AddressShipping_City`,`AddressShipping_Ward`,`AddressShipping_Region`,`AddressShipping_PostCode`,`AddressShipping_Country`,`addtime`)
                    VALUES ('$OrderId','$CustomerFirstName', '$CustomerLastName' , '$OrderNumber' , '$PaymentMethod' , '$Remarks' , '$DeliveryInfo' ,
                     '$Price' , '$GiftOption' , '$GiftMessage' , '$VoucherCode' , '$CreatedAt' , '$UpdatedAt' , '$AddressUpdatedAt',
                     '$NationalRegistrationNumber' , '$ItemsCount' , '$PromisedShippingTime' , '$ExtraAttributes' , '$AddressBilling_FirstName' , '$$AddressBilling_FirstName' , '$AddressBilling_Phone' , 
                     '$AddressBilling_Phone','$AddressBilling_Address1','$AddressBilling_Address2','$AddressBilling_CustomerEmail','$AddressBilling_City','$AddressBilling_Ward','$AddressBilling_Region',
                     '$AddressBilling_PostCode','$AddressBilling_Country','$AddressBilling_Country','$AddressShipping_LastName','$AddressShipping_Phone','$AddressShipping_Phone','$AddressShipping_Address1',
                     '$AddressShipping_Address2','$AddressShipping_CustomerEmail','$AddressShipping_City','$AddressShipping_Ward','$AddressShipping_Region','$AddressShipping_PostCode','$AddressShipping_Country','$time')";
            if(self::$dbcon->execute($wadi_sql)){
                echo '--wadi_order success--';
            }else{
                echo '--wadi_order failure--';
                echo $wadi_sql;
            }
            $data=$this->saveOrder($value111,$url,$apikey);
        }
    }

    /**
     * 保存订单到库
     * function saveOrder
     */
    function saveOrder($order,$url,$apikey){
        //获得产品信息
        $now=date("c");
        $userid = 'fangfang118@yahoo.com';

        $parameters = array(
            'UserID'=>$userid,
            'Version'=>'1.0',
            'Action'=>'GetOrderItems',
            'OrderId'=>$order['OrderId'],
            'Timestamp'=>$now,
        );

        $order_result=$this->do_request($parameters,$url,$apikey);
        $orderitem=$order_result['result']['Body']['OrderItems'];
        

        //订单信息
        $ordersn = 'wadi-fangfang118@yahoo.com-'.$order['OrderNumber'];// V2系统订单编号（账户+订单ID）
        $recordnumber       = $order["OrderNumber"];//订单id
        $createdtime = strtotime($order['CreatedAt']);//创建时间
        $paidtime = $createdtime;//付款时间
        $paystatus = '';//付款状态
        $status = trim($order['Statuses']['Status']);//平台上订单状态
        if($status == 'shipped'){
            $ebay_paystatus='SHIPPED';
            $ebay_status = '2'; //订单状态为发货
        }else{
            $ebay_paystatus='Complete';
            $ebay_status = '274';//订单状态为新导入
        }
        $ebay_total	= $order['Price'];//订单总金额(包含物流)
        $ebay_account = 'wadi-fangfang118@yahoo.com';
        
        //客户信息
        $userInfo = $order['AddressShipping'];

        $username = mysql_real_escape_string($userInfo['FirstName'].' '.$userInfo['LastName']);//客户姓名(FirstName和LastName的拼接)
        //客户电话
        if($userInfo['Phone']){
            $phone=$userInfo['Phone'];
        }else{
            $phone='';
        }
        //邮箱
        if($userInfo['CustomerEmail']){
            $usermail=$userInfo['CustomerEmail'];
        }else{
            $usermail='';
        }
        //邮编
        if($userInfo['PostCode']){
            $postcode=$userInfo['PostCode'];
        }else{
            $postcode='';
        }
        //地址1
        if($userInfo['Address1']){
            $street=mysql_real_escape_string($userInfo['Address1']);
        }else{
            $street='';
        }
        //地址2
        if($userInfo['Address2']){
            $street1=mysql_real_escape_string($userInfo['Address2']);
        }else{
            $street1='';
        }
        $city   = $userInfo['City'];//城市
        if($userInfo['Country'] =='KSA'){
            $couny ='SA';
            $countryname = getcountryen($couny);
        }
//        $countryname    = $userInfo['Country'];//国家名称
//        $couny= getcountrysn($userInfo['Country']);//根据英文名获取国家简码
        $order_no = $order['OrderId'];//订单id(与订单编号不同)
        $nowTime= date("Y-m-d H:i:s");
        $mcTime= strtotime($nowTime);//添加到系统的时间
        $defaultStoreId     = 32;// 默认深圳仓
        $currency = 'SAR';

        $order_sql = "INSERT INTO `ebay_order`(`ebay_paystatus`,`ebay_ordersn` ,`ebay_orderid`,`ebay_createdtime` ,
                    `ebay_paidtime` ,`ebay_userid` ,`ebay_username` ,`ebay_usermail` ,`ebay_street` ,`ebay_street1` ,`ebay_city` ,
                    `ebay_state` ,`ebay_couny` ,`ebay_countryname` ,`ebay_postcode` ,`ebay_phone`,`ebay_currency` ,`ebay_total` ,
                    `ebay_status`,`ebay_user`,`ebay_shipfee`,`ebay_account`,`recordnumber`,`ebay_addtime`,
                    `eBayPaymentStatus`,`ebay_warehouse`,`order_no`,`ebay_ordertype`,`status`)
                    VALUES ('$ebay_paystatus','$ordersn', '$recordnumber' , '$createdtime' ,
                     '$paidtime' , '$userid' , '$username' , '$usermail' , '$street' , '$street1' , '$city',
                     '' , '$couny' , '$countryname' , '$postcode' , '$phone' , '$currency' , '$ebay_total' , 
                     '$ebay_status','otw','','$ebay_account','$recordnumber','$mcTime',
                     '$paystatus','$defaultStoreId','$order_no','WADI','$status')";
        $detailInsertAll    = true;
        $detailHasInsert    = false;
        if(is_array($orderitem)){
            $k = 0;
            $new_order=array();

            foreach ($orderitem['OrderItem'] as $ke =>$va){
                $aa=is_string($ke);
                if ($aa){
                    $orderitem_new[0]=$orderitem['OrderItem'];
                }else{
                    $orderitem_new[$ke] = $va;
                }
            }
            //插入wadi平台详情表
            foreach ($orderitem_new as $newkey => $newvalue){
                //判断订单是否为取消单
                if ($newvalue['Status']=='canceled'){
                    continue;
                }
                if(strpos($newvalue['Sku'],' - ') !==false){
                    $start = strpos($newvalue['Sku']," - ");
                    $Sku=substr($newvalue['Sku'],0,$start);
                }else{
                    $Sku = $newvalue['Sku'];
                }
                $OrderItemId= $newvalue['OrderItemId'];
                $orderdetail_item_id= $newvalue['OrderId'].'-'.$Sku;
                $ShopId= $newvalue['ShopId'];
                $OrderId= $newvalue['OrderId'];
                $Name= mysql_real_escape_string($newvalue['Name']);
                $ShopSku= $newvalue['ShopSku'];
                $ItemPrice= $newvalue['ItemPrice'];
                $PaidPrice= $newvalue['PaidPrice'];
                $WalletCredits= $newvalue['WalletCredits'];
                $TaxAmount= $newvalue['TaxAmount'];
                $ShippingAmount= $newvalue['ShippingAmount'];
                $VoucherAmount= $newvalue['VoucherAmount'];
                $IsDigital= $newvalue['IsDigital'];
                if($newvalue['TrackingCode']){
                    $TrackingCode= $newvalue['TrackingCode'];
                }else{
                    $TrackingCode='';
                }
                if($newvalue['PurchaseOrderId']){
                    $PurchaseOrderId= $newvalue['PurchaseOrderId'];
                }else{
                    $PurchaseOrderId='';
                }
                if($newvalue['PurchaseOrderNumber']){
                    $PurchaseOrderNumber= $newvalue['PurchaseOrderNumber'];
                }else{
                    $PurchaseOrderNumber='';
                }
                $PromisedShippingTimes= '';
                $ExtraAttributes= '';
                $CreatedAt= $newvalue['CreatedAt'];
                $UpdatedAt= $newvalue['UpdatedAt'];
                $ShipmentProvider = $newvalue['ShipmentProvider'];
                $time = time();
                //将数据插入wadi_order_detail表
                $wadi_sql = "INSERT INTO `wadi_order_detail` (`OrderItemId`,`orderdetail_item_id` ,`ShopId` ,`OrderId` ,`Name`,`Sku`,`ShopSku`,
                        `ItemPrice` ,`PaidPrice`  ,`WalletCredits`,`TaxAmount`,`ShippingAmount`,`VoucherAmount`,`IsDigital`,
                        `TrackingCode`,`PurchaseOrderId`,`PurchaseOrderNumber`,`PromisedShippingTimes`,`ExtraAttributes`,`CreatedAt`,`UpdatedAt`,`ShipmentProvider`,`addtime`)
                        VALUES ('$OrderItemId','$orderdetail_item_id', '$ShopId' , '$OrderId' , '$Name' ,'$Sku' ,'$ShopSku' ,
                        '$ItemPrice' ,'$PaidPrice'  ,'$WalletCredits' ,'$TaxAmount' ,'$ShippingAmount' ,'$VoucherAmount' ,'$IsDigital',
                         '$TrackingCode','$PurchaseOrderId','$PurchaseOrderNumber','$PromisedShippingTimes','$ExtraAttributes','$CreatedAt','$UpdatedAt','$UpdatedAt','$time')";
                if(self::$dbcon->execute($wadi_sql)){
                    echo '--wadi_detail success--';
                }else{
                    echo '--wadi_detail failure--';
                    echo $wadi_sql;
                }
            }
            //计算产品数量,同时根据sku去重复(将sku写入键)
            foreach ($orderitem_new as $key1 => $value1){
                //判断订单是否为取消单
                if ($value1['Status']=='canceled'){
                    continue;
                }
                if(strpos($value1['Sku'],' - ') !==false){
                    $start = strpos($value1['Sku']," - ");
                    $sku=substr($value1['Sku'],0,$start);
                }else{
                    $sku = $value1['Sku'];
                }
                $k++;
                $sku_array[$k]=$sku;
                $key_out = $sku; //提取内部一维数组的key(name age)作为外部数组的键

                if(array_key_exists($key_out,$new_order)){
                    continue;
                }
                else{
                    $new_order[$key_out] = $orderitem_new[$key1]; //以key_out作为外部数组的键
                    $arr_wish[$k] = $orderitem_new[$key1];  //实现二维数组唯一性
                }
            }
            //最后结果为sku为键,数量为值
            $count_sku =array_count_values($sku_array);

            //订单产品明细
            foreach ($new_order as $key =>$value ){
                //判断订单是否为取消单
                if ($value['Status']=='canceled'){
                    continue;
                }
                //订单产品信息
                //sku清洗
                if(strpos($value['Sku'],' - ') !==false){
                    $start = strpos($value['Sku']," - ");
                    $sku=substr($value['Sku'],0,$start);
                }else{
                    $sku = $value['Sku'];
                }
                $itemid= $value["OrderId"].'-'.$sku;// 订单明细编号(存储OrderId和SKU关联wadi_order_detail表orderdetail_item_id字段,用于交运)
                $itemtitle = mysql_real_escape_string($value['Name']);//产品名称
                $ebay_itemprice = $value['ItemPrice'];//单价
                $ebay_amount =$count_sku[$sku];//数量
                $shipingfee = $value['ShippingAmount'];
                $itemsql = "INSERT INTO `ebay_orderdetail` (`recordnumber`,`ebay_ordersn` ,`ebay_itemid` ,`ebay_itemtitle` ,`ebay_itemprice`,
                        `ebay_amount` ,`ebay_createdtime`  ,`ebay_user`,`sku`,`shipingfee`,`ebay_account`,`addtime`,`ebay_tid`,`OrderLineItemID`)
                        VALUES ('$recordnumber','$ordersn', '$itemid' , '$itemtitle' , '$ebay_itemprice' ,
                         '$ebay_amount', '$createdtime' , 'otw','$sku',$shipingfee,'$ebay_account','$mcTime','$itemid','$itemid')";
                if(self::$dbcon->execute($itemsql)){
                    $detailHasInsert = true;
                    echo '--ebay_orderdetail success--';
                }else{
                    $detailInsertAll = false;
                    echo '--ebay_orderdetail failure--';
                }
            }
        }
        //成功条数
        $success= 0;
        if($detailHasInsert && $detailInsertAll){
            if(self::$dbcon->execute($order_sql)){
                $success++;// 记录保存成功的个数
                echo '--order success--';
            }else{
                echo '--success failure--';
                echo $order_sql;
            }
        }
        echo $success;
        return $success;
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

    function getship(){
        $now=date("c");
        $userid = 'fangfang118@yahoo.com';
        $parameters = array(
            'UserID'=>$userid,
            'Version'=>'1.0',
            'Action'=>'GetShipmentProviders',
            'Timestamp'=>$now,
//                'OrderItemId'=>$itemlist[0]['OrderItemId']
        );
        $providers_result=self::do_request($parameters);
        print_r($providers_result);
    }


    /**
     * curl请求发送
     * function do_request
     * @param $parameters
     * @return mixed
     */
    function do_request($parameters,$url,$apikey){

        //排序
        ksort($parameters);
        //对参数进行循环编码
        $encoded = array();
        foreach ($parameters as $name => $value) {
            $encoded[] = rawurlencode($name) . '=' . rawurlencode($value);
        }
        //将数组通过&连接成一个字符串
        $concatenated = implode('&', $encoded);
        //通过hash_hmac去进行MD5值的计算
        $parameters['Signature'] = rawurlencode(hash_hmac('sha256', $concatenated, $apikey, false));


        $queryString = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        // 打开curl连接
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url."?".$queryString);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // 将结果保存到$data
        $output = curl_exec($curl);
        // 关闭curl连接
        curl_close($curl);
        //将xml数据转为数组
        $xml=simplexml_load_string($output);
        $data['result'] = json_decode(json_encode($xml),TRUE);
        return $data;
    }

    /**
     * 系统自动交运
     */
    function ship_order($ebay_id){
        $jumia_sql = "SELECT w.OrderId,o.ebay_tracknumber FROM wadi_order as w 
                      LEFT JOIN ebay_order AS o ON o.recordnumber=w.`OrderNumber` 
                      WHERE o.ebay_id=".$ebay_id;
        $orderidlist = DB::QuerySQL($jumia_sql);
        $sql = "SELECT OrderItemId,Sku FROM jumia_order_detail WHERE OrderId=".$orderidlist[0]['OrderId'];
        $itemlist = DB::QuerySQL($sql);
        $now=date("c");
        $userid = 'fangfang118@yahoo.com';
        $orderitem_array=array();
        foreach ($itemlist as $key1 => $value1){
            array_push($orderitem_array,(string)$value1['OrderItemId']);
        }
        $OrderItemId='['.implode(",",$orderitem_array).']';
        $parameters = array(
            'UserID'=>$userid,
            'Version'=>'1.0',
            'Action'=>'SetStatusToReadyToShip',
            'Timestamp'=>$now,
            'OrderItemIds'=>$OrderItemId,
            'DeliveryType'=>'dropship',
//            'ShippingProvider'=>$gys,
//            'TrackingNumber'=>$trackcode
        );
    }

    function updatetoshipptime(){
        $sql = "SELECT ebay_id,ebay_ordertype,ebay_paidtime,ebay_status from ebay_order WHERE ebay_ordertype = 'WADI' AND ebay_status in (274,1)";
        $sql	= self::$dbcon->execute($sql);
        $sql	= self::$dbcon->getResultArray($sql);
        if($sql){
            foreach ($sql as $key => $value){
                $time=$value['ebay_paidtime'];
                $threedaytime=date(strtotime('-3 day'));
                if($threedaytime>$time){
                    $update_sql = "UPDATE `ebay_order` SET `ebay_status`='231' WHERE (`ebay_id`='".$value['ebay_id']."')";
                    $update_sql	= self::$dbcon->execute($update_sql);
                    $update_sql	= self::$dbcon->getResultArray($update_sql);
                    if($update_sql!=0){
                        echo '订单'.$value['ebay_id'].'状态已改为跟进订单';
                    }
                }else{
                    echo '付款时间小于3天';
                }
            }
        }else{
            echo '系统中没有订单';
        }
    }
}
?>