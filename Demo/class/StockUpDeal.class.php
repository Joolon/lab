<?php

/**
 * Class StockUpDeal
 * 销售订单备货管理
 * @author:zwl
 */
class StockUpDeal {

    private static $dbcon = '';
    public static $username = '';
    public static $groupLeader = array('otw','邓秋菊','高波','郭娇','杨燕','肖康萍','黄英玲','曹阳','邓铭昌','肖云',
        '魏长越','陈新','韦凤婷','刘枫','刘国纪');// 组长
    public static $sartap = array('otw','邓秋菊','陈飞','梁应聪','何茂深');// 主管
    public static $sale_plats = array(35 => 'AMZ-FBA',38 => 'ALIE-HBA');
    public static $sale_store = array(35 => '坂田仓库(FBA)',38 => '坂田仓库(HBA)');

    public static $show_all = array('otw','周锟','钟牧良','余伦平');

    public function __construct()
    {
        global $dbcon,$truename;
        self::$dbcon = $dbcon;
        self::$username = $truename;
    }

    /**
     * 生成指定表中指定字段 唯一个编号
     * @param string $prefix  编号前缀
     * @param string $table   目标表
     * @param string $column  目标列
     * @return string
     */
    public function createOrderSn($prefix = 'OP-',$table = 'store_out_plan_order',$column='plan_order_sn'){
        $flag = false;
        $orderSn = '';
        while(!$flag){
            $orderSn = $prefix.date('YmdHis'). mt_rand(100, 999);
            $exists = "SELECT count(1) AS c FROM $table WHERE $column='$orderSn' LIMIT 1";
            $exists = self::$dbcon->query($exists);
            $exists = self::$dbcon->getResultArray($exists);
            if(!$exists[0]['c']){
                $flag = true;
            }
        }
        return $orderSn;

    }

    /**
     * 查询备货需求单 指定产品编码和销售平台 未发需求数
     * @param string $goods 产品编码
     * @param string $sale_plat  销售平台类型
     * @return mixed
     */
    public function getStockUpOrderCountByGoods($goods = '',$sale_plat){
        // 获取备货单需求数量(未出库数量)
        $sql_stockup = "SELECT stu_o_d.goods_sn,sum(stu_o_d.amount-stu_o_d.has_amount) AS num 
        FROM stockup_order AS stu_o
        LEFT JOIN stockup_order_details AS stu_o_d ON (stu_o.id=stu_o_d.stockup_id AND stu_o_d.io_status!=-10 AND stu_o_d.io_status!=30)
        WHERE stu_o.status=20 AND stu_o.sale_plat='$sale_plat' ";

        if(is_array($goods)){
            $sku_str = implode("','",$goods);
            $sql_stockup .= " AND stu_o_d.goods_sn IN ('$sku_str') ";
        }elseif((is_string($goods) OR is_int($goods)) AND $goods != ''){
            $sql_stockup .= " AND stu_o_d.goods_sn='$goods' ";
        }

        $sql_stockup .= " GROUP BY stu_o_d.goods_sn ORDER BY stu_o_d.goods_sn ASC";
        $stockUpDatas = self::$dbcon->execute($sql_stockup);
        $stockUpDatas = self::$dbcon->getResultArray($stockUpDatas);
        $stockUpDatas = get_array_column($stockUpDatas,'num','goods_sn');
        $stockUpDatas = array_change_key_case($stockUpDatas,CASE_UPPER);// 键名都转换为大写
        return $stockUpDatas;

    }


    /**
     * 查询出库计划单 指定产品编码|计划单ID 的产品总数
     * @param string $goods
     * @param int $type  1.查找SKU，2.查找ID
     * @param int $store_id 目标仓库
     * @return array|resource
     */
    public function getStoreOutPlanCountByGoods($goods = '',$type = 1,$store_id){
        // 获取出库计划单占用库存
        $sql_store = "SELECT sopo_g.goods_sn,sum(sopo_g.amount) AS amount
                    FROM store_out_plan_order AS sopo
                    LEFT JOIN store_out_plan_order_goods AS sopo_g ON sopo.plan_order_sn=sopo_g.plan_order_sn
                    WHERE sopo.status=20 AND sopo.store_id='$store_id' ";
        if(is_string($goods) AND $goods != ''){
            if($type == 1){
                $sql_store .= " AND sopo_g.goods_sn='$goods' ";
            }elseif($type == 2){
                $sql_store .= " AND sopo.id='$goods' ";
            }
        }elseif(is_array($goods)){
            if($type == 1) {
                $sku_str = implode("','", $goods);
                $sql_store .= " AND sopo_g.goods_sn IN ('$sku_str') ";
            }elseif($type == 2){
                $id_str = implode(",", $goods);
                $sql_store .= " AND sopo.id IN ($id_str) ";
            }
        }
        $sql_store .= " GROUP BY sopo_g.goods_sn ";

        $storeOutPlans = self::$dbcon->execute($sql_store);
        $storeOutPlans = self::$dbcon->getResultArray($storeOutPlans);
        $storeOutPlans = get_array_column($storeOutPlans,'amount','goods_sn');
        $storeOutPlans = array_change_key_case($storeOutPlans,CASE_UPPER);// 键名都转换为大写
        return $storeOutPlans;
    }

    /**
     * 获取产品已经预订的数量
     * @param string $goods
     * @param $store_id
     * @return array|resource
     */
    public function getIOorderCountByGoods($goods = '',$store_id){
        // 获取采购在途数量
        $sql_booked = "SELECT goods_sn,sum(b.goods_count-b.goods_count0) as amount 
                FROM ebay_iostore AS a 
                JOIN ebay_iostoredetail AS b ON a.io_ordersn	 = b.io_ordersn	 
                WHERE a.io_status IN('0','1','3','4','5','8') and type ='2' and a.io_warehouse='$store_id' ";
        if(is_string($goods) AND $goods != ''){
            $sql_booked .= " AND b.goods_sn='$goods' ";
        }elseif(is_array($goods)){
            $sku_str = implode("','",$goods);
            $sql_booked .= " AND b.goods_sn IN ('$sku_str') ";
        }
        $sql_booked .= " GROUP BY goods_sn";
        $bookedCounts = self::$dbcon->execute($sql_booked);
        $bookedCounts = self::$dbcon->getResultArray($bookedCounts);
        $bookedCounts = get_array_column($bookedCounts,'amount','goods_sn');
        $bookedCounts = array_change_key_case($bookedCounts,CASE_UPPER);// 键名都转换为大写
        return $bookedCounts;
    }


    /**
     * 获取 指定产品 指定仓库的 实际库存
     * @param string $goods
     * @param int $type 1查询库存大于零 0.查询库存为零 -1.查询库存小于零 2.查询所有
     * @param $store_id
     * @return array|resource
     */
    public function getOnhandleCountByGoods($goods = '',$type = 2,$store_id){
        // 获取实际库存
        $sql_goodscount = "SELECT goods_sn,goods_count FROM ebay_onhandle 
            WHERE store_id='$store_id' ";
        if(is_string($goods) AND $goods != ''){
            $sql_goodscount .= " AND goods_sn='$goods' ";
        }elseif(is_array($goods)){
            $sku_str = implode("','",$goods);
            $sql_goodscount .= " AND goods_sn IN ('$sku_str') ";
        }
        if($type == 1){
            $sql_goodscount .= " AND goods_count>0 ";
        }elseif($type == 0){
            $sql_goodscount .= " AND goods_count=0 ";
        }elseif($type == -1){
            $sql_goodscount .= " AND goods_count<0 ";
        }
        $goodsCounts = self::$dbcon->execute($sql_goodscount);
        $goodsCounts = self::$dbcon->getResultArray($goodsCounts);
        $goodsCounts = get_array_column($goodsCounts,'goods_count','goods_sn');
        $goodsCounts = array_change_key_case($goodsCounts,CASE_UPPER);
        return $goodsCounts;
    }


    /**
     * F(H)BA创建采购计划的入口
     * @param string $jihuaid  采购计划批次编号
     */
    public function purchasePlan($jihuaid){
        $store_ids = array_keys(self::$sale_store);

        foreach($store_ids as $store_id){
            PurchasePlan::startPlan($jihuaid,$store_id,1);

            $results = $this->purchasePlanFBA($jihuaid,$store_id);

            PurchasePlan::endPlan($store_id,$results,1);
        }
    }


    /**
     * FBA备货订单 自动创建采购计划
     * @param string $jihuaid 采购计划批次号
     * @param int $store_id 目标仓库ID
     * @return mixed
     */
    public function purchasePlanFBA($jihuaid,$store_id){
        if( !in_array($store_id,array_keys(self::$sale_plats)) ){// 验证仓库
            return false;
        }

        $totalneedamount	= 0;// 需要采购金额统计
        $totalneedsku		= 0;// 需要采购SKU统计
        $totaloossku		= 0;// 已经有欠单的SKU

        $now_sale_plat = self::$sale_plats[$store_id];

        $this->autoCompleteStockUpOrder($now_sale_plat);// 自动完结已经完成的备货单

        $stockUpDatas = $this->getStockUpOrderCountByGoods('',$now_sale_plat);// 获取备货单需求数量(未出库数量)
        $storeOutPlans = $this->getStoreOutPlanCountByGoods('','',$store_id);// 获取出库计划单占用库存
        $onSkus = $this->getOnhandleCountByGoods('',-1,$store_id);// 实际库存小于0的也需要采购

        $skus_arr1  = array_keys($stockUpDatas);
        $skus_arr2  = array_keys($storeOutPlans);
        $skus_arr3  = array_keys($onSkus);
        $skus_arr   = array_merge($skus_arr1,$skus_arr2,$skus_arr3);// 获取可能需要采购的SKU
        $skus_arr   = array_unique($skus_arr);// 去重复

        $goodsCounts = $this->getOnhandleCountByGoods($skus_arr,2,$store_id);// 获取实际库存
        $bookedCounts = $this->getIOorderCountByGoods('',$store_id);// 获取采购在途数量


        $count = count($skus_arr);
        for($i=0;$i < $count;$i ++){
            $addInfo    = array();

            $sku = $skus_arr[$i];
            if(empty($sku)) continue;

            $stockUp = isset($stockUpDatas[$sku])?$stockUpDatas[$sku]:0;// 备货未发数量（即备货需求数）
            $outPlan = isset($storeOutPlans[$sku])?$storeOutPlans[$sku]:0;// 出库计划单占用数（不在参与运算）
            $goods  = isset($goodsCounts[$sku])?$goodsCounts[$sku]:0;// 产品实际库存
            $booked = isset($bookedCounts[$sku])?$bookedCounts[$sku]:0;// 采购单预订数量
            echo $sku.':'.$stockUp.','.$goods.','.$booked.'-->'.($goods+$booked-$stockUp).'<br/>';
//            echo $stockUp-$goods+$booked;exit;

            $actual_available_count = $goods - $stockUp;// 仓库实际可用库存

            // 采购预订+实际库存-备货需求数 （不统计出库计划单）
            if( ($goods+$booked-$stockUp) < 0 ){
                $sql = "select factory,a.goods_id,a.goods_sn,a.goods_name,a.goods_cost,a.cguser,a.isuse,a.lastpandian,a.lastexport 
                from ebay_goods as a 
                where a.goods_sn='$sku' ";
                $goodsInfo		= self::$dbcon->execute($sql);
                $goodsInfo		= self::$dbcon->getResultArray($goodsInfo);
                $goodsInfo      = $goodsInfo[0];
                if(empty($goodsInfo)){
                    subOperation("caigoujhAmazonFba","产品资料未找到".$sku.':'.$sql);
                }

                $factory		= empty($goodsInfo['factory'])?'':$goodsInfo['factory'];
                $goods_id		= empty($goodsInfo['goods_id'])?0:$goodsInfo['goods_id'];
                $goods_sn		= empty($goodsInfo['goods_sn'])?'':$goodsInfo['goods_sn'];
                $goods_name		= empty($goodsInfo['goods_name'])?'':$goodsInfo['goods_name'];
                $goods_cost		= empty($goodsInfo['goods_cost'])?0:$goodsInfo['goods_cost'];
                $isuse			= empty($goodsInfo['isuse'])?0:$goodsInfo['isuse'];
                $lastpandian	= empty($goodsInfo['lastpandian'])?'':$goodsInfo['lastpandian'];
                $lastexport		= empty($goodsInfo['lastexport'])?'':$goodsInfo['lastexport'];
                $cguser			= empty($goodsInfo['cguser'])?'':$goodsInfo['cguser'];

//                $needqty = $outPlan + $stockUp - ($goods+$booked);// 需要采购数量
                $needqty = $stockUp - ($goods+$booked);// 需要采购数量（不统计出库计划单）


                $totalneedsku ++ ;	//需要采购SKU统计
                $totalneedamount = $totalneedamount + $needqty * $goods_cost;	//需要采购金额统计
                $totaloossku ++;	//已经有欠单的SKU

                $addInfo = array(
                    'jihuaid' => $jihuaid,
                    'goods_id' => $goods_id,
                    'goods_sn' => $goods_sn,
                    'goods_name' => $goods_name,
                    'factory' => $factory,
                    'zgqty' => 0,
                    'storeid' => $store_id,
                    'cguser' => $cguser,
                    'goods_cost' => $goods_cost,
                    'isuse' => $isuse,
                    'lastpandian' => $lastpandian,
                    'lastexport' => $lastexport,
                    'goods_days' => 3,
                    'ebay_paidtime' => 0,
                    'purchasedays' => 5,
                    'prepare_day' => 0,
                    'itembooked' => $booked,
                    'usedstock' => $stockUp,
                    'waitforusestore' => 0,
                    'zdqty' => 0,
                    'goods_count' => $goods,
                    'needqty' => $needqty,
                    'show_type' => '10'
                );
            }
            elseif($actual_available_count < 0){
                $sql = "select factory,a.goods_id,a.goods_sn,a.goods_name,a.goods_cost,a.cguser,a.isuse,a.lastpandian,a.lastexport 
                from ebay_goods as a 
                where a.goods_sn='$sku' ";
                $goodsInfo		= self::$dbcon->execute($sql);
                $goodsInfo		= self::$dbcon->getResultArray($goodsInfo);
                $goodsInfo      = $goodsInfo[0];

                $factory		= empty($goodsInfo['factory'])?'':$goodsInfo['factory'];
                $goods_id		= empty($goodsInfo['goods_id'])?0:$goodsInfo['goods_id'];
                $goods_sn		= empty($goodsInfo['goods_sn'])?'':$goodsInfo['goods_sn'];
                $goods_name		= empty($goodsInfo['goods_name'])?'':$goodsInfo['goods_name'];
                $goods_cost		= empty($goodsInfo['goods_cost'])?0:$goodsInfo['goods_cost'];
                $isuse			= empty($goodsInfo['isuse'])?0:$goodsInfo['isuse'];
                $lastpandian	= empty($goodsInfo['lastpandian'])?'':$goodsInfo['lastpandian'];
                $lastexport		= empty($goodsInfo['lastexport'])?'':$goodsInfo['lastexport'];
                $cguser			= empty($goodsInfo['cguser'])?'':$goodsInfo['cguser'];

                $addInfo = array(
                    'jihuaid' => $jihuaid,
                    'goods_id' => $goods_id,
                    'goods_sn' => $goods_sn,
                    'goods_name' => $goods_name,
                    'factory' => $factory,
                    'zgqty' => 0,
                    'storeid' => $store_id,
                    'cguser' => $cguser,
                    'goods_cost' => $goods_cost,
                    'isuse' => $isuse,
                    'lastpandian' => $lastpandian,
                    'lastexport' => $lastexport,
                    'goods_days' => 3,
                    'ebay_paidtime' => 0,
                    'purchasedays' => 5,
                    'prepare_day' => 0,
                    'itembooked' => $booked,
                    'usedstock' => $stockUp,
                    'waitforusestore' => 0,
                    'zdqty' => 0,
                    'goods_count' => $goods,
                    'needqty' => 0,
                    'show_type' => '20'
                );
            }

            if($addInfo){
                $res = PurchasePlan::addPurchasePlan($addInfo);
                unset($addInfo);
            }
        }

        return array(
            'totalneedamount'   => $totalneedamount,
            'totalneedsku'      => $totalneedsku,
            'totaloossku'       => $totaloossku
        );
    }


    /**
     * 采购订单转换为备货清单的需求数量（回写备货单已采购数量，已弃用）
     * @param string $io_ordersn
     */
    public function purchaseToStockUpByAddtime($io_ordersn){
        // 统计采购单的采购数量(FBA仓库、单据类型为采购单、状态为已审核)
        $io_sql = "SELECT e_iod.goods_sn,sum(e_iod.goods_count) AS num FROM ebay_iostore AS e_io 
            LEFT JOIN ebay_iostoredetail AS e_iod ON e_io.io_ordersn=e_iod.io_ordersn 
            WHERE e_io.io_ordersn='$io_ordersn' AND e_io.io_warehouse=35 AND e_io.type=2 AND e_io.io_status=1
            GROUP BY goods_sn";
        $ioCounts		= self::$dbcon->execute($io_sql);
        $ioCounts		= self::$dbcon->getResultArray($ioCounts);
        //print_r($ioCounts);echo '<br/>';exit;

        if($ioCounts){// 判断 特定记录是否存在
            $ioCounts       = get_array_column($ioCounts,'num','goods_sn');
            foreach($ioCounts as $sku => $count){
                $flag   = true;
                while( $flag AND ($count > 0) ){
                    // 找到最先创建的备货单，写入采购单编号
                    $stock_sql = "SELECT stu_o_d.id,stu_o_d.amount-stu_o_d.purchased_amount AS  notPur_amount
                        FROM stockup_order AS stu_o
                        LEFT JOIN stockup_order_details AS stu_o_d ON stu_o.id=stu_o_d.stockup_id
                        WHERE stu_o.status=20 AND (stu_o_d.io_status=20 OR stu_o_d.io_status=25) AND stu_o_d.goods_sn='$sku' 
                        ORDER BY stu_o.id ASC LIMIT 1 ";

                    //print_r($stock_sql);exit;
                    $stockUpCount		= self::$dbcon->execute($stock_sql);
                    $stockUpCount		= self::$dbcon->getResultArray($stockUpCount);
                    if(!empty($stockUpCount) AND $stockUpCount[0]['notPur_amount']){
                        $id             = $stockUpCount[0]['id'];
                        $notPur_amount  = $stockUpCount[0]['notPur_amount'];// 待分配数量
                        if($count >= $notPur_amount){// 剩余数量大于备货单待分配数量，更新备货单为已采购
                            $str = ",$io_ordersn:$notPur_amount";
                            //echo $str;echo '<br/>';
                            self::$dbcon->execute("UPDATE stockup_order_details SET io_status=30,io_ordersn=concat(io_ordersn,'$str'),purchased_amount=purchased_amount+'$notPur_amount' WHERE id='$id' LIMIT 1");
                            $count = $count - $notPur_amount;// 剩余数量
                        }else{
                            $str = ",$io_ordersn:$count";
                            //echo $str;echo '<br/>';
                            self::$dbcon->execute("UPDATE stockup_order_details SET io_status=25,io_ordersn=concat(io_ordersn,'$str'),purchased_amount=purchased_amount+'$count' WHERE id='$id' LIMIT 1");
                            $count  = 0;// 分配完毕
                            $flag   = false;
                        }
                    }else{// 找不到需要分配的采购单则终止
                        $flag = false;
                    }
                }
            }
        }
    }


    /**
     * 根据出库计划单号 统计库存需求个数，判断库存是否充足（已弃用）
     * @param string|array $ordersn  出库计划单号
     * @return string
     */
    public function checkHaveInventory($ordersn){
        if(empty($ordersn)){
            return '出库计划单号无效';
        }
        if(is_array($ordersn)){
            $ordersn = implode(',',$ordersn);
        }

        // 统计出库单出库总数量
        $out_sql = "SELECT sopog.goods_sn,sum(sopog.amount) AS num
        FROM store_out_plan_order AS sopo
        LEFT JOIN store_out_plan_order_goods AS sopog ON sopo.plan_order_sn=sopog.plan_order_sn
        LEFT JOIN ebay_goods AS e_g ON e_g.goods_sn=sopog.goods_sn
        WHERE sopo.id IN($ordersn) GROUP BY sopog.goods_sn";

        $out_sql    = self::$dbcon->execute($out_sql);
        $out_sql    = self::$dbcon->getResultArray($out_sql);
        $outAmounts = get_array_column($out_sql,'num','goods_sn');
        $outAmounts = array_change_key_case($outAmounts,CASE_UPPER);// 键名都转换为大写

        $skus_arr   = array_keys($outAmounts);
        $skus_str   = "'".implode("','",$skus_arr)."'";

        // 实际库存
        // 获取实际库存
        $sql_goodscount = "SELECT goods_sn,goods_count FROM ebay_onhandle WHERE store_id=35 AND goods_sn IN($skus_str)";
        $goodsCounts = self::$dbcon->execute($sql_goodscount);
        $goodsCounts = self::$dbcon->getResultArray($goodsCounts);
        $goodsCounts = get_array_column($goodsCounts,'goods_count','goods_sn');
        $goodsCounts = array_change_key_case($goodsCounts,CASE_UPPER);// 键名都转换为大写

        $count = count($skus_arr);

        $error_str = '';
        for($i=0;$i<$count;$i++){
            $sku = $skus_arr[$i];
            $outAmount = isset($outAmounts[$sku])?$outAmounts[$sku]:0;
            $goodCount = isset($goodsCounts[$sku])?$goodsCounts[$sku]:0;
            if($goodCount < $outAmount){
                $error_str .= "<font color='red'>SKU:$sku 库存不足，需要$outAmount 个，实际$goodCount 个</font><br/>";
            }
        }
        return $error_str;
    }


    /**
     * 自动完结备货单（备货单SKU明细都完全出库则更新为完结状态）
     * @param $sale_plat 销售平台类型
     */
    public function autoCompleteStockUpOrder($sale_plat){
        $update_sql = "UPDATE stockup_order SET status=30,remark=concat(remark,',全部发货系统自动完结') WHERE id IN(
                            SELECT id FROM (
                                SELECT s_o.id,sum( s_o_d.amount - s_o_d.has_amount ) leaveAmount
                                FROM stockup_order AS s_o
                                LEFT JOIN stockup_order_details AS s_o_d ON (s_o_d.stockup_sn = s_o.stockup_sn  AND s_o_d.io_status=20)
                                WHERE s_o.status =20 
                                GROUP BY s_o.id
                                HAVING leaveAmount<=0
                            ) AS c 
                        ) AND sale_plat='$sale_plat' ";
        self::$dbcon->execute($update_sql);

        // 自动完成备货单明细状态
        $update_detail_sql = "UPDATE stockup_order_details SET io_status=30 WHERE has_amount>=amount AND io_status!=30
                         AND stockup_sn IN(SELECT stockup_sn FROM stockup_order WHERE sale_plat='$sale_plat')";
        self::$dbcon->execute($update_detail_sql);

    }


    /**
     * 根据出库计划单编号获得其已上传的文件名
     * @param $plan_order_sn
     * @return array
     */
    public static function getStoreOutPlanLabelFiles($plan_order_sn){
        $filePath = BASE_PATH."uploadfiles/amazonfba/";
        $fileNames = array();
        if ($dh = opendir($filePath)) {
            $flag = false;
            while (($file = readdir($dh)) !== false) {
                if(substr($file,0,20) == $plan_order_sn){
                    $flag = true;
                    $file = iconv("gb2312","UTF-8", $file);
                    $fileNames['files'][] = $file;
                }
            }
            if($flag === false){
                $fileNames['code'] = '0X0001';
                $fileNames['message'] = '该出库计划单暂未上传产品标签！';
            }else{
                $fileNames['code'] = '0X0000';
            }
            closedir($dh);
        }else{
            $fileNames['code'] = '0X0001';
            $fileNames['message'] = "文件夹($filePath)不存在！";
        }
        return $fileNames;
    }

    /**
     * 根据出库计划单编号获得其已上传的发票文件名
     * @param $plan_order_sn
     * @return array
     */
    public static function getStoreOutPlanInvoiceFiles($plan_order_sn){
        $filePath = BASE_PATH."uploadfiles/amazonfba/invoice/";
        $fileNames = array();
        if ($dh = opendir($filePath)) {
            $flag = false;
            while (($file = readdir($dh)) !== false) {
                if(substr($file,0,20) == $plan_order_sn){
                    $flag = true;
                    $file = iconv("gb2312","UTF-8", $file);
                    $fileNames['files'][] = $file;
                }
            }
            if($flag === false){
                $fileNames['code'] = '0X0001';
                $fileNames['message'] = '该出库计划单暂未上传发票文件！';
            }else{
                $fileNames['code'] = '0X0000';
            }
            closedir($dh);
        }else{
            $fileNames['code'] = '0X0001';
            $fileNames['message'] = "文件夹($filePath)不存在！";
        }
        return $fileNames;
    }

    /**
     * 根据指定路径删除单个文件
     * @param $fileName
     * @param $fileType
     * @return array
     */
    public static function deleteFile($fileName,$fileType = ''){
        $filePath = BASE_PATH."uploadfiles/amazonfba/";
        if($fileType == 'INVOICE'){// 发票文件
            $filePath .= 'invoice/';
        }
        $filePath .= $fileName;
        $filePath =  iconv("UTF-8","gb2312", $filePath);// 处理中文名，防止乱码
        //var_dump(file_exists($filePath));exit;
        $result = array();
        if(file_exists($filePath)){
            if(@unlink($filePath)){
                $result['code'] = '0X0000';
            }else{
                $result['code'] = '0X0002';
                $result['message'] = '文件移除失败';
            }
        }else{
            $result['code'] = '0X0001';
            $result['message'] = '文件不存在';
        }
        return $result;
    }

    /**
     * 获得出库计划单的状态信息
     * @param $sopo_ids
     * @return array
     */
    public function getOutPlanStatus($sopo_ids){
        $sql_store = "SELECT sopo.id,sopo.plan_order_sn,sopo.status,sopo.store_id
                    FROM store_out_plan_order AS sopo
                    WHERE 1 ";
        if(is_array($sopo_ids)){
            $id_str = implode(",", $sopo_ids);
            $sql_store .= " AND sopo.id IN ($id_str) ";
        }else{
            $sql_store .= " AND sopo.id ='$sopo_ids' ";
        }
        $sql_store .= " ORDER BY sopo.id ASC ";

        $storeOutPlans = self::$dbcon->execute($sql_store);
        $storeOutPlans = self::$dbcon->getResultArray($storeOutPlans);

        return $storeOutPlans;
    }

    /**
     * 获得出库计划单的信息
     * @param $sopo_ids
     * @return array
     */
    public function getOutPlanInfo($sopo_ids){
        $sql_store = "SELECT *
                    FROM store_out_plan_order AS sopo
                    WHERE 1 ";
        if(is_array($sopo_ids)){
            $id_str = implode(",", $sopo_ids);
            $sql_store .= " AND sopo.id IN ($id_str) ";
        }else{
            $sql_store .= " AND sopo.id ='$sopo_ids' ";
        }
        $sql_store .= " ORDER BY sopo.id ASC ";

        $storeOutPlans = self::$dbcon->execute($sql_store);
        $storeOutPlans = self::$dbcon->getResultArray($storeOutPlans);

        $storeOutPlansTmp = array();
        if($storeOutPlans){// 出库计划单明细
            foreach($storeOutPlans as $value){
                $plan_id = $value['id'];
                $plan_order_sn = $value['plan_order_sn'];
                $storeOutPlansTmp[$plan_id] = $value;

                $list = self::$dbcon->query("SELECT * FROM store_out_plan_order_goods WHERE plan_order_sn='$plan_order_sn' ");
                $list = self::$dbcon->getResultArray($list);
                $storeOutPlansTmp[$plan_id]['list'] = $list;
            }
        }
        unset($storeOutPlans);

        return $storeOutPlansTmp;
    }


    /**
     * 获取 批量操作出库计划单 时的仓库和单据状态
     * @param $orderStr
     * @return array
     */
    public function checkOutPlanStatus($orderStr){

        $ids_arr = explode(',',$orderStr);
        $sopoInfoArr = $this->getOutPlanStatus($ids_arr);
        $statusArr = get_array_column($sopoInfoArr,'status');
        $statusArr = array_unique($statusArr);

        $storeArr = get_array_column($sopoInfoArr,'store_id');
        $storeArr = array_unique($storeArr);

        $ret = array('status' => $statusArr,'store_id' => $storeArr);
        return $ret;

    }

    /**
     * 出库计划单核对实际库存是否充足
     * @param string $ids_str
     * @param int $store_id
     * @return array
     */
    public function outPlanCheckInventory($ids_str = '',$store_id){
        $result = array();
        $ids_arr = explode(',',$ids_str);
        $needCount = $this->getStoreOutPlanCountByGoods($ids_arr,2,$store_id);// 根据出库计划单ID获取出库需求数量
        $onHandleCount = $this->getOnhandleCountByGoods(array_keys($needCount),2,$store_id);

        foreach($needCount as $sku => $need){
            if($need > $onHandleCount[$sku]){// 判断出库产品库存是否充足
                $result['error'][$sku] = "$sku 库存不足：需要 $need ,实际 $onHandleCount[$sku] ";
            }
        }

        if(isset($result['error'])){
            $result = array('code' => '0X0001','message' => $result['error']);
        }else{
            $result = array('code' => '0X0000','message' => '');
        }
        return $result;
    }


    /**
     * 出库计划单标记发货
     * @param string $ids_str
     * @return array
     */
    public function outPlanShipping($ids_str = ''){
        $truename = self::$username;
        if( !in_array($truename,array('otw','吴美娟','周荣辉')) ){
            $result['code'] = '0X0001';
            $result['message'][] = '您没有该操作权限！';
            return $result;
        }
        $time = time();

        $ids_arr = explode(',',$ids_str);
        $ret = $this->checkOutPlanStatus($ids_str);
        $storeArr = $ret['store_id'];

        if(count($storeArr) > 1){
            $result['code'] = '0X0001';
            $result['message'][] = "请同时操作一个仓库的出库计划单！";
            return $result;
        }
        $now_store_id = $storeArr[0];

        $statusArr = $this->getOutPlanStatus($ids_arr);
        $statusArrTmp = array();
        foreach($statusArr as $value){
            if($value['status'] != 20){
                $result['code'] = '0X0001';
                $result['message'][] = "单号:".$value['id']." 出库计划单非审核状态！请勿重复出库！";
            }
            $statusArrTmp[$value['id']] = $value;
        }

        if($result['code'] == '0X0001' || isset($result['message']) ){
            return $result;
        }

        $result = $this->outPlanCheckInventory($ids_str,$now_store_id);// 检验库存是否充足

        if($result['code'] == '0X0000'){// 库存充足
            $ids_arr = explode(',',$ids_str);
            foreach($ids_arr as $id){
                self::$dbcon->query('BEGIN');// 事务处理，保证完整性
                $sql_store = "UPDATE store_out_plan_order SET status=30,out_user='$truename',out_time=$time WHERE id='$id' LIMIT 1 ";
                if(self::$dbcon->update($sql_store)){
                    $plan_order_sn = $statusArrTmp[$id]['plan_order_sn'];
                    // 更新出库计划单明细 实际出库数量信息
                    $sql_up = "UPDATE store_out_plan_order_goods SET real_amount=amount,real_stockup_ids=stockup_ids 
                        WHERE (real_amount=0 OR real_amount IS NULL) 
                        AND (real_stockup_ids='' OR real_stockup_ids IS NULL)
                         AND  plan_order_sn='$plan_order_sn' ";
                    self::$dbcon->update($sql_up);

                    $status1 = $this->createIOStoreOrder($id);
                    $status2 = $this->updateStockUpByOutPlan($id);
                    if($status1 AND $status2){
                        self::$dbcon->query('COMMIT');
                        self::addOperationLog('SOPO',$id,$sql_store);
                    }else{
                        $result['code'] = '0X0001';
                        self::$dbcon->query('ROLLBACK');
                        if(!$status1)
                            $result['message'][] = '创建FBA出库单发生未知错误!';

                        if(!$status2)
                            $result['message'][] = '更新备货单和实际库存时发生未知错误!';
                    }

                }
            }
        }
        return $result;
    }

    /**
     * 根据出库计划单更新到备货单已发出数量
     * @param string $id
     * @param string $plan_order_sn
     * @return boolean
     */
    public function updateStockUpByOutPlan($id = '',$plan_order_sn = ''){
        $mtime = time();
        $status = true;
        if(!empty($id) OR !empty($plan_order_sn)) {
            $store_sql = "SELECT sopo.store_id,goods_sn,amount,stockup_ids,real_amount,real_stockup_ids 
            FROM store_out_plan_order AS sopo
            LEFT JOIN store_out_plan_order_goods AS sopo_g ON sopo.plan_order_sn=sopo_g.plan_order_sn
            WHERE 1 ";
            if (!empty($id)) {
                $store_sql .= " AND sopo.id='$id' ";
            } elseif (!empty($plan_order_sn)) {
                $store_sql .= " AND sopo.plan_order_sn='$plan_order_sn' ";
            }
            $store_sql = self::$dbcon->execute($store_sql);
            $store_sql = self::$dbcon->getResultArray($store_sql);

            foreach ($store_sql as $store) {
                $goods_sn           = $store['goods_sn'];
                $stockup_ids        = $store['stockup_ids'];
                $real_stockup_ids   = $store['real_stockup_ids'];
                $stockup_ids        = empty($real_stockup_ids)?$stockup_ids:$real_stockup_ids;

                $stockup_ids = $this->analysisStockUpIds($stockup_ids);
                if ($stockup_ids) {
                    foreach ($stockup_ids as $stupId => $acmount) {
                        $up_sql = "UPDATE stockup_order_details SET has_amount=has_amount+$acmount 
                               WHERE stockup_id='$stupId'  AND goods_sn='$goods_sn'  LIMIT 1 ";
                        if(!self::$dbcon->update($up_sql)){
                            $status = false;
                            break;
                        }
                    }
                }
            }
        }else{
            $status = false;
        }

        return $status;
    }

    /**
     * 出库计划单 回库操作
     * @param string $ids_str
     * @return array
     */
    public function outPlanBackToStore($ids_str = ''){
        $truename = self::$username;

        if( !in_array($truename,array('otw','吴美娟','周荣辉')) ){
            $result['code'] = '0X0001';
            $result['message'][] = '您没有该操作权限！';
            return $result;
        }

        $outPlanInfo = $this->getOutPlanInfo($ids_str);
        $outPlanInfo = $outPlanInfo[$ids_str];

        if(empty($outPlanInfo) OR $outPlanInfo['status'] != 30){
            $result['code'] = '0X0001';
            $result['message'][] = '此出库计划单不存在或非已出库状态！';
            return $result;
        }

        if($outPlanInfo['add_time'] < 1514736000){
            $result['code'] = '0X0001';
            $result['message'][] = '2018年前的出库计划单不能执行该操作！';
            return $result;
        }

        $plan_order_sn  = $outPlanInfo['plan_order_sn'];
        $store_id       = $outPlanInfo['store_id'];

        $skuDetailList  = self::$dbcon->query("SELECT * FROM store_out_plan_order_goods WHERE plan_order_sn='$plan_order_sn'");
        $skuDetailList  = self::$dbcon->getResultArray($skuDetailList);
        if(empty($skuDetailList) ){
            $result['code'] = '0X0001';
            $result['message'][] = '此出库计划单明细不存在！';
            return $result;
        }

        $ids_arr    = array($ids_str);
        $result['code'] = '0X0000';

        foreach($ids_arr as $id){
            self::$dbcon->query('BEGIN');// 事务处理，保证完整性
            $flag = true;

            // 删除 出库计划单的出库记录
            $res = self::$dbcon->execute("DELETE FROM ebay_iostore WHERE io_ordersn='$plan_order_sn' LIMIT 1");
            if(empty($res) OR $res < 0){
                $flag = false;
            }
            $res = self::$dbcon->execute("DELETE FROM ebay_iostoredetail WHERE io_ordersn='$plan_order_sn' ");
            if(empty($res) OR $res < 0){
                $flag = false;
            }

            // 更新 仓库的实际库存、更新备货单的剩余数量
            foreach($skuDetailList as $sku_val){
                $goods_sn = $sku_val['goods_sn'];
                $real_amount = (int)$sku_val['real_amount'];
                $real_stock_up_ids = $sku_val['real_stockup_ids'];

                // 库存加回去
                $update_count = "UPDATE ebay_onhandle SET goods_count=goods_count+$real_amount WHERE goods_sn='$goods_sn' AND store_id='$store_id' ";
                $res = self::$dbcon->execute($update_count);
                if(empty($res) OR $res < 0){
                    $flag = false;
                    break;
                }

                // 出库的备货单 数量加回去、状态设置成已审核
                $stock_up_ids = $this->analysisStockUpIds($real_stock_up_ids);
                if ($stock_up_ids) {
                    foreach ($stock_up_ids as $stupId => $amount) {
                        $up_sql = "UPDATE stockup_order_details SET has_amount=has_amount-$amount,io_status=20
                               WHERE stockup_id='$stupId'  AND goods_sn='$goods_sn'  LIMIT 1 ";
                        $res = self::$dbcon->update($up_sql);
                        if($res > 0){
                            $up_sql_main = "UPDATE stockup_order SET status=20 WHERE id='$stupId' LIMIT 1";
                            self::$dbcon->update($up_sql_main);
                        }else{
                            $flag = false;
                            break;
                        }
                    }
                }
            }

            // 更新出库计划单状态
            $up_store = "UPDATE store_out_plan_order SET status=20,out_user='',out_time='' 
                        WHERE plan_order_sn='$plan_order_sn' LIMIT 1 ";
            $res = self::$dbcon->update($up_store);
            if($res > 0){
                $up_store_sub = "UPDATE store_out_plan_order_goods SET real_amount='',real_stockup_ids='',real_storage_sn='' 
                        WHERE plan_order_sn='$plan_order_sn' ";
                $res = self::$dbcon->update($up_store_sub);
                if(empty($res) OR $res < 0){
                    $flag = false;
                }
            }else{
                $flag = false;
            }

            if($flag){
                self::$dbcon->query('COMMIT');
                self::addOperationLog('SOPO',$id,'出库计划单原箱回库操作成功');

                $result['message'][] = '出库计划单原箱回库操作成功！';
            }else{
                self::$dbcon->query('ROLLBACK');

                $result['code'] = '0X0001';
                $result['message'][] = '原箱回库操作失败！';
            }
        }

        return $result;
    }


    /**
     * 创建一条操作日志
     * @param $type
     * @param $order_id
     * @param $log
     */
    public static function addOperationLog($type,$order_id,$log){
        global $truename,$mctime;
        if(!isset($truename)) $truename = empty($_SESSION['truename'])?'':$_SESSION['truename'];
        if(!isset($mctime)) $mctime = time();
        $log = mysql_escape_string(trim($log));
        $inSql = "insert into stockup_orderlog (order_id,operationuser,operationtime,notes,types) 
                values('$order_id','$truename','$mctime','$log','$type')";
        self::$dbcon->execute($inSql);
    }


    /**
     * 查询操作日志
     * @param string $type 操作类型
     * @param string $order_id  单据ID
     * @return array
     */
    public function getOperationLog($type,$order_id){
        $inSql = "select * from stockup_orderlog where types='$type' and order_id='$order_id' order by id desc ";
        $list = self::$dbcon->query($inSql);
        $list = self::$dbcon->getResultArray($list);

        return empty($list)?array():$list;
    }


    /**
     * 创建出库单
     * @param $sopo_id
     * @return bool
     */
    public function createIOStoreOrder($sopo_id){
        global $truename;

        $selSql = " SELECT sopo.*,sopog.goods_sn,sopog.amount,sopog.real_amount,sopog.asin,e_g.goods_id,e_g.goods_name,e_oh.goods_count
        FROM store_out_plan_order AS sopo
        LEFT JOIN store_out_plan_order_goods AS sopog ON sopog.plan_order_sn=sopo.plan_order_sn
        LEFT JOIN ebay_goods AS e_g ON e_g.goods_sn=sopog.goods_sn
        LEFT JOIN ebay_onhandle AS e_oh ON (e_oh.goods_sn=e_g.goods_sn AND e_oh.store_id=sopo.store_id)
        WHERE sopo.id=$sopo_id ";

        $outOrder = self::$dbcon->query($selSql);
        $outOrder = self::$dbcon->getResultArray($outOrder);

        if($outOrder){
            $ordersn = $outOrder[0]['plan_order_sn'];
            $store_id = $outOrder[0]['store_id'];

            $inOrder = array();
            $inOrder['order_type'] = 48;// FBA|HBA出库
            $inOrder['order_sn'] = $ordersn;
            $inOrder['store_id'] = $store_id;
            $inOrder['io_time'] = date('Y-m-d H:i:s');
            $inOrder['operator'] = $truename;

            foreach($outOrder as $value){
                $goods_sn       = $value['goods_sn'];
                $goods_count    = $value['amount'];
                $real_amount    = $value['real_amount'];
                $goods_count    = ($real_amount>0)?$real_amount:$goods_count;
                $goodsInfo      = Goods::getGoodsInfo($goods_sn);
//                var_dump($store_id);exit;
                $storageInfo    = Storages::findSkuByStorage($goods_sn,$store_id);
                $storage_sn     = $storageInfo['storage_sn'];


                $sku_detail                 = array();
                $sku_detail['sku']          = $goods_sn;
                $sku_detail['storage_sn']   = $storage_sn;
                $sku_detail['quantity']     = abs($goods_count);// 绝对值
                if($storageInfo){
                    $sku_detail['old_quantity'] = $storageInfo['amount'];
                }
                $sku_detail['goods_cost']   = empty($goodsInfo['goods_cost']) ? 0 : $goodsInfo['goods_cost'];
                $sku_detail['total_cost']   = $sku_detail['quantity'] * $sku_detail['goods_cost'];
                $inOrder['sku_details'][]   = $sku_detail;
            }

//            print_r($inOrder);exit;

            $res = InOutOrders::createOutOrder($inOrder);
            if($res){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    /**
     * 更新出库计划单的物流单号
     * @param string $type 更新数据类型
     * @param int $sopo_id  出库计划单ID
     * @param string $value  更新后的值
     * @return array
     */
    public function upOutPlanInfo($type,$sopo_id,$value){
        $planInfo = self::getOutPlanInfo($sopo_id);
        $planInfo = $planInfo[$sopo_id];

        if(empty($sopo_id) || empty($value)) {
            $result = array('code' => '0X0001', 'msg' => '计划单ID或数据缺失');
            return $result;
        }
        if($type == 'upTrackNumber') {
            $sqlUp = "UPDATE store_out_plan_order SET track_number='$value' WHERE id='$sopo_id' LIMIT 1 ";
        }elseif($type == 'upLogistics'){
            $value      = explode('--',$value);
            $logistics_carrier = $value[0];
            $logistics  = $value[1];

            $sqlUp = "UPDATE store_out_plan_order SET logistics='$logistics',logistics_carrier='$logistics_carrier' WHERE id='$sopo_id' LIMIT 1 ";
        }else{
            $result = array('code' => '0X0001', 'msg' => '更新数据类型错误');
            return $result;
        }
        if(self::$dbcon->execute($sqlUp)){
            $result = array('code' => '0X0000','msg' => $type.'更新成功');
            self::addOperationLog($type,$sopo_id,$sqlUp);

            if($type == 'upLogistics'){// 更新到装箱单上
                include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Logic/FbaPackOrder.class.php';
                $plan_order_sn = $planInfo['plan_order_sn'];
                $old_logistics = array($planInfo['logistics_carrier'],$planInfo['logistics']);
                $new_logistics = array($logistics_carrier,$logistics);

                FbaPackOrder::updatePackingCastValue($plan_order_sn,$old_logistics,$new_logistics);
            }
        }else{
            $result = array('code' => '0X0001','msg' => $type.'更新失败');
        }

        return $result;
    }


    /**
     * 出库计划单更改实际出库数量
     * @param $sopo_id
     * @param $data
     * @return array
     */
    public function updateRealAmount($sopo_id,$data){
        if(empty($sopo_id) || empty($data)) {
            $result = array('code' => '0X0001', 'msg' => '出库计划单ID或数据缺失');
            return $result;
        }

        $outPlanInfo = $this->getOutPlanInfo($sopo_id);
        $outPlanInfo = $outPlanInfo[$sopo_id];
        if($outPlanInfo['status'] != '20'){
            $result = array('code' => '0X0001', 'msg' => '状态不对：出库计划单非已审核状态');
            return $result;
        }

        $lists      = arrayColumnsToKey($outPlanInfo['list'],'goods_sn');
        $data       = explode('&',$data);
        $updataArr  = array();
        $flag       = true;

        if($data){
            foreach ($data as $value){
                list($sku,$amount) = explode('=',$value);
                if($amount == $lists[$sku]['amount']){
                    $updataArr[$sku]['real_stockup_ids']    = $lists[$sku]['stockup_ids'];
                    $updataArr[$sku]['real_amount']         = $lists[$sku]['amount'];
                }elseif($amount <= 0 || $amount > $lists[$sku]['amount']){ // 实际出库数量不能大于计划出库数量
                    $flag = false;
                    break;
                }elseif($amount < $lists[$sku]['amount']){ // 实际出库数量 小于 计划出库数量
                    $stockup_ids = $lists[$sku]['stockup_ids'];
                    $stockup_ids = $this->analysisStockUpIds($stockup_ids);
                    asort($stockup_ids);// 根据键值 增序排序

                    // 实际出库数量分配到相应的备货单上（优选出数量小的备货单）
                    $up_stockup_ids = '';
                    $leave_amount   = $amount;
                    foreach($stockup_ids as $stockup_id => $stockup_amount){
                        if($leave_amount <= 0){
                            break;
                        }
                        if($stockup_amount >= $leave_amount){
                            $up_stockup_ids .= ','.$stockup_id.':'.$leave_amount;
                            $leave_amount = 0;
                        }else{
                            $up_stockup_ids .= ','.$stockup_id.':'.$stockup_amount;
                            $leave_amount = $leave_amount - $stockup_amount;
                        }
                    }
                    $updataArr[$sku]['real_stockup_ids']    = $up_stockup_ids;
                    $updataArr[$sku]['real_amount']         = $amount;
                }
            }
        }

        $plan_order_sn = $outPlanInfo['plan_order_sn'];
        if($flag === false){
            $result = array('code' => '0X0001', 'msg' => '请填写正确的实际出库数量（请勿大于计划数或小于等于0）');
            return $result;
        }else{
            if($updataArr){
                foreach($updataArr as $sku => $value){
                    $real_amount = $value['real_amount'];
                    $real_stockup_ids = $value['real_stockup_ids'];
                    $sql_up = "UPDATE store_out_plan_order_goods SET real_amount='$real_amount',real_stockup_ids='$real_stockup_ids' 
                        WHERE goods_sn='$sku' AND plan_order_sn='$plan_order_sn'  LIMIT 1";
                    self::$dbcon->update($sql_up);
                    self::addOperationLog('SOPO_RealAmount',$plan_order_sn,$sql_up);
                }

                $result = array('code' => '0X0000', 'msg' => '实际出库数量更新成功');
                return $result;
            }else{// 没有数据需要更新
                $result = array('code' => '0X0001', 'msg' => '无实际需要更新的数据');
                return $result;
            }
        }
    }

    /**
     * 将出库计划单明细中对应的备货单数据解析出来成  备货单：出库数  键值对
     * @param $stockup_ids
     * @return array
     */
    public function analysisStockUpIds($stockup_ids){
        $stockup_ids = trim($stockup_ids,',');
        $stockup_ids = explode(',',$stockup_ids);

        $datas = array();
        foreach($stockup_ids as $value){
            list($stp_id,$amount) = explode(':',$value);
            $datas[$stp_id] = $amount;
        }
        return $datas;
    }


    /**
     * 获得备货单的数据
     * @param $stockup_sn
     * @return mixed
     */
    public function getStockUpOrder($stockup_sn){
        $stockup_info = "SELECT * FROM  stockup_order WHERE stockup_sn='$stockup_sn' LIMIT 1";
        $stockup_info = self::$dbcon->query($stockup_info);
        $stockup_info = self::$dbcon->getResultArray($stockup_info);

        $stockup_detail = "SELECT * FROM stockup_order_details WHERE stockup_sn='$stockup_sn' ";
        $stockup_detail = self::$dbcon->query($stockup_detail);
        $stockup_detail = self::$dbcon->getResultArray($stockup_detail);

        $result = $stockup_info[0];
        $result['list'] = $stockup_detail;

        return $result;
    }


    /**
     * 改变备货单的状态
     * @param $stockup_sn
     * @param $update
     * @return bool
     */
    public function updateStockUp($stockup_sn,$update){
        $update_str = '';
        foreach ($update as $key => $value){
            $update_str .= "$key='$value',";// 拼接成更新字段
        }
        $update_str = trim($update_str,',');
        $status = $update['status'];
        if($update_str){
            $sql = "UPDATE stockup_order SET $update_str WHERE stockup_sn='$stockup_sn' LIMIT 1 ";
            self::$dbcon->execute($sql);
            self::addOperationLog('STO',$stockup_sn,$sql);
            $sqlD = "UPDATE stockup_order_details SET io_status='$status' WHERE stockup_sn='$stockup_sn' AND io_status!=-10 ";
            self::$dbcon->execute($sqlD);
            self::addOperationLog('STO',$stockup_sn,$sqlD);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 判断权限并改变备货单的状态
     * @param $stockup_sn
     * @param $new_status
     * @return array
     */
    public function changeStockUpOrderStatus($stockup_sn,$new_status){
        if(empty($stockup_sn)){
            $result = array('code' => '0X0001','msg' => '备货单号缺失！');
            return $result;
        }

        $time = time();
        $stockUpInfo = $this->getStockUpOrder($stockup_sn);
        if(!isset($stockUpInfo['id'])){
            $result = array('code' => '0X0001','msg' => '备货单号未找到！');
            return $result;
        }
        $now_status = $stockUpInfo['status'];// 备货单当前状态

        if($new_status == 20 AND $now_status == 10){// 只有新建状态才能审核
            $max_count = 0;// 获取备货单SKU最大备货数量
            foreach ($stockUpInfo['list'] as $value){
                if($value['amount'] > $max_count) $max_count = $value['amount'];
            }
            if($max_count > 200 ){
                if(in_array(self::$username,self::$sartap)){
                    $this->updateStockUp($stockup_sn,array('status' => 20,'audit_user' => self::$username,'audit_time'=> $time));
                    $result = array('code' => '0X0000' ,'msg' => '');
                }else{
                    $result = array('code' => '0X0001' ,'msg' => '审核失败（SKU数量大于200需要主管审核）');
                }
            }elseif($max_count > 100 ){
                if(in_array(self::$username,self::$groupLeader) || in_array(self::$username,self::$sartap)){  // zhuguan shenhe 
                    $this->updateStockUp($stockup_sn,array('status' => 20,'audit_user' => self::$username,'audit_time'=> $time));
                    $result = array('code' => '0X0000' ,'msg' => '');
                }else{
                    $result = array('code' => '0X0001' ,'msg' => '审核失败（SKU数量大于100需要组长以上审核）');
                }
            }else{
                $this->updateStockUp($stockup_sn,array('status' => 20,'audit_user' => self::$username,'audit_time'=> $time));
                $result = array('code' => '0X0000' ,'msg' => '');
            }
        }elseif($new_status == 10  AND $now_status == 20){// 只有审核状态才能恢复到新建状态
            if(in_array(self::$username,array_merge(self::$groupLeader,self::$sartap))){
                $this->updateStockUp($stockup_sn,array('status' => 10));
                $result = array('code' => '0X0000' ,'msg' => '');
            }else{
                $result = array('code' => '0X0001' ,'msg' => '抱歉，您无权限取消审核');
            }
        }elseif($new_status == -10  AND $now_status == 10){// 只有新建状态才能作废
            $this->updateStockUp($stockup_sn,array('status' => -10,'zuofei_user' => self::$username,'zuofei_time' => $time));
            $result = array('code' => '0X0000' ,'msg' => '作废成功');
        }else{
            $result = array('code' => '0X0001' ,'msg' => '状态不符，请勿非法操作！');
        }

        return $result;

    }

}


