<?php

/**
 * Class PurchasePlan
 * 运行采购计划
 * @user:zwl
 * @date:2018-03-06
 */

class PurchasePlan {


    /**
     * 获取 计算日均销量时，7/15/30天销量所占比重
     */
    public static function getConfigProportion(){
        global $dbcon,$user;
        $proportion		= "select * from ebay_config WHERE `ebay_user` ='$user' LIMIT 1";
        $proportion		= $dbcon->execute($proportion);
        $proportion		= $dbcon->getResultArray($proportion);

        return empty($proportion)?array():$proportion[0];
    }

    /**
     * 获取指定SKU 指定仓库的最早出售时间
     * @param $goods_sn
     * @param $store_id
     * @return array|resource|string
     */
    public static function skuTheEarliest($goods_sn,$store_id){
        global $dbcon;

        /* 检查这个产品最早售出的时间 */ //（没有对状态进行要求）
        $theEarliestTime 	= "select ebay_paidtime 
                from ebay_order as a join ebay_orderdetail as b on a.ebay_ordersn = b.ebay_ordersn
                where b.sku='$goods_sn' and a.ebay_warehouse='$store_id' and ebay_paidtime<>'' and not isnull(ebay_paidtime) 
                order by a.ebay_id asc limit 1 ";
        $theEarliestTime = $dbcon->query($theEarliestTime);
        $theEarliestTime = $dbcon->getResultArray($theEarliestTime);

        return $theEarliestTime;
    }

    /**
     * 运行采购计划（所有产品按 LIMIT查询条件划分为多任务处理）
     * @param string $jihuaid  采购计划批次ID
     * @param int $offset   查询产品偏移量
     * @param int $total_count  查询的产品总个数
     * @return array
     */
    public static function runPlan($jihuaid,$offset,$total_count){
        global $dbcon,$truename,$specialPurDaysKeys,$user;

        $mctime = time();

        // 坂田仓库采购计划开始统计
        $totalneedamount	= 0;// 需要采购金额统计
        $totalneedsku		= 0;// 需要采购SKU统计
        $totaloossku		= 0;// 已经有欠单的SKU

        $storeid            = 32;// 所有仓库(目前限制只是坂田仓库)

        $proportion         = self::getConfigProportion();// 计算日均销量时，7/15/30天销量所占比重
        $sys_mrpdays30	    = $proportion['days30'];
        $sys_mrpdays15	    = $proportion['days15'];
        $sys_mrpdays7	    = $proportion['days7'];


        $sql_sel = "select a.factory,a.goods_id,a.goods_sn,a.goods_name,a.goods_cost,a.cguser,a.salesuser,a.isuse,
                        a.lastpandian,a.lastexport,a.goods_unit,a.prepare_day 
                    from ebay_goods as a 
                    where 1 and a.goods_location not like 'U%' ";

        if(date('H') != 0) {
            $sql_sel    .= " and (a.isuse=0 or a.isuse=3 or a.isuse=5 or a.isuse=6) ";
        }

        subOperation("caigoujihua".date('Y-m'),'产品总个数：'.$total_count);
        $sql_sel    .= " order by a.goods_id asc ";
        if($truename == 'otw') echo $sql_sel.'<br><br><br>';
        subOperation("caigoujihua".date('Y-m'),$sql_sel);


        $k          = $offset;
        $pagesize   = 1000;
        for( ;$k < $total_count;$k += $pagesize){
            $goodsSalesStatistics   = array();// SKU 销量统计数据缓存列表
            $goodsStatusChange      = array();// SKU 状态变更缓存列表
            $goodsAverageDailySales = array();// SKU 的日均销量



            $goodsList  =  $sql_sel." LIMIT $k,$pagesize ";
            // echo $goodsList.'<br/>';continue;
            $goodsList	= $dbcon->execute($goodsList);
            $goodsList	= $dbcon->getResultArray($goodsList);

            $goods_sn_arr       = get_array_column($goodsList,'goods_sn');
            $goods_sn_str       = implode("','",$goods_sn_arr);

            // 批量获取SKU的实际库存
            $goodsActualCount   = "SELECT UPPER(trim(goods_sn)) AS goods_sn,goods_count FROM ebay_onhandle WHERE goods_sn IN('$goods_sn_str') AND store_id='$storeid' ";
            $goodsActualCount	= $dbcon->execute($goodsActualCount);
            $goodsActualCount	= $dbcon->getResultArray($goodsActualCount);
            $goodsActualCount   = get_array_column($goodsActualCount,'goods_count','goods_sn');
            // print_r($goodsActualCount);exit;

            // 获取 SKU 占用数、待分配库存数、预订数
            $goodsStatisticCount    = "SELECT UPPER(trim(goods_sn)) AS goods_sn,sale_need,sale_used,pur_onway FROM sku_inventory_cache WHERE goods_sn IN('$goods_sn_str')";
            $goodsStatisticCount	= $dbcon->execute($goodsStatisticCount);
            $goodsStatisticCount	= $dbcon->getResultArray($goodsStatisticCount);
            $goodsStatisticCount    = arrayColumnsToKey($goodsStatisticCount,'goods_sn');
            // print_r($goodsStatisticCount);exit;

            // SKU 7/15/30天销量
            $goodsSaleCountByDays   = "SELECT UPPER(trim(goods_sn)) AS goods_sn,sale_7,sale_15,sale_30 FROM sku_salecount7and15and30_cache WHERE goods_sn IN('$goods_sn_str') ";
            $goodsSaleCountByDays	= $dbcon->execute($goodsSaleCountByDays);
            $goodsSaleCountByDays	= $dbcon->getResultArray($goodsSaleCountByDays);
            $goodsSaleCountByDays   = arrayColumnsToKey($goodsSaleCountByDays,'goods_sn');
            // print_r($goodsSaleCountByDays);exit;

            //所有负责产品循环
            foreach($goodsList as $i => $goods_val){
                $addInfo        = array();

                $factory		= $goods_val['factory'];
                $goods_id		= $goods_val['goods_id'];
                $goods_sn		= $goods_val['goods_sn'];
                $goods_sn_upper = strtoupper(trim($goods_sn));
                $goods_name		= $goods_val['goods_name'];
                $goods_cost		= $goods_val['goods_cost'];
                $isuse			= $goods_val['isuse'];
                $lastpandian	= $goods_val['lastpandian'];
                $lastexport		= $goods_val['lastexport'];
                $cguser			= $goods_val['cguser'];
                $salesuser		= $goods_val['salesuser'];
                $prepare_day    = empty($goods_val['prepare_day'])?0:$goods_val['prepare_day'];// SKU备货天数
                $goods_count    = isset($goodsActualCount[$goods_sn_upper])?$goodsActualCount[$goods_sn_upper]:0;// SKU实际库存


                /* 检查这个产品最早售出的时间 */ //（没有对状态进行要求）
                $earliestOrder = self::skuTheEarliest($goods_sn,$storeid);

                if(count($earliestOrder) > 0){
                    if($isuse == 5 || $isuse == 6){ // 修改新开发、待上传产品为在线状态
                        $goodsStatusChange[$goods_sn] = array('old_isuse' => $isuse,'new_isuse' => 0);
                    }

                    /* 如果days 是小于或等于30的话，统一按/每天的销量 */
                    $ebay_paidtime		= $earliestOrder[0]['ebay_paidtime'];
                    $time3              = $mctime - $ebay_paidtime;
                    $day                = floor($time3/(3600*24));

                    // 销量
                    $qty7 	= isset($goodsSaleCountByDays[$goods_sn_upper])?$goodsSaleCountByDays[$goods_sn_upper]['sale_7']:0;
                    $qty15 	= isset($goodsSaleCountByDays[$goods_sn_upper])?$goodsSaleCountByDays[$goods_sn_upper]['sale_15']:0;
                    $qty30 	= isset($goodsSaleCountByDays[$goods_sn_upper])?$goodsSaleCountByDays[$goods_sn_upper]['sale_30']:0;


                    $goodsSalesStatistics[$goods_sn] = array('store_id' => $storeid,'qty7' => $qty7,'qty15' => $qty15,'qty30' => $qty30);// 更新SKU的销量数据


                    if($day < 30 ){// 如果最早售出时间小于30 的，按取得小于30 天内的总销量，在除以指定的天数
                        if($day < 7) $day = 7;// 不足7天按7天计算（通常发生在新品）byzhuwf20150328
                        $start1						= date('Y-m-d').'23:59:59';
                        $start0						= date('Y-m-d',strtotime("$start1 -$day days")).' 00:00:00';
                        $qty0						= getProductsqty($start0,$start1,$goods_sn,$storeid)/$day;		//日均售出数量
                    }else{// 最早销售日大于30天
                        $qty7_2			    = ($qty7/7)   * $sys_mrpdays7;// 7天销售数量
                        $qty15_2			= ($qty15/15) * $sys_mrpdays15;// 15天销售数量
                        $qty30_2			= ($qty30/30) * $sys_mrpdays30;// 30天销售数量
                        $qty0				= $qty7_2 + $qty15_2 + $qty30_2;// 根据系统设定的7、15、30天销量所占比例计算平均每天的销量
                    }

                    $usedstock			= isset($goodsStatisticCount[$goods_sn_upper])?$goodsStatisticCount[$goods_sn_upper]['sale_used']:0;// 占用库存
                    $waitforusestore	= isset($goodsStatisticCount[$goods_sn_upper])?$goodsStatisticCount[$goods_sn_upper]['sale_need']:0;// 等待分配的订单产品数
                    $itembooked			= isset($goodsStatisticCount[$goods_sn_upper])?$goodsStatisticCount[$goods_sn_upper]['pur_onway']:0;// 取得已经预订的产品数量

                    list($goods_days,$purchasedays) = getGoodsAndPurDays($day,$qty0);
                    $specialInfo = null;
                    if(trim($salesuser) == '束从华' AND $storeid == 32 ){// 限定产品开发员和仓库
                        $specialInfo = specialKeysFlag($specialPurDaysKeys,$goods_val);// 判断是否含有关键字
                    }

                    if(isset($specialInfo) AND $specialInfo['flag'] === true){
                        $beishu             = 2.5;
                        $prepare_day_count  =  $qty0 * $prepare_day;// 加长备货天数的需求数量
                        $zdqty 			= $qty7 * $beishu + $prepare_day_count;// 最低库存（2倍周销量）
                        $zgqty 			= $zdqty + $qty7 * $beishu + $prepare_day_count; // 最高库存
                        $goods_days 	= $specialInfo['goodDay'];
                        $purchasedays 	= $specialInfo['purDay'];
                    }else{
                        $goods_days     += $prepare_day;
                        $purchasedays   += $prepare_day;

                        $zdqty 	        = ceil($qty0 * $goods_days); // 最低库存
                        $zgqty	        = ceil($qty0 * $purchasedays);
                    }

                    //处理报警天数（最低库存）及日均销量\处理采购天数（最高库存）
                    $goodsAverageDailySales[$goods_sn] = array(
                        'dailysold'     => sprintf('%.3f',$qty0),
                        'goods_days'    => $goods_days,
                        'purchasedays'  => $purchasedays,
                        'storeid'       => $storeid
                    );


                    //  如果实际可用库存,小于预警库存时生成采购订单
                    $available_count        = $goods_count + $itembooked - $usedstock - $waitforusestore;// 可用库存（包含采购在途）
                    $actual_available_count = $goods_count - $usedstock - $waitforusestore;// 实际可用库存（不包含采购在途）

                    if( $available_count < $zdqty){

                        if(isset($specialInfo) AND $specialInfo['flag'] === true){
                            $needqty		= $zdqty; // 计划数
                        }else{
                            $needqty		= $zgqty - $available_count;	//计划数
                        }
                        if($needqty <= 0) continue;// 有一些需求数为0的跑出来，还未找原因

                        $totalneedsku ++ ;	//需要采购SKU统计
                        if($goods_count + $itembooked - $usedstock - $waitforusestore < 0) $totaloossku ++;	//已经有欠单的SKU
                        $totalneedamount = $totalneedamount + $needqty * $goods_cost;	//需要采购金额统计

                        $addInfo = array(
                            'jihuaid' => $jihuaid,
                            'goods_id' => $goods_id,
                            'goods_sn' => $goods_sn,
                            'goods_name' => $goods_name,
                            'factory' => $factory,
                            'zgqty' => $zgqty,
                            'storeid' => $storeid,
                            'cguser' => $cguser,
                            'goods_cost' => $goods_cost,
                            'isuse' => $isuse,
                            'lastpandian' => $lastpandian,
                            'lastexport' => $lastexport,
                            'goods_days' => $goods_days,
                            'ebay_paidtime' => $ebay_paidtime,
                            'purchasedays' => $purchasedays,
                            'prepare_day' => $prepare_day,
                            'qty0' => $qty0,
                            'itembooked' => $itembooked,
                            'usedstock' => $usedstock,
                            'waitforusestore' => $waitforusestore,
                            'zdqty' => $zdqty,
                            'goods_count' => $goods_count,
                            'needqty' => $needqty,
                            'show_type' => '10'
                        );
                    }
                    elseif($actual_available_count < 0){// 仓库实际缺货的产品
                        $addInfo = array(
                            'jihuaid' => $jihuaid,
                            'goods_id' => $goods_id,
                            'goods_sn' => $goods_sn,
                            'goods_name' => $goods_name,
                            'factory' => $factory,
                            'zgqty' => $zgqty,
                            'storeid' => $storeid,
                            'cguser' => $cguser,
                            'goods_cost' => $goods_cost,
                            'isuse' => $isuse,
                            'lastpandian' => $lastpandian,
                            'lastexport' => $lastexport,
                            'goods_days' => $goods_days,
                            'ebay_paidtime' => $ebay_paidtime,
                            'purchasedays' => $purchasedays,
                            'prepare_day' => $prepare_day,
                            'qty0' => $qty0,
                            'itembooked' => $itembooked,
                            'usedstock' => $usedstock,
                            'waitforusestore' => $waitforusestore,
                            'zdqty' => $zdqty,
                            'goods_count' => $goods_count,
                            'needqty' => 0,
                            'show_type' => '20'
                        );
                    }

                    if($addInfo){
                        $res = PurchasePlan::addPurchasePlan($addInfo);
                        unset($addInfo);
                    }
                }
            }

            self::updateGoodsStatus($goodsStatusChange);// 修改状态

            self::updateGoodsSalesStatistics($goodsSalesStatistics);// 更新SKU的销量数据

            self::updateGoodsAverageDailySales($goodsAverageDailySales);// 更新SKU的日均销量

        }


        return array(
            'totalneedamount'   => $totalneedamount,
            'totalneedsku'      => $totalneedsku,
            'totaloossku'       => $totaloossku
        );

    }

    /**
     * 保存一个采购计划的记录（完善数据）
     * @param $addInfo
     * @return bool
     */
    public static function addPurchasePlan($addInfo){
        $jihuaid    = $addInfo['jihuaid'];
        $goods_sn   = $addInfo['goods_sn'];
        $storeid    = $addInfo['storeid'];

        if(empty($addInfo) OR empty($jihuaid) OR empty($goods_sn) OR empty($storeid)){
            return false;
        }

        if(is_numeric($addInfo['factory'])){// 供应商ID转成供应商名称
            $partnerInfo = Partner::getPartner($addInfo['factory']);
            $addInfo['factory'] = $partnerInfo['company_name'];
        }

        if(!isset($addInfo['ali_supplier_name'])){
            $aliSupplierInfo                = self::getAli1688Supplier($goods_sn);// 查找最优1688供应商
            $addInfo['ali_supplier_name']   = isset($aliSupplierInfo['company_name'])?trim($aliSupplierInfo['company_name']):'';
        }

        if(!isset($addInfo['stockoutday'])){
            $stockoutday            = (int)fnGetSKUOosDays($goods_sn);// 缺货天数
            $addInfo['stockoutday'] = $stockoutday;
        }

        $res_insert_id = DB::Add('ebay_cgjh',$addInfo);

        if(empty($res_insert_id) OR $res_insert_id < 0){
            subOperation("caigoujihua".date('Y-m'),'计划插入失败：'.json_encode($addInfo));
        }


        if($addInfo['show_type'] == '10' AND strpos($jihuaid,' 00')){// 放到 0 点执行
            $purPlanOrderDeal = new PurchaseOrderDeal();

            $result         = $purPlanOrderDeal->getPurPlanOrderType($goods_sn);// 获得SKU最近的发货渠道和订单类型
            $l_o_type       = $result['l_o_type'];
            $egersis_color  = $result['egersis_color'];
            $l_o_type       = empty($l_o_type)?$egersis_color:$l_o_type;

            $uptqty = "update ebay_goods set logistics_ordertype_type='$l_o_type' where goods_sn='$goods_sn' limit 1";
            DB::QuerySQL($uptqty);
            $upcolor = "update ebay_cgjh set egersis_color='$egersis_color' where jihuaid='$jihuaid' and goods_sn='$goods_sn' and storeid='$storeid' limit 1 ";
            DB::QuerySQL($upcolor);
        }

        return $res_insert_id;
    }


    /**
     * 更新SKU的状态
     * @param $goodsStatusChange
     */
    public static function updateGoodsStatus($goodsStatusChange){
        global $dbcon;

        // 修改状态
        foreach($goodsStatusChange as $goods_sn => $isuse_val){
            $modskustatus = "update ebay_goods set isuse=0 where goods_sn='$goods_sn'";
            $res = $dbcon->execute($modskustatus);
            $msg = "修改产品状态从[{$isuse_val['old_isuse']}]到[{$isuse_val['new_isuse']}]";
            subOperation("caigoujhmodskustatus","caigoujh--->".$msg);
            if(empty($res)){
                subOperation("caigoujihua".date('Y-m'),'修改产品状态失败：'.$modskustatus);
            }
        }

    }

    /**
     * 更新SKU的 7/15/30的销量数据
     * @param $goodsSalesStatistics
     */
    public static function updateGoodsSalesStatistics($goodsSalesStatistics){
        global $dbcon;

        // 更新SKU的销量数据
        foreach($goodsSalesStatistics as $goods_sn => $sale_val){
            $store_id   = (int)$sale_val['store_id'];
            $qty7   = (int)$sale_val['qty7'];
            $qty15  = (int)$sale_val['qty15'];
            $qty30  = (int)$sale_val['qty30'];

            if($store_id == 32 OR $store_id == 37){
                $uptqty = "update ebay_goods set qty7=$qty7,qty15=$qty15,qty30=$qty30 where goods_sn='$goods_sn' limit 1";
                $res = $dbcon->execute($uptqty);
                if(empty($res)){
                    subOperation("caigoujihua".date('Y-m'),'更新7/15/30销量失败：'.$uptqty);
                }
            }
            $res = DB::Find('ebay_onhandle',"goods_sn='$goods_sn' AND store_id='$store_id' ");
            if(empty($res)){// 没有库存记录则插入
                $condition = array();
                $condition['goods_sn'] = $goods_sn;
                $condition['store_id'] = $store_id;
                Goods::addSkuToOnhandle($condition);
            }

            // 更新分仓销量的数据
            $uptqty1 = "update ebay_onhandle set qty7=$qty7,qty15=$qty15,qty30=$qty30 where goods_sn='$goods_sn' and store_id='$store_id' limit 1";
            $dbcon->execute($uptqty1);

        }
    }

    /**
     * 更新SKU的日均销量、采购天数、预警天数
     * @param $goodsAverageDailySales
     */
    public static function updateGoodsAverageDailySales($goodsAverageDailySales){
        global $dbcon;

        // 更新SKU的日均销量
        foreach($goodsAverageDailySales as $goods_sn => $daily_val){
            $qty0           = $daily_val['dailysold'];
            $goods_days     = (int)$daily_val['goods_days'];
            $purchasedays   = (int)$daily_val['purchasedays'];
            $storeid        = (int)$daily_val['storeid'];

            $res = DB::Find('ebay_onhandle',"goods_sn='$goods_sn' AND store_id='$storeid' ");
            if(empty($res)){// 没有库存记录则插入
                $condition = array();
                $condition['goods_sn'] = $goods_sn;
                $condition['store_id'] = $storeid;
                Goods::addSkuToOnhandle($condition);
            }

            $uptpdays = "update ebay_onhandle set dailysold=$qty0,goods_days=$goods_days,purchasedays=$purchasedays where goods_sn='$goods_sn' and store_id='$storeid' ";
            $res = $dbcon->execute($uptpdays);
            if(empty($res)){
                subOperation("caigoujihua".date('Y-m'),'更新日均销量失败：'.$uptpdays);
            }
        }
    }


    /**
     * 标记一个采购计划运算任务  开始运行
     * @param $jihuaid
     * @param $store_id
     * @param int $progress_number
     */
    public static function startPlan($jihuaid,$store_id,$progress_number = 1){
        $where = "plan_type='采购计划' and store_id='$store_id' ";
        if($progress_number){
            $where .= " and progress_number='$progress_number' ";
        }

        $update = array(
            'jihuaid' => $jihuaid,
            'latest_run_time' => date('Y-m-d H:i:s'),
            'start_time' => date('Y-m-d H:i:s'),
            'content' => '',
            'status' => 1
        );

        DB::Update('ebay_cgjh_results',$update,$where);
    }

    /**
     * 标记一个采购计划运算任务  运行完成
     * @param $store_id
     * @param $content
     * @param int $progress_number
     */
    public static function endPlan($store_id,$content,$progress_number = 1){
        $where = "plan_type='采购计划' and store_id='$store_id' ";
        if($progress_number){
            $where .= " and progress_number='$progress_number' ";
        }
        $plan = DB::Find('ebay_cgjh_results',$where);
        $start_time = $plan['start_time'];

        if(!is_string($content)) $content = json_encode($content);

        $update = array(
            'end_time' => date('Y-m-d H:i:s'),
            'use_time' => sprintf('%.2f',(time() - strtotime($start_time)) / 60),
            'content' => $content,
            'status' => 0
        );

        DB::Update('ebay_cgjh_results',$update,$where);

        self::updatePurchasePlanStatisticsData();// 更新采购计划运行结果数据（数据汇总）
    }

    /**
     * 更新采购计划运行结果数据（数据汇总）
     */
    public static function updatePurchasePlanStatisticsData(){
        $list = DB::Select('ebay_cgjh_results',"plan_type='采购计划'",'jihuaid,status,content');

        $jihuaid_arr    = get_array_column($list,'jihuaid');
        $status_arr     = get_array_column($list,'status');
        $jihuaid_arr    = array_unique($jihuaid_arr);
        $status_arr     = array_unique($status_arr);


        if(count($jihuaid_arr) == 1 AND (count($status_arr) == 1 AND $status_arr[0] == 0 ) ){// 所有任务是同一批次且都运行完成

            $all_totalneedamount = $all_totalneedsku = $all_totaloossku = 0;
            foreach($list as $list_val){
                $statisticsData = $list_val['content'];
                $statisticsData = json_decode($statisticsData,true);

                $all_totalneedamount    += isset($statisticsData['totalneedamount'])?$statisticsData['totalneedamount']:0;
                $all_totalneedsku       += isset($statisticsData['totalneedsku'])?$statisticsData['totalneedsku']:0;
                $all_totaloossku        += isset($statisticsData['totaloossku'])?$statisticsData['totaloossku']:0;
            }

            $notes = "库存预警SKU数：$all_totalneedsku ，库存缺货SKU数：<font color=red> $all_totaloossku </font>，统计时间：".date('Y-m-d H:i:s');


            $update = array(
                'caigoujhdata'      => $notes,
                'caigoujhskuamount' => $all_totalneedamount
            );

            DB::Update('ebay_config',$update,'1');

        }

    }
    

    /**
     * 根据产品编码 找到已关联的最优的1688供应商
     * @param $goods_sn SKU编码
     * @return bool|array 供应商信息
     */
    public static function getAli1688Supplier($goods_sn){
        global $dbcon,$truename;

        $sqlSel = "SELECT p_s.goods_sn,p.product_id,p_s.spec_id,p_s.sku_id,p.supplier_user_id,p.supplier_login_id,s.member_id,s.company_name
            FROM ali1688_products_skuinfos AS p_s
            LEFT JOIN ali1688_products AS p ON p.product_id=p_s.product_id
            LEFT JOIN ali1688_supplier AS s ON p.supplier_user_id=s.user_id
            WHERE p_s.goods_sn='$goods_sn' ";

        $supplierList = $dbcon->query($sqlSel);
        $supplierList = $dbcon->getResultArray($supplierList);

        if(empty($supplierList)) return false;
        return $supplierList[0];
    }

}

