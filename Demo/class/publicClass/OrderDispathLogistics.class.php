<?php
/*
*@name 订单路由匹配类
*@add time 2018-03-29
*@add user tian
*/
include_once dirname(__FILE__)."/LogOper.class.php";
include_once dirname(dirname(__FILE__))."/OrderProcess.class.php";
class OrderDispathLogistics{
    /*
    *@name 自动分配路由
    *@param ebay_id
    *@return 匹配一个路由
    *@add time 2018-03-29 tian
    */
    public static function autoDispathLogistics($ebay_id){
        global $dbcon;
        $return=array('toStatus'=>230,'company'=>'','carrier'=>'','logistics'=>'','notes'=>'');
        $mainDataArray = $dbcon->query('select a.ebay_id,
												a.ebay_ordersn,
												a.ebay_username,
												a.ebay_street,
												a.ebay_street1,
												a.ebay_city,
												a.ebay_state,
												a.ebay_couny,
												a.ebay_countryname,
												a.ebay_postcode,
												a.ebay_phone,
												a.ebay_currency,
												a.ebay_total,
												a.ebay_shipfee,
												a.orderweight,
												a.ebay_carrier,
												a.ebay_account,
												a.ebay_ordertype,
												a.ebay_tracknumber,
												a.ebay_warehouse,
												a.ebay_note,
												a.ebay_noteb,
												b.ebay_id as ebayid,
												b.sku,
												b.ebay_itemprice,
												b.ebay_amount,
												b.shipingfee,
												b.notes,
												c.goods_location,
												c.goods_weight,
												c.goods_name2,
												c.isuse 
									from ebay_order as a 
									inner join ebay_orderdetail as b on a.ebay_ordersn=b.ebay_ordersn 
									left join ebay_goods as c on b.sku=c.goods_sn 
									where a.ebay_id=' . intval($ebay_id) . ' 
									group by b.ebay_id');
        $orderData = $dbcon->getResultArray($mainDataArray);
        //产品名称分类
        $skuAttribute='普货';
        $goods_name2 = '普货';
        foreach($orderData AS $v){
            if($v['goods_name2']!='普通' && $v['goods_name2']!='普货' && $v['goods_name2']){//多个非普货暂时按第一个归类
                $goods_name2 = $v['goods_name2'];
                break;
            }
        }
        switch($goods_name2){//
            case "带电" :
            case "LED带电" :
            case "电子" :
            case "纽扣电池" :
            case "钮扣电池" :
                $skuAttribute = "带电";
                break;
            case "液体" :
                $skuAttribute = "液体";
                break;
            case "电池" :
                $skuAttribute = "电池";
                break;
            case " " :
            case "普通" :
            case "普货" :
                $skuAttribute = "普货";
                break;
            default :
                $skuAttribute = "非普货";

        }
        //根据仓库id获取仓库地域简称
        $warehouse_cn = 'SELECT warehouse_cn FROM order_belong_warehouse WHERE store_id='.$orderData[0]["ebay_warehouse"];
        $warehouse_cn = $dbcon->query($warehouse_cn);
        $warehouse_cn = $dbcon->getResultArray($warehouse_cn);
        $warehouse_cn = $warehouse_cn[0]['warehouse_cn'];
        //汇率
        $totalNow = $orderData[0]["ebay_total"];
        $currency = $orderData[0]["ebay_currency"];
        $toCurrency = "USD";
        $ss = "select rates / (SELECT rates FROM ebay_currency WHERE currency = '$toCurrency' AND USER = 'otw') as rates from ebay_currency where currency='$currency' and user='otw'";
        $ss = $dbcon->execute($ss);
        $ss = $dbcon->getResultArray($ss);
        $ssrates = $ss[0]['rates'] ? $ss[0]['rates'] : 1;
        $totalNow = $totalNow * $ssrates;
        //查询路由
        $ddd = 'select b.id,
							b.name,
							a.ebay_carrier,
							a.usepercent,
							a.priority 
					from ebay_carrierroute AS a
					left join system_shippingcarrier AS b on b.name=a.ebay_logisticsname 
					where a.ebay_countryname="' . mysql_real_escape_string($orderData[0]["ebay_countryname"]) . '" 
					and a.ebay_ordertype="' . $orderData[0]["ebay_ordertype"] . '" 
					and ' . $orderData[0]["orderweight"] . ' between a.weightstart 
					and a.weightend 
					and ' . $totalNow . ' between a.amountstart 
					and a.amountend 
					and a.isuse = 1
					and a.warehouse="' . $warehouse_cn . '" 
					and a.goods_name2="' . $skuAttribute . '" 
					and (a.ebay_account like "%,' . $orderData[0]["ebay_account"] . ',%" || a.ebay_account=",any,") 
					group by a.id 
					order by priority asc';
        $shipDataArray = $dbcon->query($ddd);
        $shipDataArray = $dbcon->getResultArray($shipDataArray);
        if(count($shipDataArray)==0){//匹配不到路由
            $return['toStatus'] = 1;
            $return['notes']='匹配不到路由,转待处理';
//            if(trim($orderData[0]["ebay_note"]) || trim($orderData[0]["ebay_noteb"]) || trim($orderData[0]["notes"])){
//                $return['toStatus'] = 229;
//            }
//            return $return;
        }else{
            $priority = 1000;
            foreach($shipDataArray AS $v){
                if($v['priority']<$priority){//最小  优先级最高
                    $priority = $v['priority'];
                    $return['company'] = $v['name'];
                    $return['carrier'] = $v['ebay_carrier'];
                    $return['logistics'] = $v['id'];
                    $return['notes'] = '自定义分配路由：['.$return['company'].'-----'.$return['carrier'].']';
                }
            }
        }
        //有留言订单处理
        if(trim($orderData[0]["ebay_note"]) || trim($orderData[0]["ebay_noteb"]) || trim($orderData[0]["notes"])){
            $return['toStatus'] = 229;
            $return['notes']='订单有留言,转有留言订单';
        }
        //-----------------------业务需求,订单状态分配 //分配路由后 覆盖状态
        $dispathOrderState=self::dispathOrderState($ebay_id);
        if($dispathOrderState['toStatus']){
            $return['toStatus'] = $dispathOrderState['toStatus'];
            $return['notes']	= '自定义分配路由：'.$dispathOrderState['toStatusName'];
        }

        //已经分配到的路由 特殊处理
        return $return;
    }

    /*
    *@name 保存已经分配渠道的订单
    *@param ebay_id
    *@param logistics_id
    *@param ebay_status
    *@param ebay_carrier
    *@return boolean
    *@add time 2018-03-29 tian
    */
    public static function confirmOrder($ebay_id,$ebay_status,$logistics_id=null,$ebay_carrier=null){
        global $dbcon;
        if(!$ebay_id){
            return false;
        }
        $sql='UPDATE ebay_order SET ebay_status='.$ebay_status.',ebay_confirmtime='.time();
        if($logistics_id){
            $sql .=',system_shippingcarriername="'.$logistics_id.'" ';
        }
        if($ebay_carrier){
            $sql .=',ebay_carrier="'.$ebay_carrier.'" ';
        }
        $sql .='  WHERE ebay_id='.$ebay_id;
        if($dbcon->execute($sql)){
            return true;
        }
        return false;
    }
    /*
    *@name 根据订单条件自定义路由规则
    *@param dataArr
    *@param1 订单id
    *@return 自定义结果
    *@add time 2018-03-29 tian
    */
    public static function definLogistics($ebay_id){
        global $dbcon;
        $return=array('toStatus'=>'','company'=>'','carrier'=>'','logistics'=>'','notes'=>'');
        $orderData=$dbcon->query('select a.ebay_id,
												a.ebay_ordersn,
												a.ebay_username,
												a.ebay_street,
												a.ebay_street1,
												a.ebay_city,
												a.ebay_state,
												a.ebay_couny,
												a.ebay_countryname,
												a.ebay_postcode,
												a.ebay_phone,
												a.ebay_currency,
												a.ebay_total,
												a.ebay_shipfee,
												a.orderweight,
												a.ebay_carrier,
												a.ebay_account,
												a.ebay_ordertype,
												a.ebay_tracknumber,
												a.ebay_note,
												a.ebay_noteb,
												a.ordertype,
												a.ebay_warehouse,
												b.ebay_id as ebayid,
												b.sku,
												b.ebay_itemprice,
												b.ebay_amount,
												b.shipingfee,
												b.notes,
												b.listingtype,
												c.goods_location,
												c.goods_weight,
												c.goods_name2,
												c.isuse 
									from ebay_order as a 
									inner join ebay_orderdetail as b on a.ebay_ordersn=b.ebay_ordersn 
									left join ebay_goods as c on b.sku=c.goods_sn 
									where a.ebay_id=' . intval($ebay_id) . ' 
									group by b.ebay_id');
        $orderData = $dbcon->getResultArray($orderData);
        $orderDataC=count($orderData);
        //产品名称分类
        $skuAttribute='普货';
        $goods_name2 = '普货';
        foreach($orderData AS $v){
            if($v['goods_name2']!='普通' && $v['goods_name2']!='普货' && $v['goods_name2']){//多个非普货暂时按第一个归类
                $goods_name2 = $v['goods_name2'];
                break;
            }
        }
        switch($goods_name2){//
            case "带电" :
            case "LED带电" :
            case "电子" :
            case "纽扣电池" :
            case "钮扣电池" :
                $skuAttribute = "带电";
                break;
            case "液体" :
                $skuAttribute = "液体";
                break;
            case "电池" :
                $skuAttribute = "电池";
                break;
            case " " :
            case "普通" :
            case "普货" :
                $skuAttribute = "普货";
                break;
            default :
                $skuAttribute = "非普货";

        }

        //---------------谷仓的物流渠道begin-------------------2016/1/5-tian
        if ($orderData[0]["ordertype"] == "US-WISHEXPRESS" && $orderData[0]["listingtype"] == "US-WISHEXPRESS" && ($orderData[0]['ebay_ordertype'] == 'WISH海外仓' || $orderData[0]['ebay_ordertype'] == 'TOP')) {//强制覆盖路由规则分配的运输方式
            if ($orderData[0]['ebay_ordertype'] == 'WISH海外仓' && $orderWeight > 0.448) {
                $return['carrier'] = "USPS-BPARCEL";
                $return['logistics'] = 61;
                $return['company'] = "USPS";
            }
            if ($orderData[0]['ebay_ordertype'] == 'WISH海外仓' && $orderWeight < 0.448) {
                $return['carrier'] = "USPS-LWPARCEL";
                $return['logistics'] = 61;
                $return['company'] = "USPS";
            }
            if ($orderData[0]['ebay_ordertype'] == 'TOP' && $orderWeight > 0.448) {
                $return['carrier'] = "FEDEX-LARGEPARCEL";
                $return['logistics'] = 16;
                $return['company'] = "Fedex";
            }
            if ($orderData[0]['ebay_ordertype'] == 'TOP' && $orderWeight < 0.448) {
                $return['carrier'] = "FEDEX-SMALLPARCEL";
                $return['logistics'] = 16;
                $return['company'] = "Fedex";
            }
            //如果不是留言订单，谷仓订单则进入277等待推送
            $return['toStatus'] = 277;
            $return['notes'] = '自定义分配路由：['.$return['company'].'------'.$return['carrier'].']';
        }
        //---------------谷仓的物流渠道end-------------------2017/1/5-tian

        //-------------------amazon几个账号指定sku，分配到云途深圳EUB线下begin------------------------2017/05/08
        for ($s = 0; $s < $orderDataC; $s++) {
            if ($orderData[0]['ebay_account'] == 'sosogo798@gmail.com' && ($orderData[$s]['sku'] == 'Q4018' || $orderData[$s]['sku'] == 'Q3026')) {
                $szEub = $dbcon->query('SELECT encounts FROM ebay_carrier WHERE NAME="云途深圳EUB线下"');
                $szEub = $dbcon->getResultArray($szEub);
                if (stripos($szEub[0]['encounts'], $orderData[0]['ebay_countryname']) !== FALSE && $orderData[0]['goods_location']{0} != 'U') {//云途深圳EUB线下只有深圳走(如果以后不用U这里需要改)
                    $return['carrier'] = '云途深圳EUB线下';
                    $return['logistics'] = '44';
                    $return['company'] = '云途物流';
                    $return['toStatus'] = 230;
                    $return['notes'] = '自定义分配路由：['.$return['company'].'------'.$return['carrier'].']';
                    break;
                } else {//国家不支持，或者调动过仓位
                    $return['toStatus'] = 1;
                    $return['notes'] = '自定义分配路由：云途深圳EUB线下不抵达该国家';
                }
            }
        }

        //-----------------------------EBAY线上EUB begin----------------------------
        if ($orderData[0]['ebay_ordertype'] == 'EBAY订单' && ($orderData[0]['ebay_couny'] == 'US' || $orderData[0]['ebay_countryname'] == 'UNITED STATES')) {
            /*             * ************* S: EUB *************** */
            if ($orderData[0]['ebay_currency'] == 'GBP'){
                $eubPrice = 2.9;
            }elseif ($orderData[0]['ebay_currency'] == 'EUR'){
                $eubPrice = 3.5;
            }else{
                $eubPrice = 5;
            }
            for ($j = 0; $j < $orderDataC; $j++) {
                if ($orderData[$j]['ebay_itemprice'] + $orderData[$j]['shipingfee'] >= $eubPrice && $orderData[0]['goods_location']{0} != 'U') {
                    $return['carrier'] = '云途深圳EUB线上';
                    $return['logistics'] = '44';
                    $return['company'] = '云途物流';
                    $return['toStatus'] = 1;
                    $return['notes'] = '自定义分配路由：['.$return['company'].'-----'.$return['carrier'].']';
                    break;
                }
            }
        }

        //----------------------------WISH妥投规则 begin----------------------------
        if ($orderData[0]['ebay_currency'] == 'USD' && $orderData[0]['ebay_ordertype'] == 'WISH' && $orderData[0]['ebay_total'] < 20) {
            $oldWarehouse = $orderData[0]['ebay_warehouse'];
            if ($orderData[0]['ebay_total'] >= 10 && in_array($orderData[0]['ebay_couny'], array('GB','US','UK','DE','DK','CL','AR','ES','CA','FR', 'SE','MX'))) {
                $ress = self::checkWishOrderPrice($orderData[0]['ebay_id'], 10);
                if ($ress === false) {//没有达到妥投规则
                    $return['toStatus'] = 230;
                    if ($skuAttribute == '普货') {
                        switch ($orderData[0]['ebay_couny']) {
                            case 'US':
                            case 'GB':
                            case 'UK':
                            case 'DE':
                            case 'FR':
                            case 'AR':
                            case 'CL':
                            case 'ES':
                            case 'DK':
                                if ($oldWarehouse == '32') {
                                    $return['carrier'] = "wish邮平邮-东莞仓";
                                    $return['logistics'] = 44;
                                    $return['company'] = '深圳邮局';
                                } elseif ($oldWarehouse == '37') {
                                    $return['carrier'] = "WISH邮小包";
                                    $return['logistics'] = 62;
                                    $return['company'] = '义乌深圳邮局';
                                }
                                break;
                            case 'CA':
                            case 'MX':
                                $return['carrier'] = "京华达HK小包-wish";
                                if ($oldWarehouse == '32') {
                                    $return['logistics'] = 32;
                                    $return['company'] = '京华达';
                                } elseif ($oldWarehouse == '37') {
                                    $return['logistics'] = 40;
                                    $return['company'] = '（义乌）京华达';
                                }
                                break;
                            case 'SE':
                                $return['carrier'] = "云途中欧专线平邮";
                                if ($oldWarehouse == '32') {
                                    $return['logistics'] = 44;
                                    $return['company'] = '云途物流';
                                } elseif ($oldWarehouse == '37') {
                                    $return['logistics'] = 56;
                                    $return['company'] = '义乌云途物流';
                                }
                                break;
                        }
                    } else {//普货之外
                        switch ($orderData[0]['ebay_couny']) {
                            case 'US':
                            case 'GB':
                            case 'UK':
                            case 'FR':
                            case 'DE':
                            case 'ES':
                            case 'CA':
                                $return['carrier'] = "台湾小包国洋";
                                if ($oldWarehouse == '32') {
                                    $return['logistics'] = 87;
                                    $return['company'] = '国洋运通';
                                } elseif ($oldWarehouse == '37') {
                                    $return['logistics'] = 88;
                                    $return['company'] = '（义乌）国洋运通';
                                }
                                break;
                            case 'AR':
                            case 'CL':
                            case 'MX':
                                $return['carrier'] = "比利时小包全球-深圳速递";
                                if ($oldWarehouse == '32') {
                                    $return['logistics'] = 36;
                                    $return['company'] = '深圳速递';
                                } elseif ($oldWarehouse == '37') {
                                    $return['logistics'] = 86;
                                    $return['company'] = '（义乌）深圳速递';
                                }
                                break;
                            case 'SE':
                                $return['carrier'] = "云途中欧专线平邮";
                                if ($oldWarehouse == '32') {
                                    $return['logistics'] = 44;
                                    $return['company'] = '云途物流';
                                } elseif ($oldWarehouse == '37') {
                                    $return['logistics'] = 56;
                                    $return['company'] = '义乌云途物流';
                                }
                                break;
                            case 'DK':
                                $return['carrier'] = "顺丰荷兰小包";
                                if ($oldWarehouse == '32') {
                                    $return['logistics'] = 30;
                                    $return['company'] = '顺丰国际物流';
                                } elseif ($oldWarehouse == '37') {
                                    $return['logistics'] = 48;
                                    $return['company'] = '（义乌）顺丰国际物流';
                                }
                                break;

                        }
                    }
                    $return['notes'] = '自定义分配路由：WISH妥投['.$return['company'].'-----'.$return['carrier'].']';
                }
            }
            if ($orderData[0]['ebay_total'] >= 7 && in_array($orderData[0]['ebay_couny'], array('IT'))) {
                $ress = self::checkWishOrderPrice($orderData[0]['ebay_id'], 7);
                if ($ress === false) {//没有达到妥投规则
                    $return['toStatus'] = 230;
                    if ($skuAttribute == '普货') {
                        switch ($orderData[0]['ebay_couny']) {
                            case 'IT':
                                if ($oldWarehouse == '32') {
                                    $return['carrier'] = "京华达HK小包-wish";
                                    $return['logistics'] = 32;
                                    $return['company'] = '京华达';
                                } elseif ($oldWarehouse == '37') {
                                    $return['carrier'] = "京华达HK小包-wish";
                                    $return['logistics'] = 40;
                                    $return['company'] = '（义乌）京华达';
                                }
                                break;
                        }
                    } else {//普货之外
                        switch ($orderData[0]['ebay_couny']) {
                            case 'IT':
                                if ($oldWarehouse == '32') {
                                    $return['carrier'] = "台湾小包国洋";
                                    $return['logistics'] = 87;
                                    $return['company'] = '国洋运通';
                                } elseif ($oldWarehouse == '37') {
                                    $return['carrier'] = "台湾小包国洋";
                                    $return['logistics'] = 88;
                                    $return['company'] = '（义乌）国洋运通';
                                }
                                break;
                        }
                    }
                    $return['notes'] = '自定义分配路由：WISH妥投['.$return['company'].'-----'.$return['carrier'].']';
                }
            }
            if ($orderData[0]['ebay_total'] >= 3 && in_array($orderData[0]['ebay_couny'], array('RU'))) {
                $ress = self::checkWishOrderPrice($orderData[0]['ebay_id'], 3);
                if ($ress === false) {//没有达到妥投规则
                    $return['toStatus'] = 230;
                    if ($skuAttribute == '普货') {
                        switch ($orderData[0]['ebay_couny']) {
                            case 'RU':
                                if ($oldWarehouse == '32') {
                                    $return['carrier'] = "京华达HK小包-wish";
                                    $return['logistics'] = 32;
                                    $return['company'] = '京华达';
                                } elseif ($oldWarehouse == '37') {
                                    $return['carrier'] = "京华达HK小包-wish";
                                    $return['logistics'] = 40;
                                    $return['company'] = '（义乌）京华达';
                                }
                                break;
                        }
                    } else {//普货之外
                        switch ($orderData[0]['ebay_couny']) {
                            case 'RU':
                                if ($oldWarehouse == '32') {
                                    $return['carrier'] = "台湾小包国洋";
                                    $return['logistics'] = 87;
                                    $return['company'] = '国洋运通';
                                } elseif ($oldWarehouse == '37') {
                                    $return['carrier'] = "台湾小包国洋";
                                    $return['logistics'] = 88;
                                    $return['company'] = '（义乌）国洋运通';
                                }
                                break;
                        }
                    }
                    $return['notes'] = '自定义分配路由：WISH妥投['.$return['company'].'-----'.$return['carrier'].']';
                }
            }
        }
        
        //有留言订单处理
        if(($orderData[0]['ebay_note'] || $orderData[0]['ebay_noteb']) && $return['toStatus']){
            $return['toStatus'] = 229;
        }
        return $return;
    }
    /*
    *@name 分配订单状态
    *@param1 订单id
    *@return array
    *@add time 2018-03-29 tian
    */
    public static function dispathOrderState($ebay_id){
        global $dbcon;
        $return=array('toStatus'=>'','toStatusName'=>'');
        $orderData=$dbcon->query('select a.ebay_id,
												a.ebay_ordersn,
												a.ebay_username,
												a.ebay_street,
												a.ebay_street1,
												a.ebay_city,
												a.ebay_state,
												a.ebay_couny,
												a.ebay_countryname,
												a.ebay_postcode,
												a.ebay_phone,
												a.ebay_currency,
												a.ebay_total,
												a.ebay_shipfee,
												a.orderweight,
												a.ebay_carrier,
												a.ebay_account,
												a.ebay_ordertype,
												a.ebay_tracknumber,
												a.ebay_note,
												a.ebay_noteb,
												a.ordertype,
												b.ebay_id as ebayid,
												b.sku,
												b.ebay_itemprice,
												b.ebay_amount,
												b.shipingfee,
												b.notes,
												b.listingtype,
												c.goods_location,
												c.goods_weight,
												c.goods_name2,
												c.isuse 
									from ebay_order as a 
									inner join ebay_orderdetail as b on a.ebay_ordersn=b.ebay_ordersn 
									left join ebay_goods as c on b.sku=c.goods_sn 
									where a.ebay_id=' . intval($ebay_id) . ' 
									group by b.ebay_id');
        $orderData = $dbcon->getResultArray($orderData);

        //这个规则放在分配路由的前面，难道是平台自带的路由，后面再问他们(已经问过何茂深,确认可以删除这个需求)
//		if ($orderData[0]["ebay_ordertype"] == "ALI-EXPRESS" && $orderData[0]["ebay_carrier"] == "SMT线上-中邮+" && $orderData[0]["ebay_total"] > 7) {
//			$return['toStatus'] = 254;
//			$return['toStatusName'] = 'Ali-订单';
//		}
        //速卖通有运费放ali订单
        if ($orderData[0]['ebay_shipfee'] > 0 && $orderData[0]['ebay_ordertype'] == 'ALI-EXPRESS') {
            $return['toStatus'] = 254;
            $return['toStatusName'] = '速卖通有运费';
        }
        //EBAY墨西哥订单作废
        if ($orderData[0]['ebay_couny'] == 'MX' && in_array($orderData[0]['ebay_ordertype'], array('EBAY订单', 'EBAY订单-US', 'EBAY订单-UK', 'EBAY订单-AU'))) {
            $return['toStatus'] = 236;
            $return['toStatusName'] = '墨西哥订单作废';
        }
        //2018-03-05 梁应聪的需求
        if($orderData[0]['ebay_account']=='ali-cn1001452745' AND (in_array(trim($orderData[0]['ebay_username']),array('Anna Lyashenko','Igor Burula')))){
            $return['toStatus'] = 254;
            $return['toStatusName'] = '代购,放ALI-订单';
        }
        //2018-03-05 邱催催需求
//        if($orderData[0]['ebay_account']=='ali-cn1511164375'){
//            foreach($orderData AS $qcc){
//                if($qcc['sku']=='FZ2038'){
//                    $dbcon->execute("UPDATE ebay_orderdetail SET sku='T5192' WHERE ebay_id={$qcc['ebayid']}");
//                    $return['toStatus'] = 254;
//                    $return['toStatusName'] = '发现sku【FZ2038】,放ALI-订单';
//                    break;
//                }
//            }
//        }
        //2018-03-06 邱催催需求
        if(in_array($orderData[0]['ebay_account'],array('ali-cn1510972133','ali-cn1511194180',"ali-cn1521140640qrtd","ali-cn1521121740zjha","ali-cn1521284244mnqa","ali-cn1521150492gwlc","cn1511234426"))){
            $return['toStatus'] = 254;
            $return['toStatusName'] = '新导入订单,放ALI-订单';
        }
        //---速卖通义乌所有账号 订单总价超过5美金 放ALI-订单begin---2018/01/09-tian
        if ($orderData[0]['ebay_total'] > 5 && $orderData[0]['ebay_currency'] == 'USD' && $orderData[0]['ebay_ordertype'] == 'ALI-EXPRESS' && in_array($orderData[0]['ebay_account'],array('ali-cn1510782465','ali-cn1511164860','ali-cn1511249091','ali-cn1511228502','ali-cn1511234426','ali-cn1510990749','ali-cn1511156618','ali-cn1511265136','ali-cn1511164375','ali-cn1511241161','ali-cn1511245418','ali-cn1511258000','ali-cn1510384389','ali-fzc88','ali-cn1001589976','ali-cn1510972133','ali-cn1511162025','ali-cn1511236840','ali-cn1511251997','ali-cn1511194180','ali-cn1511231068','ali-cn1511256971','ali-cn1510402646','ali-cn1510710537','ali-cn1511239574','ali-cn1511242292','ali-cn1511242702','ali-cn1510289803', 'ali-cn1521127657qsfm','ali-cn1511162025','ali-cn1521167950stht','ali-cn1521140640qrtd','ali-cn1521179139xkgp','ali-cn1521150492gwlc'))) {
            $return['toStatus'] = 254;
            $return['toStatusName'] = '订单总价大于5美元的义乌账号,放ALI-订单';
        }
        //---速卖通组合订单总价值低于5美元的西班牙订单 放ALI-订单begin---2017/08/14-tian(何茂深18-4-24确认干掉这个逻辑--tuliang)
//        if ($orderData[0]['ebay_total'] < 5 && $orderData[0]['ebay_currency'] == 'USD' && $orderData[0]['ebay_ordertype'] == 'ALI-EXPRESS' && $orderData[0]['ebay_couny'] == 'ES') {
//            $return['toStatus'] = 254;
//            $return['toStatusName'] = '订单总价小于5美元';
//        }
        //---速卖通组合订单总价值低于10美元的俄罗斯订单 放ALI-订单
        if ($orderData[0]['ebay_total'] < 10 && $orderData[0]['ebay_currency'] == 'USD' && $orderData[0]['ebay_ordertype'] == 'ALI-EXPRESS' && $orderData[0]['ebay_couny'] == 'RU') {
            $ress = OrderProcess::checkOrderdetailcount($orderData[0]['ebay_id']);//判断是否为组合订单
            if($ress){
                $return['toStatus'] = 254;
                $return['toStatusName'] = '速卖通订单总价小于10美元的俄罗斯订单进入ALI-订单';
            }
        }
        //--ali-cn1521140640qrtd这个账号直接分配ali订单
        if(in_array($orderData[0]['ebay_account'],array('ali-cn1521140640qrtd','ali-cn1521121740zjha'))){
            $return['toStatus'] = 254;
            $return['toStatusName'] = '账号订单直接转ALI订单';
        }

        //EBAY订单金额大于等于100美金，分配到待处理begin------------------------2017/03/01
        if ($orderData[0]['ebay_ordertype'] == 'EBAY订单' && $orderData[0]['ebay_total'] >= 100) {
            $return['toStatus'] = 1;
            $return['toStatusName'] = '超过100美金待处理订单';
        }

        //速卖通组合订单总价值超过5美元，且没有子订单超过5美元 放ALI-订单
        if ($orderData[0]['ebay_total'] >= 5 && $orderData[0]['ebay_currency'] == 'USD' && $orderData[0]['ebay_ordertype'] == 'ALI-EXPRESS') {
            $ress = OrderProcess::checkAliOrderPrice($orderData[0]['ebay_id']);
            if ($ress===false) {
                $return['toStatus'] = 254;
                $return['toStatusName'] = '订单总价大于5美元,且没有子订单超过5美元';
            }
        }
        //amazon广州账号直接放待处理
        if(in_array($orderData[0]['ebay_account'],array('happysky8@outlook.com','kokostore158@gmail.com','limingxie123@gmail.com','jackyang8@outlook.com','wiselyxuan@outlook.com',
            'clearman8@outlook.com','oujuanzi@gmail.com','gigiband8@outlook.com','keykeyone@outlook.com','lingwuxiao@outlook.com','kohlrabichi@163.com','Walsh.d71@yahoo.com'))){
            $return['toStatus'] = 1;
            $return['toStatusName'] = '亚马逊广州账号订单，手动处理';
        }
        //*******************【留个位置】WISH妥投规则

        //amazon几个账号非普货订单，分配到待处理
        $isBattry = OrderProcess::getgoodsattr($orderData[0]['ebay_id']);
        $notConfirmAccount = array('Ohyeahs6@outlook.com', 'blackhorsebiz@gmail.com', 'oujuanzi@gmail.com', 'fangfang959@outlook.com');
        if (in_array($orderData[0]['ebay_account'], $notConfirmAccount) && $isbattry == 1) {
            $return['toStatus'] = 1;
            $return['toStatusName'] = '【待处理】amazon非普货不确单';
        }
        return $return;
    }
    /*
    *@name 不分配路由 直接确单
    *@param $ebay_id  订单号
    *@return array
    *@add time 2018-04-07 tian
    */
    public static function noLogistics($ebay_id){
        global $dbcon;
        $return = array('status'=>'error','toStatus'=>230,'notes'=>'');
        $sql = 'SELECT ebay_id,ebay_ordertype,ebay_account FROM ebay_order WHERE ebay_id='.$ebay_id;
        $sql = $dbcon->query($sql);
        $res = $dbcon->getResultArray($sql);
        $ordertypes = array('LAZADA','SHOPEE');
        if(in_array($res[0]['ebay_ordertype'],$ordertypes)){
            $return['status'] = 'success';
            if($res[0]['ebay_note'] || $res[0]['ebay_noteb']){
                $return['toStatus'] = 229;
                $return['notes'] = '【'.$res[0]['ebay_ordertype'].'】订单有留言';
            }else{
                $return['notes'] = '【'.$res[0]['ebay_ordertype'].'】直接确单';
            }
        }
		//2018-05-08 彭蓉需求
		if(in_array($res[0]['ebay_account'],array('houseshoping@outlook.com','orangestore9@outlook.com'))){
			$return['status'] = 'success';
            $return['toStatus'] = 1;
            $return['notes'] = '销售需要手动确单，转待处理';
        }
        return $return;
    }
    /*
    *@name WISH妥投规则检查
    *@param string $ebay_id 订单编号
    *@add date 2018-01-13 tian
    */
    public static function checkWishOrderPrice($ebay_id,$n=5){
        global $dbcon;
        $returnStr=false;//不需要妥投
        $sql='SELECT a.ebay_total,b.ebay_itemprice,b.ebay_amount,b.shipingfee FROM ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE a.ebay_id='.$ebay_id;
        $sql=$dbcon->query($sql);
        $sql=$dbcon->getResultArray($sql);
        foreach($sql AS $v){//不管是多产品还是单产品都要检查
            $sonOrderPrice='';
            //妥投规则只按1个sku与1个sku的运费之和
            $sonOrderPrice=$v['ebay_itemprice']+($v['shipingfee']/$v['ebay_amount']);
            if($sonOrderPrice>=$n){//只要有子订单价格超过n 说明需要妥投
                $returnStr=true;
                break;
            }
        }
        return $returnStr;
    }
}