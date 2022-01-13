<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Help/DB.class.php';


include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'IOBase.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'HhStock.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Storages.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'StockLog.class.php';

/**
 * 产品出(入)库单据业务操作类
 * Class InOutOrders
 */
class InOutOrders extends IOBase
{
    protected static $_table_main   = 'ioorders';
    protected static $_table_detail = 'ioorders_detail';

    // 按照订单ID的区间 映射存储数据表名
    protected static $map_table = array(
        ''   => array(       1,43000000),
        '_1' => array(43000001,46000000),
        '_2' => array(46000001,49000000),
        '_3' => array(49000001,52000000),
        '_4' => array(52000001,55000000),
        '_5' => array(55000001,58000000),
        '_6' => array(58000001,61000000),

    );

    /**
     * 单据存放表映射关系 （根据单据编号 判断出所属哪个表中）
     * @param $order_sn
     */
    public static function setTable($order_sn){
        $suffix = '';

        if(is_numeric($order_sn)){// 是不是数字或数字的字符串
            foreach (self::$map_table as $key_name => $val){
                $min = $val[0];
                $max = $val[1];
                if($order_sn >= $min AND $order_sn <= $max){
                    $suffix = $key_name;
                    break;
                }
            }
        }else{// 非数字
            $suffix = '';
        }

        self::$_table_main      = self::$_table_main.$suffix;// 表名添加后缀
        self::$_table_detail    = self::$_table_detail.$suffix;// 表名添加后缀

    }

    /**
     * 获取表名
     * @return array
     */
    public static function getTable(){
        return array(self::$_table_main,self::$_table_detail);

    }

    /**
     * 查询 出入库类型信息
     * @param string $type  类型(IN.入库,OUT.出库,空.所有)
     * @param int $id  查找指定类型ID
     * @return array|type
     */
    public static function getIOOrderType($type = '',$id = 0){
        $where = ' 1 ';
        if($type == 'IN') $where .= ' AND ebay_storetype=0 ';
        if($type == 'OUT') $where .= ' AND ebay_storetype=1 ';
        if($id) $where .= " AND id='$id' ";

        $list = DB::Select('ebay_storetype',$where);
        if($list){
            $listTmp = array();
            foreach ($list as $value){
                $listTmp[$value['id']] = $value;
            }
            $list = $listTmp;
            unset($listTmp);
        }
        return $list;
    }

    /**
     * 创建入库单（自动增加库存数）
     * $condition = array(
     *      'order_type' => '入库单据类型(string)',
     *      'order_sn' => '入库单据编号(string|int)'
     *      'store_id' => '入库仓库ID（int）',
     *      'io_time' => '入库时间(string 日期时间类型，如 2018-01-01 00:00:00)',
     *      'operator' => '入库操作人',
     *      'remark' => '备注',
     *      'sku_details' = array(
     *          array(
     *              'sku' => 'SKU1',
     *              'quantity' => '入库数量',
     *              'storage_sn' => '入库仓位',
     *              'old_quantity' => '入库前该仓位数量（可不填）',
     *              'goods_cost' => '产品成本(float|double，可不填)'
     *          ),
     *          array(
     *              'sku' => 'SKU2',
     *              'quantity' => '入库数量',
     *              'storage_sn' => '入库仓位',
     *              'old_quantity' => '入库前该仓位数量（可不填）',
     *              'goods_cost' => '产品成本（可不填）'
     *          )
     *      )
     *  )
     * @param $condition
     * @return bool|int
     */
    public static function createInOrder($condition){
        self::init();

        $detail = $condition['sku_details'];
        if(empty($detail)){
            self::$error = '入库单明细缺失';
            StockLog::addOperationLog(21,$condition['order_sn'],json_encode($condition)."[ERROR:".self::$error."]");
            return false;
        }

        try{
            $order = array();
            $order['io_type']       = 0;// 入库单
            $order['order_type']    = $condition['order_type'];
            $order['order_sn']  = $condition['order_sn'];
            $order['store_id']  = $condition['store_id'];
            $order['io_time']   = empty($condition['io_time'])?date('Y-m-d H:i:s'):$condition['io_time'];
            $order['operator']  = empty($condition['operator'])?self::$username:$condition['operator'];
            $order['addtime']   = date('Y-m-d H:i:s');
            $order['remark']    = isset($condition['remark'])?$condition['remark']:'';
            $order['last_order_sn'] = isset($condition['last_order_sn'])?$condition['last_order_sn']:'';
            $order['last_order_type'] = isset($condition['last_order_type'])?$condition['last_order_type']:'';

            $result = DB::Add(self::$_table_main,$order);
            if($result){
                $ioorder_id = mysql_insert_id();

                $orderDetail = array();
                foreach($detail as $value){
                    $nowDetailTmp = array();
                    $nowDetailTmp['ioorder_id'] = $ioorder_id;
                    $nowDetailTmp['sku']    = $value['sku'];
                    $nowDetailTmp['quantity'] = $value['quantity'];
                    $nowDetailTmp['storage_sn'] = $value['storage_sn'];

                    if(isset($value['old_quantity'])){// 未设置时自动获取
                        $old_quantity = $value['old_quantity'];
                    }else{
                        $old_quantity = self::getOldAmountByStorageSn($condition['store_id'],$value['sku'],$value['storage_sn']);
                    }
                    $nowDetailTmp['old_quantity'] = $old_quantity;
                    $nowDetailTmp['goods_cost'] = isset($value['goods_cost'])?$value['goods_cost']:0;
                    $nowDetailTmp['total_cost'] = $value['quantity']*$value['goods_cost'];
                    $nowDetailTmp['last_sku_link'] = isset($value['last_sku_link'])?$value['last_sku_link']:'';

                    // 增加库存
                    $incResult = HhStock::warehouseEntry($condition['store_id'], $value['sku'],$value['quantity'],$value['storage_sn']);
                    if(empty($value['storage_sn']) AND $incResult){
                        // 没有仓位且增加库存成功时设置出库仓位
                        $nowDetailTmp['storage_sn'] = HhStock::getSuccess();
                    }
                    //$orderDetail[] = $nowDetailTmp;

                    $result_d = DB::Add(self::$_table_detail,$nowDetailTmp);
                    if(!$result_d){
                        self::$error = '入库单明细插入失败';
                        StockLog::addOperationLog(21,$order['order_sn'],$nowDetailTmp);
                    }else{
                        if($condition['order_type'] == 43 OR $condition['order_type'] == 46){// 43.盘点入库 46.盘点出库
                            include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Goods.class.php';
                            Goods::updateGoodsInfoPandianTime($value['sku']);// 更新产品资料的盘点时间
                        }elseif($condition['order_type'] == 41){
                            self::updateGoodsInfoOutTime($value['sku'],0);// 最后入库时间
                        }
                    }
                }
                return $ioorder_id;
            }else{
                self::$error = '入库单插入失败';
                StockLog::addOperationLog(21,$condition['order_sn'],json_encode($condition)."[ERROR:".self::$error."]");
                return false;
            }

        }catch (Exception $e){
            StockLog::addOperationLog(21,$order['order_sn'],$condition);
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 创建出库单（自动扣减系统库存数）
     * $condition = array(
     *      'order_type' => '出库单据类型(string)',
     *      'order_sn' => '出库单据编号(string|int)'
     *      'store_id' => '出库仓库ID(int)',
     *      'io_time' => '出库时间(string 日期时间类型，如 2018-01-01 00:00:00)',
     *      'operator' => '出库操作人',
     *      'remark' => '备注',
     *      'sku_details' = array(
     *          array( 'sku' => 'SKU1',
     *              'quantity' => '出库数量',
     *              'storage_sn' => '出库仓位',
     *              'old_quantity' => '出库前该仓位数量（可不填）',
     *              'goods_cost' => '产品成本（可不填）'
     *           ),
     *          array( 'sku' => 'SKU2',
     *              'quantity' => '出库数量',
     *              'storage_sn' => '出库仓位',
     *              'old_quantity' => '出库前该仓位数量（可不填）',
     *              'goods_cost' => '产品成本（可不填）'
     *          ),
     *
     *      )
     *  )
     * @param $condition
     * @return bool|int
     */
    public static function createOutOrder($condition){
        self::init();
        $detail = $condition['sku_details'];
        if(empty($detail)){
            self::$error = '出库单明细缺失';
            StockLog::addOperationLog(22,$condition['order_sn'],json_encode($condition)."[ERROR:".self::$error."]");
            return false;
        }


        try{
            $order = array();
            $order['io_type']       = 1;// 出库单
            $order['order_type']    = $condition['order_type'];
            $order['order_sn']  = $condition['order_sn'];
            $order['store_id']  = $condition['store_id'];
            $order['io_time']   = empty($condition['io_time'])?date('Y-m-d H:i:s'):$condition['io_time'];
            $order['operator']  = empty($condition['operator'])?self::$username:$condition['operator'];
            $order['addtime']   = date('Y-m-d H:i:s');
            $order['remark']    = isset($condition['remark'])?$condition['remark']:'';
            $order['last_order_sn'] = isset($condition['last_order_sn'])?$condition['last_order_sn']:'';
            $order['last_order_type'] = isset($condition['last_order_type'])?$condition['last_order_type']:'';

            $result = DB::Add(self::$_table_main,$order);
            if($result){
                $ioorder_id = mysql_insert_id();

                $orderDetail = array();
                foreach($detail as $value){
                    $nowDetailTmp = array();
                    $nowDetailTmp['ioorder_id'] = $ioorder_id;
                    $nowDetailTmp['sku']    = $value['sku'];
                    $nowDetailTmp['quantity'] = $value['quantity'];
                    $nowDetailTmp['storage_sn'] = $value['storage_sn'];
                    if(isset($value['old_quantity'])){// 未设置时自动获取
                        $old_quantity = $value['old_quantity'];
                    }else{
                        $old_quantity = self::getOldAmountByStorageSn($condition['store_id'],$value['sku'],$value['storage_sn']);
                    }
                    $nowDetailTmp['old_quantity'] = $old_quantity;
                    $nowDetailTmp['goods_cost'] = isset($value['goods_cost'])?$value['goods_cost']:0;
                    $nowDetailTmp['total_cost'] = $value['quantity']*$value['goods_cost'];
                    $nowDetailTmp['last_sku_link'] = isset($value['last_sku_link'])?$value['last_sku_link']:'';

                    // 扣库存
                    $decResult = HhStock::warehouseOut($condition['store_id'], $value['sku'],$value['quantity'],$value['storage_sn']);
                    if(empty($value['storage_sn']) AND $decResult){
                        // 没有仓位且扣库存成功时设置出库仓位
                        $nowDetailTmp['storage_sn'] = HhStock::getSuccess();
                    }
                    //$orderDetail[] = $nowDetailTmp;
                    $result_d = DB::Add(self::$_table_detail,$nowDetailTmp);
                    if(!$result_d){
                        self::$error = '出库单明细插入失败';
                        StockLog::addOperationLog(22,$order['order_sn'],$nowDetailTmp);
                    }else{
                        if($condition['order_type'] == 43 OR $condition['order_type'] == 46){// 43.盘点入库 46.盘点出库
                            include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Goods.class.php';
                            Goods::updateGoodsInfoPandianTime($value['sku']);// 更新产品资料的盘点时间
                        }elseif($condition['order_type'] == 44){
                            self::updateGoodsInfoOutTime($value['sku'],1);// 最后出库时间
                        }
                    }
                }
                return $ioorder_id;
            }else{
                self::$error = '出库单插入失败';
                StockLog::addOperationLog(22,$condition['order_sn'],json_encode($condition)."[ERROR:".self::$error."]");
                return false;
            }

        }catch (Exception $e){
            StockLog::addOperationLog(22,$order['order_sn'],$condition);
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 更新产品资料的最后出库时间
     * @param $sku
     * @param $type 0 入库,1出库
     */
    public static function updateGoodsInfoOutTime($sku,$type = 0){
        if($sku){
            if($type == 0){// 更新入库时间
                $update_data = array('lastruku' => time());
            }else{// 更新出库时间
                $update_data = array('lastexport' => time());
            }
            DB::Update('ebay_goods',$update_data,"goods_sn='{$sku}'");
        }
    }

    /**
     * 判断与获取出库仓位当前的实际数量
     * @param $store_id
     * @param $sku
     * @param $storage_sn
     * @return mixed
     */
    public static function getOldAmountByStorageSn($store_id,$sku,$storage_sn){
        if(empty($store_id) OR empty($sku) OR empty($storage_sn)){
            self::$error = '仓库|SKU|仓位缺失';
            return 0;
        }
        $skuStorageInfo = Storages::findSkuByStorage($sku,$store_id,$storage_sn);
        $old_quantity = isset($skuStorageInfo['amount'])?$skuStorageInfo['amount']:0;

        return $old_quantity;
    }


    /**
     * 删除出库记录（自动扣减系统库存数）
     * @param string  $io_order_id  出库单编号
     * @param string $order_sn  出库单源单据编号
     * @return bool
     */
    public static function deleteInOutOrder($io_order_id='',$order_sn=''){
        if(empty($io_order_id) AND empty($order_sn)){
            self::$error = '参数缺失';
            return false;
        }
        $IOorder = self::getInOutOrder($io_order_id,$order_sn,true);
        if( $IOorder === false){
            self::$error = '出库单记录未找到';
            return false;
        }
        $io_order_id = $IOorder['id'];
        $io_type = $IOorder['io_type'];

        $lists = $IOorder['lists'];
        foreach($lists as $list_val){
            if($io_type == 1){// 出库单 库存加回来
                HhStock::warehouseEntry($IOorder['store_id'],$list_val['sku'],$list_val['quantity'],$list_val['storage_sn']);
            }else{// 入库单  库存减下去
                HhStock::warehouseOut($IOorder['store_id'],$list_val['sku'],$list_val['quantity'],$list_val['storage_sn']);
            }
        }

        DB::Delete(self::$_table_main,"id='$io_order_id'");
        DB::Delete(self::$_table_detail,"ioorder_id='$io_order_id'");

        StockLog::addOperationLog(24,$io_order_id,'删除出入库单及明细'.$io_order_id.'[源:'.$order_sn.']:'.json_encode($IOorder));
        return true;

    }


    /**
     * 查找一个出库单记录
     * @param string $io_order_id  出库单编号
     * @param string $order_sn      出库单源单据编号
     * @param bool $have_detail     是否附加明细
     * @return bool|array
     */
    public static function getInOutOrder($io_order_id='',$order_sn='',$have_detail = true){
        if(empty($io_order_id) AND empty($order_sn)){
            self::$error = '参数缺失';
            return false;
        }

        $where = '1';
        if($io_order_id) $where .= "  AND id='$io_order_id' ";
        if($order_sn) $where .= " AND order_sn='$order_sn' ";

        $IOOrder = DB::Find(self::$_table_main,$where);
        if($IOOrder AND $have_detail){
            $io_order_id = $IOOrder['id'];
            $detail = DB::Select(self::$_table_detail,"ioorder_id='$io_order_id'");
            $IOOrder['lists'] = $detail;
        }
//        print_r($IOorder);exit;

        if(empty($IOOrder)) return false;
        return $IOOrder;
    }
    /*
    *@name 检查订单是否出过库(旧的出库记录表)
    *@param string $ebay_id  订单号
    *@return boolean
    *@addtime 2018-04-03 tian
    */
    public static function checkOutOrder($ebay_id){
        $where = ' sourceorder='.$ebay_id;
        $res = DB::Find('zfrode_goods_inout',$where);
        return $res;
    }
    /*
    *@name 检查订单是否出过库(出库单)
    *@param string $ebay_ordersn  订单编号
    *@return boolean
    *@addtime 2018-04-03 tian
    */
    public static function checkOrderInvoice($ebay_id){
        $where = " order_sn='{$ebay_id}'";
        $res = DB::Find(self::$_table_main,$where);
        return $res;
    }
    /*
    *@name 保存订单出库记录，生成出库单
    *@param string $ebay_id  订单号
    *@param1 int $type 出库类型
    *@return boolean
    *@addtime 2018-04-03 tian
    */
    public static function saveOutOrderRecord($ebay_id,$type=44){
        $return = array('status'=>'success','msg'=>'');
        $checkRes = self::checkOutOrder($ebay_id);//检测旧出库表
        if($checkRes){
            $return['status'] 	=	'error';
            $return['msg']		=	'订单已经出过库,请勿重复出库';
            return $return;
        }
		
        $checkRes = self::checkOrderInvoice($ebay_id);
        if($checkRes){
            $return['status'] 	=	'error';
            $return['msg']		=	'订单已经出过库,请勿重复出库';
            return $return;
        }
        $re=self::getOrderArr($ebay_id,$type);
        if(!$re){
			$return['status'] 	=	'error';
			$return['msg']		=	'无仓位SKU禁止出库';	
			return $return;
		}
        $inRes=self::createOutOrder($re);
		if($inRes){
			$return['msg']		=	'出库单创建成功';	
		}else{
			$return['status'] 	=	'error';
			$return['msg']		=	'出库创建失败';//读占位龙的日志
		}
		return $return;
    }
    /*
    *@name 获取出库主单信息
    *@param string $ebay_id  订单号
    *@param1 int $type 出库单类型
    *@return array()
    *@addtime 2018-04-03 tian
    */
    public static function getOrderArr($ebay_id,$type){
        $arr=array();
        $skuDetail=array();
        $table = 'ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn = b.ebay_ordersn LEFT JOIN ebay_goods AS c ON b.sku = c.goods_sn ';
        $where = 'a.ebay_id='.$ebay_id;
        $fields = 'a.ebay_ordertype,a.ebay_ordersn,a.ebay_id,a.ebay_warehouse,a.scantime,a.packinguser,a.ebay_note,a.ebay_noteb,b.sku,b.ebay_amount,b.qh_qty,b.sub_order_shippedqty,c.goods_cost';
        $re = DB::Select($table,$where,$fields);
		$f = 0;
		foreach($re AS $a){
			//多品实际发货数
			if($a['sub_order_shippedqty']===null){
				$qty = $a['ebay_amount'];
			}else{
				$qty = $a['sub_order_shippedqty'];//
				if($qty>$a['ebay_amount']){//打包产品
					$qty = $a['ebay_amount'];
				}
			}
			//出库备注的缺货
			$qh_qty = $a['qh_qty'] ? $a['qh_qty'] : 0;
			$qty = $qty-$qh_qty;
			$skuRes=self::getAmount($a['sku'],$a['ebay_warehouse']);
			
			foreach($skuRes AS $h){
				$skuDetail[$f]['sku']		=	$a['sku'];//sku
				$skuDetail[$f]['quantity']		=	$qty;//出库数量
				$skuDetail[$f]['goods_cost']	=	$a['goods_cost'];//产品成本
				$skuDetail[$f]['storage_sn'] = $h['storage_sn'];//出库的仓位
				$skuDetail[$f]['old_quantity'] = $h['amount'];//扣前库存
				$f++;
				if($h['amount']>=$qty){
					break;
				}else{
					$qty = $qty - $h['amount'];
				}
				//有一种可能  所有仓位都取完了 都不够 那就是是扣除所有仓位的库存,不允许扣负
			}
				// $qh_qty	=	$v['qh_qty'] ? $v['qh_qty'] : 0;
			// if(!$skuRes['storage_sn']){//防止拣别仓库的货物发货
				// return false;
			// }
		}
        $arr['order_type']	=	$type;
        $arr['order_sn']	=	$a['ebay_id'];
        $arr['store_id']	=	$a['ebay_warehouse'];
        $arr['io_time']		=	date('Y-m-d H:i:s',$a['scantime']);
        $arr['operator']	=	$a['packinguser'];
        $arr['remark']		=	$a['ebay_note'].','.$a['ebay_noteb'];
        $arr['sku_details']	=	$skuDetail;
        return $arr;
    }
    /*
    *@name 获取出库sku信息
    *@param string $id  主键id
    *@param1 string $store_id 仓库id
    *@return array()
    *@addtime 2018-04-03 tian
    */
    public static function getAmount($sku,$store_id){
        $table = ' ebay_storage_sku AS c';
        $where = " c.store_id='$store_id' AND c.sku='$sku' ORDER BY amount DESC";
        $fields = 'c.amount,c.storage_sn';
        $re = DB::Select($table,$where,$fields);
        return $re;
    }
	/*
	*获取库存分配的记录
	*
	*/
	public static function getKcfp($ebay_id){
		$where = ' ebay_id='.$ebay_id;
		$res = DB::Select('order_allot_storage',$where);
		return $res;
		
	}
}
//38679181   29703370
// InOutOrders::saveOutOrderRecord(38679181); 









