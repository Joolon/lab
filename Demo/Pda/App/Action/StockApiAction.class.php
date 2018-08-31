<?php

/**
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */

class StockApiAction extends ApiBaseAction
{

    public $username = '';

    /**
     * 入库上架请求响应方法
     */
    public function putInStockSave(){
        $data = $_POST['data'];

        $force = $this->_post('force');// 是否强制入库
        $store_id = $this->getActiveStore('ID');// 当前操作的仓库ID
        $sku_code = trim($data['sku_code']);
        $sku = trim($data['sku']);
        $goods_count = (int)trim($data['goods_count']);
        $storage_sn = trim($data['storage_sn']);

        $puOrderDetail = PurchaseOrder::getPurchaseOrderDetail(array('id' => $sku_code));
        if($puOrderDetail){
            $total_quantity     = $puOrderDetail[0]['goods_count'];
            $received_quantity  = $puOrderDetail[0]['goods_count0'];

            // 本次入库数量 + 已入库数量 > 采购总数
            if(empty($force) AND ($goods_count + $received_quantity > $total_quantity) ){
                $this->ajaxReturn(api_error('API_2000'));
            }

            $io_ordersn = $puOrderDetail[0]['io_ordersn'];
            $puOrderInfo = PurchaseOrder::getPurchaseOrder(array('io_ordersn'  => $io_ordersn));
            if($puOrderInfo['io_warehouse'] != $store_id){
                $this->ajaxReturn(api_error('采购单仓库与当前操作仓库不匹配'));
            }
        }else{
            $this->ajaxReturn(api_error('产品条码未匹配到对应采购单'));
        }


        // 验证并判定仓位
        $storageSkuInfo = Storages::findSkuByStorage($sku,$store_id,$storage_sn);
        if(empty($storageSkuInfo)){
            $storageInfo = Storages::findStorage($store_id,$storage_sn);
            if($storageInfo){
                $res = Storages::bingSkuByStorage($storageInfo['id'],$sku);
                if(empty($res)){
                    $this->ajaxReturn(api_error(Storages::$error));
                }
            }else{
                $this->ajaxReturn(api_error('此仓库下无此仓位'));
            }
        }
        // 更新库存并生成入库单
        if($storageSkuInfo OR $res){// 该仓位没有此SKU 且仓位绑定SKU失败
            DB::QuerySQL('BEGIN');

            PurchaseOrder::updatePurchaseOrderDetailFPRK(array('id' => $sku_code),$goods_count);
            PurchaseOrder::checkPurchaseOrderIsComplete($puOrderDetail[0]['io_ordersn']);
            $truename = $this->getActiveUsername();

            $inOrder = array();
            $inOrder['order_type'] = '41';// 出入库类型
            $inOrder['order_sn'] = createOrderSn('PDA');
            $inOrder['store_id'] = $store_id;
            $inOrder['io_time'] = date('Y-m-d H:i:s');
            $inOrder['operator'] = $truename;
            $inOrder['last_order_sn'] = $puOrderDetail[0]['io_ordersn'];
            $inOrder['last_order_type'] = '采购入库';

            // 历史库存数
            $old_goods_count = isset($storageSkuInfo['amount'])?$storageSkuInfo['amount']:0;

            $sku_detail = array();
            $sku_detail['sku'] = $sku;
            $sku_detail['quantity'] = $goods_count;
            $sku_detail['storage_sn'] = $storage_sn;
            $sku_detail['old_quantity'] = $old_goods_count;
            $sku_detail['goods_cost'] = empty($goodsInfo['goods_cost']) ? 0 : $goodsInfo['goods_cost'];
            $sku_detail['total_cost'] = $sku_detail['quantity'] * $sku_detail['goods_cost'];
            $sku_detail['last_sku_link'] = $sku_code;
            $inOrder['sku_details'] = array($sku_detail);

            $ioorder_id = InOutOrders::createInOrder($inOrder);
            if($ioorder_id){
                // 更新入库仓位
                $upSql = "UPDATE ebay_iostoredetail SET audit0='$truename',audit1='$truename',audit2='$truename',
                            last_storage='$storage_sn' WHERE id='$sku_code' LIMIT 1";
                DB::QuerySQL($upSql);
                // 插入入库时间
                if(DB::Find('ebay_iostore_rukutime',"io_ordersn='$io_ordersn'")){
                    DB::Update('ebay_iostore_rukutime',array('rukutime' => time()),"io_ordersn='$io_ordersn'");
                }else{
                    $addInfo = array('fp_ordersn' => $ioorder_id,'io_ordersn' => $io_ordersn,'type' => 1,'rukutime' => time());
                    DB::Add('ebay_iostore_rukutime',$addInfo);
                }

                DB::QuerySQL('COMMIT');
                $this->ajaxReturn(api_success());
            }else{
                DB::QuerySQL('ROLLBACK');
                $this->ajaxReturn(api_error('操作失败：'.InOutOrders::getError()));
            }
        }else{
            DB::QuerySQL('ROLLBACK');
            $this->ajaxReturn(api_error('此库位库存不存在且自动绑定仓位失败'));
        }
    }


    /**
     * 库存转移请求响应方法
     */
    public function updateLocationSave(){
        $data = $_POST['data'];

        $store_id = $this->getActiveStore('ID');// 当前操作的仓库ID
        $sku_code = trim($data['sku_code']);
        $sku = trim($data['sku']);
        $from_storage_sn = trim($data['from_storage_sn']);
        $to_storage_sn = trim($data['to_storage_sn']);

        $res = StorageStock::stockShift($sku,$store_id,$from_storage_sn,$to_storage_sn,'',true);
//        print_r($res);exit;
        if($res){
            $this->ajaxReturn(api_success());
        }else{
            $this->ajaxReturn(api_error('更新库存失败：'.HhStock::getError()));
        }
    }

    /**
     * 库存盘点请求响应方法
     */
    public function stockCountSave(){
        $data = $_POST['data'];

        $store_id = $this->getActiveStore('ID');// 当前操作的仓库ID
        $sku_code = trim($data['sku_code']);
        $sku = trim($data['sku']);
        $goods_count = (int)trim($data['goods_count']);
        $storage_sn = trim($data['storage_sn']);
//        print_r($data);exit;

        $storageSkuInfo = Storages::findSkuByStorage($sku,$store_id,$storage_sn);
        if(empty($storageSkuInfo)){
            $storageInfo = Storages::findStorage($store_id,$storage_sn);
            if($storageInfo){
                $res = Storages::bingSkuByStorage($storageInfo['id'],$sku);
            }else{
                $this->ajaxReturn(api_error('此仓库下无此仓位'));
            }
        }


        if($storageSkuInfo OR $res){
            if($storageSkuInfo){
                $old_goods_count = $storageSkuInfo['amount'];
            }else{
                $old_goods_count = 0;
            }
            $addQty = $goods_count - $old_goods_count;
            if($addQty == 0){
                $this->ajaxReturn(api_error('数据未发生改变'));
            }
            if($addQty > 0){
                $io_type = '43';
            }else{
                $io_type = '46';
            }

            $inOrder = array();
            $inOrder['order_type'] = $io_type;// 出入库类型
            $inOrder['order_sn'] = createOrderSn('PDA-PD-');
            $inOrder['store_id'] = $store_id;
            $inOrder['io_time'] = date('Y-m-d H:i:s');
            $inOrder['operator'] = $this->getActiveUsername();


            $sku_detail = array();
            $sku_detail['sku'] = $sku;
            $sku_detail['quantity'] = abs($addQty);// 绝对值
            $sku_detail['storage_sn'] = $storage_sn;
            $sku_detail['old_quantity'] = $old_goods_count;
            $sku_detail['goods_cost'] = empty($goodsInfo['goods_cost']) ? 0 : $goodsInfo['goods_cost'];
            $sku_detail['total_cost'] = $sku_detail['quantity'] * $sku_detail['goods_cost'];
            $inOrder['sku_details'] = array($sku_detail);
            if($addQty > 0){
                $res = InOutOrders::createInOrder($inOrder);// 入库单(盘盈都做入库处理)
            }else{
                $res = InOutOrders::createOutOrder($inOrder);// 出库单盘亏做入库处理)
            }

            $this->ajaxReturn(api_success());

        }else{
            $this->ajaxReturn(api_error('此库位库存不存在且自动绑定仓位失败'));
        }

    }

    /**
     * 库存查询请求响应方法
     */
    public function queryStockSave(){

        $store_id = $this->getActiveStore('ID');// 当前操作的仓库ID
        $query_type = $this->_get('query_type');
        $query_value = $this->_get('query_value');
        $page_index = $this->_get('page_index');
        if(empty($page_index)) $page_index = 1;


        if(empty($query_type) OR empty($query_value)){
            $this->ajaxReturn(api_error('API_0001'));
        }

        $store_id = intval($store_id);
        $where = " AND store_id='$store_id' ";
        switch ($query_type){
            case 'sku':
                $where .= " AND sku='$query_value' ";
                break;
            case 'sku_code':// 产品条码转换成 SKU查询
                $skuHelpApi = new SkuHelpApiAction();
                $query_value = $skuHelpApi->analysisSkuCode($query_value,true);
                $puOrderDetail = PurchaseOrder::getPurchaseOrderDetail(array('id' => $query_value));

                if($puOrderDetail){
                    $sku = $puOrderDetail[0]['goods_sn'];
                    $where .= " AND sku='$sku' ";
                }else{
                    $goodsInfo = Goods::getGoodsInfo('',$query_value);
                    $sku = $goodsInfo['goods_sn'];
                    $where .= " AND sku='$sku' ";
                }
                if(empty($puOrderDetail) AND empty($goodsInfo)){
                    $where .= " AND sku='XXXXXXXXX' ";// 设置异常值，查询结果为空
                }
                break;
            case 'storage_sn':
                $where .= " AND storage_sn='$query_value' ";
                break;
        }

        // 统计总个数
        $sqlSelCount = " SELECT count(1) as num FROM ebay_storage_sku WHERE 1 ".$where;
        $totalCount = DB::QuerySQL($sqlSelCount);
        $totalCount = isset($totalCount[0]['num'])?$totalCount[0]['num']:0;

        // 分页展示的数据
        $page_count = 10;
        if($page_index == 'LAST') $page_index = ceil($totalCount/$page_count);
        $limit  = " LIMIT ".($page_count*($page_index-1)).",{$page_count}";
        $sqlSel = " SELECT sku,store_id,storage_id,storage_sn,amount FROM ebay_storage_sku WHERE 1 ".$where.$limit;
        $list = DB::QuerySQL($sqlSel);
//        print_r($list);exit;

        if(count($list)){
            $list = array(
                'page_index' => $page_index,
                'page_num' => ceil($totalCount/$page_count),
                'list' => $list
            );
//            print_r($list);exit;

            $this->ajaxReturn(api_success($list));

        }else{
            $this->ajaxReturn(api_error('API_0002'));
        }
    }

}