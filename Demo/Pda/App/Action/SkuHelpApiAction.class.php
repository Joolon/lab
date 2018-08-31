<?php

/**
 * SKU相关 请求响应API方法
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */

class SkuHelpApiAction extends ApiBaseAction
{

    /**
     * 获取SKU列表
     * 请求方式 GET
     * eg.  参数 sku_code 产品条码
     *      参数 sku 产品编码
     *      参数 store_id 仓库ID
     */
    public function getSkuInfoList(){
        $sku_code = $this->_get('sku_code');
        $sku = $this->_get('sku');
        $store_id = $this->_get('store_id');

        $where = '1';
        if($sku_code){
            $where .= " AND goods_id={$sku_code} ";
        }
        if($sku){
            $where .= " AND goods_sn={$sku} ";
        }
        if($store_id){
            $where .= " AND store_id={$store_id} ";
        }

        if($where == '1'){// 查询参数缺失
            $this->ajaxReturn(api_error('API_0001'));
        }

        $goodsInfoList = DB::Select('ebay_goods',$where);
        if(empty($goodsInfoList))
            $this->ajaxReturn(api_error('API_0002'));

        $this->ajaxReturn(api_success($goodsInfoList));
    }

    /**
     * 获取单个指定SKU信息
     * 请求方式 GET
     * eg.  参数 sku_code 产品条码
     *      参数 sku 产品编码
     *      参数 have_st_sn 若设置则附加返回SKU 最多3个仓位
     */
    public function findSkuInfo(){
        $sku_code = $this->_get('sku_code');// 采购单的产品明细的ID的产品条码
        $sku = $this->_get('sku');
        $have_st_sn = $this->_get('have_st_sn');
        $store_id = $this->getActiveStore('ID');

        if(empty($sku) AND empty($sku_code)){// 查询参数缺失
            $this->ajaxReturn(api_error('API_0001'));
        }


        $ret = array(
            'sku_code' => $sku_code,
        );

        $sku_code = $this->analysisSkuCode($sku_code);// 产品条码解析
        if(empty($sku)){// 根据产品条码找到对应的采购单里面的SKU
            $puOrderDetail = PurchaseOrder::getPurchaseOrderDetail(array('id' => $sku_code));
            if($puOrderDetail){
                $sku = $puOrderDetail[0]['goods_sn'];
                $quantity = $puOrderDetail[0]['goods_count'];
                $received_quantity = $puOrderDetail[0]['goods_count0'];
                $ret['total_quantity'] = $quantity;// 采购数量
                $ret['received_quantity'] = $received_quantity;// 已到货数量

//                $store_id = $this->getActiveStore('ID');// 当前操作的仓库ID
//                $io_ordersn = $puOrderDetail[0]['io_ordersn'];
//                $puOrderInfo = PurchaseOrder::getPurchaseOrder(array('io_ordersn'  => $io_ordersn));
//                if($puOrderInfo['io_warehouse'] != $store_id){
//                    $this->ajaxReturn(api_error('产品条码所属采购单仓库与当前操作仓库不匹配'));
//                }
            }else{
                $this->ajaxReturn(api_error('产品条码未匹配到对应采购单'));
            }
        }
        if(empty($sku)) $this->ajaxReturn(api_error('API_0011'));
        $goodsInfoList = Goods::getGoodsInfo($sku);

        if(empty($goodsInfoList))
            $this->ajaxReturn(api_error('未查询到对应的SKU'));
        $sku = $goodsInfoList['goods_sn'];

        $ret['sku'] = $goodsInfoList['goods_sn'];
        $ret['goods_name'] = self::analysisGoodsName($goodsInfoList['goods_name']);

//        print_r($ret);exit;
        if($have_st_sn !== null){
//            print_r(12);exit;
            $condition = array();
            $condition['sku'] = $sku;
            $condition['store_id'] = $store_id;
            if($this->_get('storage_id')){
                $condition['storage_id'] = $this->_get('storage_id');
            }
            if($this->_get('storage_sn')){
                $condition['storage_sn'] = $this->_get('storage_sn');
            }
            $storageSkuList = Storages::findSkuStorageList($condition,'in_time','desc');

            // 最多只返回3条仓位记录
            $storageSkuList = array_chunk($storageSkuList,3);
            $ret['stList'] = $storageSkuList[0];
//            print_r($storageSkuList);exit;

        }

        $this->ajaxReturn(api_success($ret));
    }

    /**
     * 获取单个指定SKU信息（支持特殊SKU条码）
     * 请求方式 GET
     * eg.  参数 sku_code 产品条码
     *      参数 sku 产品编码
     *      参数 have_st_sn 若设置则附加返回SKU 最多3个仓位
     */
    public function findSkuInfo2(){
        $sku_code = $this->_get('sku_code');// 采购单的产品明细的ID的产品条码
        $sku = $this->_get('sku');
        $have_st_sn = $this->_get('have_st_sn');
        $store_id = $this->getActiveStore('ID');

        if(empty($sku) AND empty($sku_code)){// 查询参数缺失
            $this->ajaxReturn(api_error('API_0001'));
        }


        $ret = array(
            'sku_code' => $sku_code,
        );

        $sku_code = $this->analysisSkuCode($sku_code,true);// 产品条码解析
        if(empty($sku)){// 根据产品条码找到对应的采购单里面的SKU
            $puOrderDetail = PurchaseOrder::getPurchaseOrderDetail(array('id' => $sku_code));
            if($puOrderDetail){
                $sku = $puOrderDetail[0]['goods_sn'];
                $quantity = $puOrderDetail[0]['goods_count'];
                $received_quantity = $puOrderDetail[0]['goods_count0'];
                $ret['total_quantity'] = $quantity;// 采购数量
                $ret['received_quantity'] = $received_quantity;// 已到货数量
            }else{
                $goodsInfo = Goods::getGoodsInfo('',$sku_code);
                $sku = $goodsInfo['goods_sn'];
            }

            if(empty($puOrderDetail) AND empty($goodsInfo)){
                $this->ajaxReturn(api_error('产品条码未匹配到产品信息'));
            }
        }
        if(empty($sku)) $this->ajaxReturn(api_error('API_0011'));
        $goodsInfoList = Goods::getGoodsInfo($sku);

        if(empty($goodsInfoList))
            $this->ajaxReturn(api_error('未查询到对应的SKU'));
        $sku = $goodsInfoList['goods_sn'];

        $ret['sku'] = $goodsInfoList['goods_sn'];
        $ret['goods_name'] = self::analysisGoodsName($goodsInfoList['goods_name']);

        if($have_st_sn !== null){
//            print_r(12);exit;
            $condition = array();
            $condition['sku'] = $sku;
            $condition['store_id'] = $store_id;
            if($this->_get('storage_id')){
                $condition['storage_id'] = $this->_get('storage_id');
            }
            if($this->_get('storage_sn')){
                $condition['storage_sn'] = $this->_get('storage_sn');
            }
            $storageSkuList = Storages::findSkuStorageList($condition,'in_time','desc');

            // 最多只返回3条仓位记录
            $storageSkuList = array_chunk($storageSkuList,3);
            $ret['stList'] = $storageSkuList[0];

        }

        $this->ajaxReturn(api_success($ret));
    }

    /**
     * 解析 产品条码（获取到采购单明细ID对应的值）
     * @param string $sku_code 产品条码
     * @param bool $move_prefix_suffix 是否去除条码中的前后缀
     * @return string
     */
    public function analysisSkuCode($sku_code,$move_prefix_suffix = false){
        if(strpos($sku_code,'.')){
            $sku_code = substr($sku_code,0,strpos($sku_code,'.'));
        }
        if($move_prefix_suffix === true){
            if(strpos($sku_code,'P')){
                $sku_code = substr($sku_code,0,strpos($sku_code,'P'));
            }

        }

        return $sku_code;
    }

    /**
     * 获取SKU仓位列表
     * eg.  参数 sku SKU
     *      参数 storage_id 仓位ID
     *      参数 storage_sn 仓位编码
     *      参数 store_id 仓库ID
     */
    public function getStorageSkuList(){
        $sku = $this->_get('sku');
        $storage_id = $this->_get('storage_id');
        $storage_sn = $this->_get('storage_sn');
        $store_id = $this->_get('store_id');

        // 产品条码或产品ID至少有一个
        $condition = array();
        if($sku) $condition['sku'] = $sku;

        if(empty($condition)){
            $this->ajaxReturn(api_error('API_0001'));
        }
        $goodsInfo = Goods::getGoodsInfo($sku);
        if(empty($goodsInfo)){
            $this->ajaxReturn(api_error('API_0002'));
        }else{
            $condition['sku'] = $goodsInfo['goods_sn'];
        }

        if($storage_id) $condition['storage_id'] = $storage_id;
        if($storage_sn) $condition['storage_sn'] = $storage_sn;
        if($store_id) $condition['store_id'] = $store_id;

        if(empty($condition)){
            $this->ajaxReturn(api_error('API_0001'));
        }

        $stList = Storages::findSkuStorageList($condition);

        $this->ajaxReturn(api_success($stList));

    }

    /**
     * PDA界面 显示的产品名称（缩写形式）
     * @param $goods_name
     * @return string
     */
    public static function analysisGoodsName($goods_name){

        $nameLen = mb_strlen($goods_name,'utf-8');// 计算中文编码下的字符长度
        if($nameLen >= 12){// 只显示前面12个字符
            $goods_name = mb_substr($goods_name,0,12,'utf-8').'...';
        }else{
            $goods_name = $goods_name;
        }

        return $goods_name;
    }



}