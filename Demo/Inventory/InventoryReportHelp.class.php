<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'include/tools/arrayfunction.php';

/**
 * Class InventoryReportHelp
 * 库存报表查询、数据统计帮助类
 * @author:zwl
 */
class InventoryReportHelp
{

    private static $dbcon       = '';
    private static $dbconErp    = '';
    public static $username     = '';

    private  $inventoryDate;
    public  function __construct() {
        self::init();

        $this->setInventoryDate();
    }

    public static function init()
    {
        global $dbcon, $truename,$dbconErp;
        self::$dbcon    = $dbcon;
        self::$username = $truename;
        self::$dbconErp = $dbconErp;
    }

    //获取期初值
    public function setInventoryDate(){
        $sql = "SELECT MAX(inventory_date) as  inventory_date FROM `inventories` ";
        $inventoryDate = self::$dbcon->query($sql);
        $inventoryDate = self::$dbcon->getResultArray($inventoryDate);

        $this->inventoryDate = '2018-05-14';
        return;

        if(isset($inventoryDate[0]['inventory_date'])){
            $this->inventoryDate =  $inventoryDate[0]['inventory_date'];
        }else{
            $this->inventoryDate = date('Y-m').'-01';
        }
    }


    /**
     * 查询指定SKU的EBAY、WISH、ALI平台的刊登Listing个数
     * @param string|array $skus  SKU编码
     * @return array
     */
    public static function getListingBySku($skus,$accounts){
        self::init();
        $nowTime = time();
        if(is_array($skus)){
            $sku_str = implode("','",$skus);
        }else{
            $sku_str = $skus;
        }
        $counts = array();

        // EBAY刊登LISTING统计
        $sql_ebay = "(SELECT frode_ebay_items.itemid,frode_ebay_items.seller,frode_ebay_items.site,frode_ebay_items.currentprice,
            frode_ebay_items.type,FROM_UNIXTIME(`frode_ebay_items`.`starttime`) AS starttime,
            FROM_UNIXTIME(`frode_ebay_items`.`endtime`) AS endtime,frode_ebay_items.qty,frode_ebay_items.qtysold,
            frode_ebay_items.type,frode_ebay_items.bid,upload_items.user,frode_ebay_items.sku
            FROM frode_ebay_items
            LEFT JOIN upload_items ON upload_items.itemid=frode_ebay_items.itemid
            WHERE frode_ebay_items.sku IN('$sku_str') && frode_ebay_items.type!='FixedPriceItem_Variations' && frode_ebay_items.endtime>'$nowTime')

        UNION ALL

            (SELECT frode_ebay_items.itemid,frode_ebay_items.seller,frode_ebay_items.site,frode_ebay_items.currentprice,
            frode_ebay_items.type,FROM_UNIXTIME(`frode_ebay_items`.`starttime`) AS starttime,
            FROM_UNIXTIME(`frode_ebay_items`.`endtime`) AS endtime,frode_ebay_items_variations.qty,frode_ebay_items_variations.qtysold,
            frode_ebay_items.type,frode_ebay_items.bid,upload_items.user,frode_ebay_items_variations.sku
            FROM frode_ebay_items
            INNER JOIN frode_ebay_items_variations ON frode_ebay_items_variations.itemid=frode_ebay_items.itemid
            LEFT JOIN upload_items ON upload_items.itemid=frode_ebay_items.itemid
            WHERE frode_ebay_items_variations.sku IN('$sku_str') && frode_ebay_items.endtime>'$nowTime')
            ORDER BY site,seller ";
        $sql_ebay = self::$dbconErp->query($sql_ebay);
        $sql_ebay = self::$dbconErp->getResultArray($sql_ebay);
        $ebayCount = array();
        foreach ($sql_ebay as $sku => $ebayListInfo){
            $sku        = strtoupper($ebayListInfo['sku']);
            $seller     = trim($ebayListInfo['seller']);
            $deptname   = $accounts[$seller];// 部门(分ebay1部和ebay2部)

            if(isset($ebayCount[$deptname][$sku])){// 每个部门下SKU Listing数
                $ebayCount[$deptname][$sku] ++ ;
            }else{
                $ebayCount[$deptname][$sku] = 1;
            }
        }


        // ALI刊登LISTING统计
        $sql_ali = "SELECT frode_ali_items.itemid,frode_ali_items.seller,FROM_UNIXTIME(`frode_ali_items`.`endtime`) AS endtime,
                frode_ali_items_variations.startprice,FROM_UNIXTIME(`frode_ali_uploaditems`.`uploadtime`) AS starttime,
                frode_ali_uploaditems.user,frode_ali_items_summary.lotnum,frode_ali_items_variations.sku
            FROM frode_ali_items
            INNER JOIN frode_ali_items_variations ON frode_ali_items_variations.itemid=frode_ali_items.itemid
            LEFT JOIN frode_ali_items_summary ON frode_ali_items_summary.itemid=frode_ali_items.itemid
            LEFT JOIN frode_ali_uploaditems ON frode_ali_uploaditems.itemid=frode_ali_items.itemid
            WHERE frode_ali_items.statustype='onSelling' && frode_ali_items.wsdisplay='expire_offline'
                && frode_ali_items_variations.sku IN('$sku_str') && frode_ali_items.endtime>'$nowTime' ORDER BY seller";
        $sql_ali = self::$dbconErp->query($sql_ali);
        $sql_ali = self::$dbconErp->getResultArray($sql_ali);

        $aliCounts = array();
        foreach ($sql_ali as $aliListInfo){
            $sku = strtoupper($aliListInfo['sku']);
            if(isset($aliCounts[$sku])){
                $aliCounts[$sku] ++ ;
            }else{
                $aliCounts[$sku] = 1;
            }
        }

        // WISH刊登LISTING统计
        $sql_wish = "SELECT frode_wish_items.itemid,frode_wish_items.seller,frode_wish_items.price,frode_wish_items.qty,
        frode_wish_items.variationsku as sku1,frode_wish_items.goods_sn as sku2
        FROM frode_wish_items 
        LEFT JOIN frode_wish_uploaditems ON frode_wish_uploaditems.itemid=frode_wish_items.itemid 
        WHERE (frode_wish_items.variationsku IN('$sku_str')  || frode_wish_items.goods_sn IN('$sku_str')) 
        && frode_wish_items.enabled='True'  ORDER BY seller";
        $sql_wish = self::$dbconErp->query($sql_wish);
        $sql_wish = self::$dbconErp->getResultArray($sql_wish);

        $wishCounts = array();
        foreach ($sql_wish as $wishListInfo){
            $sku1 = strtoupper($wishListInfo['sku1']);// 平台上的SKU编码
            $sku2 = strtoupper($wishListInfo['sku2']);// 同步平台上的listing并去掉子SKU前缀的编码

            if($sku1 != $sku2) {// 子SKU与原SKU不一致时，子SKU与原SKU都加数
                if(isset($wishCounts[$sku2])) {
                    $wishCounts[$sku2]++;
                } else {
                    $wishCounts[$sku2] = 1;
                }
            }
            if(isset($wishCounts[$sku1])){
                $wishCounts[$sku1] ++ ;
            }else{
                $wishCounts[$sku1] = 1;
            }
        }

        $counts = array('ebay' => $ebayCount,'ali' => $aliCounts,'wish' => $wishCounts);
        return $counts;

    }


    /**
     * 根据 Itemid以及类型获取刊登信息
     * @param string $ordertype
     * @param array $itemids
     * @return array
     */
    public static function getListingByItemid($ordertype,$itemids){
        self::init();

        $itemidsStr = implode("','",$itemids);

        $list = array();
        if($ordertype == 'EBAY'){
            $sqlSel = " SELECT a.*,b.user
            FROM frode_ebay_items AS a
            LEFT JOIN upload_items AS b ON a.itemid=b.itemid
            WHERE a.itemid IN('$itemidsStr') ";


            $list = self::$dbconErp->query($sqlSel);
            $list = self::$dbconErp->getResultArray($list);
        }elseif($ordertype == 'WISH'){
            $sqlSel = " SELECT a.*,b.user
            FROM frode_wish_items AS a
            LEFT JOIN frode_wish_uploaditems AS b ON a.itemid=b.itemid 
            WHERE a.itemid IN('$itemidsStr') ";

            $list = self::$dbconErp->query($sqlSel);
            $list = self::$dbconErp->getResultArray($list);

        }elseif($ordertype == 'ALI-EXPRESS'){
            $sqlSel = " SELECT a.*,b.user
            FROM frode_ali_items AS a
            LEFT JOIN frode_ali_uploaditems AS b ON a.itemid=b.itemid
            WHERE a.itemid IN('$itemidsStr') ";

            $list = self::$dbconErp->query($sqlSel);
            $list = self::$dbconErp->getResultArray($list);

        }

        return $list;

    }



    /**
     * 根据采购单、销售订单、实际库存统计生成库存报表并缓存数据
     * @param int $type  是否更新数据 0.不更新，1.更新
     * @param int $valid_second 数据有效时间(0-1800之间)
     * @return array
     */
    public static function createSkuInventoryDatas($type = 1,$valid_second = 1200){
        self::init();

        ignore_user_abort(true);
        $time = time();
        $now_list = 0;// 历史数据(当前报表展示的数据) 更新后删除历史数据，避免更新时间内报表数据真空状态
        $new_list = 0;// 更新后的数据
        $expire_sql = "SELECT s1,s2,now_list FROM sku_inventory_cache WHERE goods_sn='XXXXXXXXXX' LIMIT 1";// 获取缓存有效期
        $expire_sql = self::$dbcon->execute($expire_sql);
        $expire_sql = self::$dbcon->getResultArray($expire_sql);
        if (!empty($expire_sql)) {
            $expire_sql = $expire_sql[0];
            $s1 = $expire_sql['s1'];
            $s2 = $expire_sql['s2'];
            $now_list = $expire_sql['now_list'];

            if($type == 0){// 等于0表示只查询不更新数据
                return array('code' => true,'nowList' => $now_list);
            }
            if(time() - $s1 <= 1800) {// 最后更新时间超过 30 分钟则强制更新数据（防止程序中断后状态一直未更新中）
                if($s2 != 1){// 状态不等于1为数据正在更新中
                    return array('code' => true,'nowList' => $now_list);
                }elseif(time() - $s1 <= $valid_second){ // 未超过20分钟则不更新数据
                    return array('code' => true,'nowList' => $now_list);
                }
            }
        }else{
            $expire_sql = "INSERT INTO sku_inventory_cache(goods_sn,s1,s2) VALUES ('XXXXXXXXXX','$time',1)";// 设置一个缓存时间标记
            self::$dbcon->execute($expire_sql);
        }

        if($now_list == 10){// 变换展示队列
            $new_list = 20;
        }else{
            $new_list = 10;
        }

        // 标记正在更新中
        $sst = self::$dbcon->execute("UPDATE sku_inventory_cache SET s1='$time',s2=0 WHERE goods_sn='XXXXXXXXXX' LIMIT 1");
        $cc = mysql_affected_rows(); // 更新影响的记录数

        if($sst AND $cc){
            // 更新数据
            $sql_deltee = " DELETE FROM `sku_inventory_cache` WHERE `goods_sn` != 'XXXXXXXXXX' AND now_list !='$now_list' ";// 清空数据表
            self::$dbcon->execute($sql_deltee);

            // 获取未分配库存销售订单SKU需求记录
            $sql_sale = "SELECT UPPER(trim(sku)) AS sku,sum(ebay_amount) AS amount
                        FROM ebay_orderdetail AS e_od
                        INNER JOIN ebay_order AS e_o ON e_o.ebay_ordersn=e_od.ebay_ordersn
                        WHERE e_o.ebay_status NOT IN(232,236,268,3,400,2,500,5) AND not(e_o.ebay_status=230 AND e_o.usestore=1)
                        AND e_o.ebay_combine != '1' AND e_o.ebay_warehouse=32
                        GROUP BY e_od.sku
                        HAVING amount > 0 ";
            $sql_sale = self::$dbcon->execute($sql_sale);
            $sql_sale = self::$dbcon->getResultArray($sql_sale);
            $sale_skus = get_array_column($sql_sale, 'amount', 'sku');

            foreach ($sale_skus as $sku => $amount) {
                $sku = strtoupper($sku);
                $sql = "INSERT INTO sku_inventory_cache(goods_sn,sale_need,now_list) VALUES ('$sku',$amount,'$new_list')";
                self::$dbcon->execute($sql);
            }


            // 查询已经存在的SKU
            $exists = self::$dbcon->execute("SELECT goods_sn FROM sku_inventory_cache WHERE now_list='$new_list'  ");
            $exists = self::$dbcon->getResultArray($exists);
            $exists = get_array_column($exists, 'goods_sn', 'goods_sn');
            // 获取销售订单占用库存数
            $sql_usedstock = "SELECT UPPER(trim(b.sku)) AS sku,sum(b.ebay_amount) as amount 
                        FROM ebay_order AS a 
                        INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn = b.ebay_ordersn 
                        WHERE ((a.ebay_status=230 and a.usestore=1) or (a.ebay_status=232)) 
                        and ebay_combine !=  '1' and a.ebay_warehouse =32 
                        GROUP BY b.sku
                        HAVING amount > 0 ";
            $sql_usedstock = self::$dbcon->execute($sql_usedstock);
            $sql_usedstock = self::$dbcon->getResultArray($sql_usedstock);
            $sale_usedskus = get_array_column($sql_usedstock, 'amount', 'sku');
            foreach ($sale_usedskus as $sku => $amount) {
                $sku = strtoupper($sku);
                if (isset($exists[$sku])) {
                    $sql = "UPDATE sku_inventory_cache SET sale_used='$amount' WHERE goods_sn='$sku' AND now_list='$new_list' LIMIT 1 ;";
                } else {
                    $sql = "INSERT INTO sku_inventory_cache(goods_sn,sale_used,now_list) VALUES ('$sku',$amount,'$new_list')";
                }
                self::$dbcon->execute($sql);
            }


            // 查询已经存在的SKU
            $exists = self::$dbcon->execute("SELECT goods_sn FROM sku_inventory_cache WHERE 1 AND now_list='$new_list'  ");
            $exists = self::$dbcon->getResultArray($exists);
            $exists = get_array_column($exists, 'goods_sn', 'goods_sn');

            // 获取采购在途SKU数量
            $sql_pur = "SELECT UPPER(trim(e_iod.goods_sn)) AS goods_sn,sum(e_iod.goods_count-e_iod.goods_count0) as amount
                        FROM ebay_iostore AS e_io
                        JOIN ebay_iostoredetail AS e_iod ON e_io.io_ordersn=e_iod.io_ordersn
                        WHERE type ='2' AND e_io.io_warehouse=32
                        AND e_io.io_status IN('0','1','3','4','5','8')
                        GROUP BY e_iod.goods_sn
                        HAVING amount > 0 ";
            $sql_pur = self::$dbcon->execute($sql_pur);
            $sql_pur = self::$dbcon->getResultArray($sql_pur);
            $pur_skus = get_array_column($sql_pur, 'amount', 'goods_sn');

            foreach ($pur_skus as $sku => $amount) {
                $sku = strtoupper($sku);
                if (isset($exists[$sku])) {
                    $sql = "UPDATE sku_inventory_cache SET pur_onway='$amount' WHERE goods_sn='$sku' AND now_list='$new_list' LIMIT 1 ";
                } else {
                    $sql = "INSERT INTO sku_inventory_cache(goods_sn,pur_onway,now_list) VALUES ('$sku',$amount,'$new_list')";
                }
                self::$dbcon->execute($sql);
            }

            $time = time();
            self::$dbcon->execute("UPDATE sku_inventory_cache SET s1='$time',s2=1,now_list='$new_list' WHERE goods_sn='XXXXXXXXXX' LIMIT 1");
            self::$dbcon->execute("DELETE FROM `sku_inventory_cache` WHERE `goods_sn` != 'XXXXXXXXXX' AND now_list !='$new_list'");// 删除历史数据

            return array('code' => true,'nowList' => $new_list);
        }
        else{
            return array('code' => true,'nowList' => $now_list);
        }

    }

    /**
     * 将 SKU 数组拼接成 SQL IN 的字符串
     * @param $multi_sku
     * @return string
     */
    public function convertMultiSku($multi_sku){
        $multi_sku_str = implode("','",$multi_sku);
        return $multi_sku_str;
    }

    /**
     * 将 仓库ID 数组拼接成 SQL IN 的字符串
     * @param $store_ids
     * @return string
     */
    public function convertStoreIds($store_ids){
        $store_ids_str = implode(',',$store_ids);
        return $store_ids_str;
    }

    /**
     * 创建期初数据
     */
    public function createPeriodBeginStock(){
        $date       = date('Y-m-d');

        // 当前期初已存在则不再创建
        if(isset($this->inventoryDate) AND $this->inventoryDate == $date){
            return true;
        }

        $insert_sql = "INSERT INTO inventories(inventory_date,store_id,goods_id,sku,quantity,goods_price,goods_cost,period_in,period_out)
                    SELECT '$date',e_onh.store_id,e_g.goods_id,e_g.goods_sn,e_onh.goods_count,e_g.goods_price,e_g.goods_cost,period_in,period_out
                    FROM ebay_goods AS e_g
                    LEFT JOIN ebay_onhandle AS e_onh ON e_g.goods_sn=e_onh.goods_sn
                    WHERE 1";

        self::$dbcon->execute($insert_sql);


        // 清空所有产品的本期出入库
        $update_sql = "UPDATE ebay_onhandle SET period_in=0,period_out=0";
        self::$dbcon->execute($update_sql);

    }

    /**
     * 获取SKU的期初库存
     * @param $multi_sku
     * @param $store_ids
     * @return array
     */
    public function getMultiSkuPeriodBeginStock($multi_sku,$store_ids){
        $multi_sku_str = $this->convertMultiSku($multi_sku);
        $store_ids_str = $this->convertStoreIds($store_ids);

        $inventories = "SELECT inventory_date,store_id,sku,quantity AS period_begin_stock 
                    FROM inventories 
                    WHERE sku IN('$multi_sku_str') 
                    AND store_id IN($store_ids_str)
                    AND inventory_date='".$this->inventoryDate."' ";

//        echo $inventories;exit;

        $inventories = self::$dbcon->execute($inventories);
        $inventories = self::$dbcon->getResultArray($inventories);

        $inventoriesTmp = array();
        if(count($inventories)){
            foreach($inventories as $value){
                $inventoriesTmp[$value['store_id']][$value['sku']] = $value;
            }
        }

        unset($inventories);
        return $inventoriesTmp;
    }

    /**
     * 获取SKU的调拨占用库存
     * @param $multi_sku
     * @param $store_ids
     * @return array
     */
    public function getAllocationTakenStock($multi_sku,$store_ids){
        $multi_sku_str = $this->convertMultiSku($multi_sku);
        $store_ids_str = $this->convertStoreIds($store_ids);

        // status.30 已出库  aduit_status.10 审核通过
        $allocation = "SELECT sku,SUM(in_a_d.out_qty) AS allot_taken_stock 
                FROM inventory_allot AS in_a
                LEFT JOIN inventory_allot_detail AS in_a_d ON in_a.allot_sn=in_a_d.allot_sn
                WHERE in_a.status<30 AND in_a.aduit_status=10
                AND in_a.begin_store_id IN($store_ids_str)
                AND in_a_d.sku IN('$multi_sku_str') 
                GROUP BY in_a_d.sku ";

//        echo $allocation;exit;

        $allocation = self::$dbcon->execute($allocation);
        $allocation = self::$dbcon->getResultArray($allocation);


        $allocationTmp = array();
        if(count($allocation)){
            $allocationTmp = get_array_column($allocation,'allot_taken_stock','sku');
        }

        unset($allocation);
        return $allocationTmp;
    }

    /**
     * 获取SKU的调拨在途库存
     * @param $multi_sku
     * @param $store_ids
     * @return array
     */
    public function getAllocationOnwayStock($multi_sku,$store_ids){
        $multi_sku_str = $this->convertMultiSku($multi_sku);
        $store_ids_str = $this->convertStoreIds($store_ids);

        // status.30 已出库  aduit_status.10 审核通过
        $allocation = "SELECT sku,SUM(in_a_d.real_out_qty) AS allot_onway_stock 
                FROM inventory_allot AS in_a
                LEFT JOIN inventory_allot_detail AS in_a_d ON in_a.allot_sn=in_a_d.allot_sn
                WHERE in_a.status=30 AND in_a.aduit_status=10
                AND in_a.begin_store_id IN($store_ids_str)
                AND in_a_d.sku IN('$multi_sku_str') 
                GROUP BY in_a_d.sku ";

//        echo $allocation;exit;

        $allocation = self::$dbcon->execute($allocation);
        $allocation = self::$dbcon->getResultArray($allocation);


        $allocationTmp = array();
        if(count($allocation)){
            $allocationTmp = get_array_column($allocation,'allot_onway_stock','sku');
        }

        unset($allocation);
        return $allocationTmp;
    }

    /**
     * 获取SKU的盘点数量
     * @param $multi_sku
     * @param $store_ids
     * @return array
     */
    public function getCheckStock($multi_sku,$store_ids){
        $multi_sku_str = $this->convertMultiSku($multi_sku);
        $store_ids_str = $this->convertStoreIds($store_ids);

        $period_time    = strtotime($this->inventoryDate);
        $between_time   = "AND e_io.io_addtime>=$period_time ";// 本期盘点（查询时间是本期设置期初的时间之后）
        $between_time   = "AND io.addtime>=$period_time ";// 本期盘点（查询时间是本期设置期初的时间之后）
        // $between_time = '';

        // 盘盈（io_type.43 盘点入库  type.0 入库）
        // 盘亏（io_type.46 盘点出库  type.1 出库）
        $check = "SELECT sku,SUM(check_stock_in) AS stock_in,SUM(check_stock_out) AS stock_out
            FROM (
                SELECT e_iod.goods_sn AS sku,SUM(e_iod.goods_count) AS check_stock_in,0 as check_stock_out
                FROM ebay_iostore AS e_io
                LEFT JOIN ebay_iostoredetail AS e_iod ON e_io.io_ordersn=e_iod.io_ordersn
                WHERE e_io.io_type='43'
                AND e_io.type=0 {$between_time}
                AND e_io.io_warehouse IN($store_ids_str)
                AND e_iod.goods_sn IN('$multi_sku_str')
                GROUP BY e_iod.goods_sn 
                
                UNION ALL 
                
                SELECT e_iod.goods_sn AS sku,0 as check_stock_in,SUM(e_iod.goods_count) AS check_stock_out
                FROM ebay_iostore AS e_io
                LEFT JOIN ebay_iostoredetail AS e_iod ON e_io.io_ordersn=e_iod.io_ordersn
                WHERE e_io.io_type='46'
                AND e_io.type=1 {$between_time}
                AND e_io.io_warehouse IN($store_ids_str)
                AND e_iod.goods_sn IN('$multi_sku_str')
                GROUP BY e_iod.goods_sn 
            ) AS cc 
            GROUP BY cc.sku";

        $check = "SELECT sku,SUM(check_stock_in) AS stock_in,SUM(check_stock_out) AS stock_out
            FROM (
                SELECT io_d.sku,SUM(io_d.quantity)  AS check_stock_in,0 as check_stock_out
                FROM ioorders AS io
                LEFT JOIN ioorders_detail AS io_d ON io_d.ioorder_id=io.id
                WHERE io.io_type=0 
                AND io.order_type=43 {$between_time}
                AND io.store_id IN($store_ids_str)
                AND io_d.sku IN('$multi_sku_str')
                GROUP BY io_d.sku
                
                UNION ALL 
                
                SELECT io_d.sku,0 as check_stock_in,SUM(io_d.quantity)  AS check_stock_out
                FROM ioorders AS io
                LEFT JOIN ioorders_detail AS io_d ON io_d.ioorder_id=io.id
                WHERE io.io_type=0 
                AND io.order_type=46 {$between_time}
                AND io.store_id IN($store_ids_str)
                AND io_d.sku IN('$multi_sku_str')
                GROUP BY io_d.sku
            ) AS cc
            GROUP BY cc.sku";


        // echo $check;exit;

        $check = self::$dbcon->execute($check);
        $check = self::$dbcon->getResultArray($check);


        $checkTmp = array();
        if(count($check)){
            $checkTmp = arrayColumnsToKey($check,'sku');
        }

        unset($check);
        return $checkTmp;

    }

    /**
     * 获取销售订单的SKU的占用库存数量
     * @param $multi_sku
     * @param $store_ids
     * @return array
     */
    public function getOrderTakenStock($multi_sku,$store_ids){
        $multi_sku_str = $this->convertMultiSku($multi_sku);
        $store_ids_str = $this->convertStoreIds($store_ids);

        // ebay_status.230 等待打印  ebay_status.232 等待扫描 usestore.1 已分配库存
        $orderTaken = "SELECT e_od.sku AS sku,SUM(e_od.ebay_amount) AS order_taken_stock
                FROM ebay_order AS e_o
                LEFT JOIN ebay_orderdetail AS e_od ON e_o.ebay_ordersn = e_od.ebay_ordersn 
                WHERE ((e_o.ebay_status=230 AND e_o.usestore=1) OR (e_o.ebay_status=232))
                AND e_od.sku IN('$multi_sku_str')
                AND e_o.ebay_warehouse IN($store_ids_str)
                AND e_o.ebay_combine !='1'
                GROUP BY e_od.sku";

//        echo $orderTaken;exit;

        $orderTaken = self::$dbcon->execute($orderTaken);
        $orderTaken = self::$dbcon->getResultArray($orderTaken);

        $orderTakenTmp = array();
        if(count($orderTaken)){
            $orderTakenTmp = get_array_column($orderTaken,'order_taken_stock','sku');
        }

        unset($orderTaken);
        return $orderTakenTmp;

    }

    /**
     * 获取销售订单的SKU的等待分配库存的数量
     * @param $multi_sku
     * @param $store_ids
     * @return array
     */
    public function getOrderWaitTakenStock($multi_sku,$store_ids) {
        $multi_sku_str = $this->convertMultiSku($multi_sku);
        $store_ids_str = $this->convertStoreIds($store_ids);

        // ebay_status.230 等待打印 usestore.1 已分配库存
        // ebay_status.232 等待扫描 236 已作废 268 物流退件 3 转单发货 400 转单 2 已发货 500 营销订单 5 营销发货
        $orderWaitTaken = "SELECT e_od.sku,SUM(e_od.ebay_amount) AS order_wait_taken_stock
            FROM ebay_order AS e_o
            LEFT JOIN ebay_orderdetail AS e_od ON e_o.ebay_ordersn = e_od.ebay_ordersn 
            WHERE e_od.sku IN('$multi_sku_str')
            AND e_o.ebay_status NOT IN (232,236,268,3,400,2,500,5)
            AND NOT(e_o.ebay_status=230 AND e_o.usestore=1)
            AND e_o.ebay_combine !='1'
            AND e_o.ebay_warehouse IN($store_ids_str)
            GROUP BY e_od.sku";

//        echo$orderWaitTaken;exit;

        $orderWaitTaken = self::$dbcon->execute($orderWaitTaken);
        $orderWaitTaken = self::$dbcon->getResultArray($orderWaitTaken);

        $orderWaitTakenTmp = array();
        if(count($orderWaitTaken)){
            $orderWaitTakenTmp = get_array_column($orderWaitTaken,'order_wait_taken_stock','sku');
        }

        unset($orderWaitTaken);
        return $orderWaitTakenTmp;
    }

    /**
     * 获取采购订单中预订的SKU的未到货数量
     * @param $multi_sku
     * @param $store_ids
     * @return array
     */
    public function getPurchaseOrderBookedNotInStoreStock($multi_sku,$store_ids) {
        $multi_sku_str = $this->convertMultiSku($multi_sku);
        $store_ids_str = $this->convertStoreIds($store_ids);

        $puBookedNotInStore = "SELECT e_iod.goods_sn AS sku,SUM(e_iod.goods_count-e_iod.goods_count0) AS purchase_not_in_stock 
                FROM ebay_iostore AS e_io
                JOIN ebay_iostoredetail AS e_iod ON e_io.io_ordersn=e_iod.io_ordersn	 
                WHERE e_io.type ='2' 
                AND e_io.io_status IN('0','1','3','4','5') 
                AND e_iod.goods_sn IN('$multi_sku_str') 
                AND e_io.io_warehouse IN($store_ids_str)
                GROUP BY e_iod.goods_sn";

        $puBookedNotInStore = self::$dbcon->execute($puBookedNotInStore);
        $puBookedNotInStore = self::$dbcon->getResultArray($puBookedNotInStore);

        $puBookedNotInStoreTmp = array();
        if(count($puBookedNotInStore)){
            $puBookedNotInStoreTmp = get_array_column($puBookedNotInStore,'purchase_not_in_stock','sku');
        }

        unset($puBookedNotInStore);
        return $puBookedNotInStoreTmp;

    }




}

