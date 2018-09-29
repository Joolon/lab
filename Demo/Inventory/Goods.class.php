<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Help/DB.class.php';

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'IOBase.class.php';

/**
 * 产品业务操作基础类
 * Class Goods
 */
class Goods extends IOBase
{
    public static $tablename = 'ebay_goods';

    // 产品状态
    public static $goodStatus = array(
                                        0 => '在线',
                                        1 => '下线',
                                        2 => '零库存',
                                        3 => '清仓',
                                        4 => '已调整',
                                        5 => '新开发',
                                        6 => '待上传'
                                    );
    // 产品状态（状态文字附加HTML颜色标签）
    public static $goodStatusColor = array(
                                            0 => '在线',
                                            1 => '<font color=red>下线</font>',
                                            2 => '零库存',
                                            3 => '<font color=green>清仓</font>',
                                            4 => '<font color=blue>已调整</font>',
                                            5 => '新开发',
                                            6 => '待上传'
                                        );


    // 操作类型
    private static $eventType = array(
        10 => '审核产品',
        20 => '添加库存记录',
        30 => '更新仓位',
        40 => '修改产品状态',
        50 => '修改产品侵权',


    );

    /**
     * 获取单个产品信息
     * @param string    $sku SKU
     * @param int       $goods_id 产品ID
     * @return array
     */
    public static function getGoodsInfo($sku,$goods_id = 0){
        $sku        = trim($sku);
        $goods_id   = intval($goods_id);

        $where      = ' 1 ';
        if($sku)        $where .= " AND goods_sn='$sku' ";
        if($goods_id)   $where .= " AND goods_id='$goods_id' ";


        $goodsInfo = DB::Find(self::$tablename,$where);
        return $goodsInfo;
    }

    /**
     * 获取指定 仓库、SKU 的仓位列表
     * @param int       $warehouse  仓库
     * @param string    $sku        SKU
     * @return bool|array
     */
    public static function getLocationList($warehouse,$sku){
        if(empty($warehouse) AND empty($sku)){
            self::$error = '未设定参数';
            return false;
        }

        $where = '1';
        if($warehouse) $where .= " AND store_id='$warehouse' ";
        if($sku) $where .= " AND sku='$sku' ";
        $where .= " ORDER BY amount DESC ";


        $list = DB::Select('ebay_storage_sku',$where);
        return $list;

    }

	/**
     * 查找目标SKU编码的同父SKU下所有子SKU编码
     * @param $goods_sn
     * @param $parent_sku
     * @return array|bool|type
     */
    public static function getAllSubGoodsSnList($goods_sn,$parent_sku = ''){
        if(empty($parent_sku)){
            $parent_sku = self::getGoodsInfo($goods_sn);
            if(empty($parent_sku)){
                $parent_sku = DB::Find('ebay_newgoods',"goods_sn='$goods_sn'");
            }
            $parent_sku = $parent_sku['parent_sku'];
        }
        
        if($parent_sku){
            $fields = 'goods_id,UPPER(goods_sn) AS goods_sn,goods_name,goods_pic,goods_imgs,addtim';
            $goodsList = DB::Select(self::$tablename,"parent_sku='$parent_sku' ",$fields);
            if(empty($goodsList)){
                $goodsList = DB::Select('ebay_newgoods',"parent_sku='$parent_sku' ",$fields);
            }
            if($goodsList){
                $goodsListTmp = array();
                foreach($goodsList as $goods){
                    $goodsListTmp[$goods['goods_sn']] = $goods;
                }
                $goodsList = $goodsListTmp;
                unset($goodsListTmp);
            }
            return $goodsList;
        }else{
            return false;
        }

    }

    /**
     * 添加库存记录(库存记录存在则不做任何操作，不存在则插入库存记录)
     * @param array $condition
     *      $condition = array(
     *          'goods_id' => '产品ID',
     *          'goods_sn' => '产品编码(必须)',
     *          'goods_name' => '产品名称',
     *          'goods_count' => '产品数量(默认0)',
     *          'store_id' => '产品仓库ID(必须)',
     *          'goods_days' => '产品预警天数(默认1)',
     *          'purchasedays' => '产品采购天数(默认3)',
     *       )
     * @return bool|int
     */
    public static function addSkuToOnhandle($condition){
        $add = array();

        // 验证产品编码 与 仓库ID
        if(!isset($condition['goods_sn']) OR empty($condition['goods_sn']) OR intval($condition['store_id']) <=0 ){
            self::$error = '产品编码或仓库ID不合法';
            return false;
        }

        $condition['goods_sn'] = trim($condition['goods_sn']);
        $condition['store_id'] = intval($condition['store_id']);// 转成int类型

        $add['goods_sn'] = $condition['goods_sn'];
        $add['store_id'] = $condition['store_id'];

        $goodsInfo = self::getGoodsInfo($condition['goods_sn']);
        if(empty($goodsInfo)){
            self::$error = '产品信息不存在';
            return false;
        }
        $have = DB::Find('ebay_onhandle',"goods_sn='".$condition['goods_sn']."' AND store_id='".$condition['store_id']."'");
        if($have){
            /*
            if($condition['goods_count'] != 0){// 当做入库（会不会导致两次入库呢？？还是让单独更新库存吧）
                $amount = $condition['goods_count'];
                $sqlUp  = "UPDATE ebay_onhandle SET goods_count=goods_count+{$amount} WHERE id={$have['id']} ";
                DB::QuerySQL($sqlUp);
            } */

            self::$success = '库存记录已经存在(请单独更新库存)';
            self::$error = '库存记录已经存在(请单独更新库存)';
            return true;
        }


        if(isset($condition['goods_id']) AND $condition['goods_id'] > 0) {
            $add['goods_id'] = $condition['goods_id'];
        }else{
            $add['goods_id'] = $goodsInfo['goods_id'];
        }
        if(isset($condition['goods_name']) AND $condition['goods_name'] ) {
            $add['goods_name'] = mysql_real_escape_string($condition['goods_name']);
        }else{
            $add['goods_name'] = $goodsInfo['goods_name'];
        }
        if(isset($condition['goods_count'])){
            $add['goods_count'] = intval($condition['goods_count']);
        }else{
            $add['goods_count'] = 0;
        }
        if(isset($condition['goods_days'])){
            $add['goods_days'] = intval($condition['goods_days']);
        }else{
            $add['goods_days'] = 1;
        }
        if(isset($condition['purchasedays'])){
            $add['purchasedays'] = intval($condition['purchasedays']);
        }else{
            $add['purchasedays'] = 3;
        }
        $add['qty7']    = isset($condition['qty7'])?$condition['qty7']:0;
        $add['qty15']   = isset($condition['qty15'])?$condition['qty15']:0;
        $add['qty30']   = isset($condition['qty30'])?$condition['qty30']:0;

        $add['goods_sx']    = 0;
        $add['goods_xx']    = 0;
        $add['ebay_user']   = 'otw';
        $add['remark']      = empty($_SERVER['PHP_SELF'])?'not find':$_SERVER['PHP_SELF'];// 当前运行文件路径

//        print_r($add);exit;
        $res = DB::Add('ebay_onhandle',$add);

//        print_r($res);exit;
        if($res){
            self::$success = '库存记录插入成功';
            return $res;
        }else{
            self::$error = '插入数据库失败';
            self::addOperationLog($add['goods_sn'],20,'插入库存记录失败,'.json_encode($add),$add['store_id']);// 添加错误日志
            return false;
        }

    }

    /**
     * 更新产品的仓位
     * @param string    $sku            SKU
     * @param string    $newLocation    目标仓位
     * @param null      $warehouse      为空则不判断仓库
     * @param null      $oldLocation    为空则不判断仓位
     * @return bool
     */
    public static function updateLocation($sku,$newLocation,$warehouse = NULL,$oldLocation = NULL){
        if(empty($sku) OR empty($newLocation)){
            self::$error = '参数异常';
            return false;
        }

        $where = "sku='$sku'";
        if(!empty($warehouse)){
            $where .= " AND store_id='$warehouse'";
        }
        if(!empty($oldLocation)){
            $where .= " AND storage_sn='$oldLocation' ";
        }

        $find = DB::Find('ebay_storage_sku',$where);
        if(!empty($find)){
            $res = DB::Update('ebay_storage_sku',array('storage_sn' => $newLocation),$where);
            if(!$res){
                self::$error = '产品仓位更新失败';
                return false;
            }else{
                self::addOperationLog($sku,30,"更新仓位,从[$oldLocation]到[$newLocation]",$warehouse,$oldLocation);
                return true;
            }
        }
        self::$error = '目标记录不存在';
        return false;

    }

    /**
     * 根据事件的数值获取时间类型的名称
     * @param int | array $numbers
     * @return array|mixed
     */
    public static function getEventNameByNumber($numbers){
        if(is_array($numbers)){
            $eventName = array();
            foreach($numbers as $value){
                $eventName[] = self::$eventType[$value];
            }

            return $eventName;
        }else{
            return self::$eventType[$numbers];
        }
    }

    /**
     * 更新产品资料的最后出库时间
     * @param $sku
     */
    public static function updateGoodsInfoOutTime($sku){
        if($sku){
            $update_data = array('lastexport' => time());
            DB::Update('ebay_goods',$update_data,"goods_sn='{$sku}'");
        }
    }

    /**
     * 更新产品资料的最后盘点时间
     * @param $sku
     */
    public static function updateGoodsInfoPandianTime($sku){
        if($sku){
            $update_data = array('lastpandian' => time());
            DB::Update('ebay_goods',$update_data,"goods_sn='{$sku}'");
        }
    }


    /**
     * 产品操作日志
     * @param string    $sku        SKU
     * @param string    $type       操作类型
     * @param string    $notes      日志内容
     * @param int       $warehouse  仓库
     * @param string    $storage_sn   仓位
     */
    public static function addOperationLog($sku,$type,$notes,$warehouse=0,$storage_sn=''){
        self::init();

        $add = array();
        $add['goods_sn']    = $sku;
        $add['event']       = self::$eventType[$type];
        $add['notes']       = $notes;
        $add['store_id']    = $warehouse;
        $add['storage_sn']  = $storage_sn;
        $add['operuser']    = self::$username;
        $add['opertime']    = time();

//        print_r($add);exit;

        DB::Add('ebay_goods_log',$add);

    }

    /**
     * 获得产品操作日志
     * @param string    $goods_sn  产品编码
     * @param int|array $event    日志事件类型
     * @param string $order_by
     * @return array|type
     */
    public static function getGoodsLog($goods_sn,$event,$order_by = ''){
        if(is_array($event)){
            $event = self::getEventNameByNumber($event);
            $event = implode("','",$event);
            $where = "goods_sn='$goods_sn' AND event IN('$event') ".$order_by;
        }else{
            $event = self::$eventType[$event];
            $where = "goods_sn='$goods_sn' AND event='$event' ".$order_by;
        }
//        print_r($where);exit;
        $list = DB::Select('ebay_goods_log',$where);

        if(empty($list)) return array();
        return $list;
    }

    /**
     * 修改产品的侵权状态与平台
     * @param string $goods_sn
     * @param string $tort_type  侵权平台
     * @param bool $is_add  false | true 取消|新增 侵权类型
     * @return bool
     */
    public static function setTortType($goods_sn,$tort_type,$is_add = true){
        self::init();

        $goods_tort_info = self::getGoodsTortInfo($goods_sn);

        if(empty($goods_tort_info) AND $is_add === false){// 取消侵权时 若记录不存在则直接返回成功
            return true;
        }


        $tort_type_add = array('tort_type' => $tort_type,'user'=> self::$username,'time' => time());
        // 若侵权记录不存在
        if(empty($goods_tort_info)){
            $new_more_type_tort = array($tort_type => $tort_type_add);

            $update_data['goods_sn'] = $goods_sn;
            $update_data['more_type_tort'] = serialize($new_more_type_tort);

            if( DB::Add('ebay_goods_tort',$update_data) ){
                $notes = $tort_type.": 产品设置侵权 成功";
                Goods::addOperationLog($goods_sn,50,$notes);// 保存日志
                return true;
            }else{
                return false;
            }
        }

        // 记录存在时：取消 或 设置侵权
        $now_more_type_tort = $goods_tort_info['more_type_tort'];
        $now_tort_arr = array();
        if($now_more_type_tort){
            $now_tort_arr = unserialize($now_more_type_tort);
        }

        if($is_add === false){// 取消侵权类型
            $op_msg         = "产品取消侵权";
            unset($now_tort_arr[$tort_type]);// 去除侵权
        }else{// 增加侵权类型
            $op_msg         = "产品设置侵权";
            $now_tort_arr[$tort_type] = $tort_type_add;// 增加侵权
        }

        $update_data['more_type_tort']   = serialize($now_tort_arr);
        if( DB::Update('ebay_goods_tort',$update_data,"goods_sn='$goods_sn'") ){
            $notes = $tort_type.": $op_msg 成功";
            Goods::addOperationLog($goods_sn,50,$notes);// 保存日志
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获得产品的侵权信息
     * @param $goods_sn
     * @return bool|type
     */
    public static function getGoodsTortInfo($goods_sn){

        $where = " goods_sn='$goods_sn' ";
        $goodsTort = DB::Find('ebay_goods_tort',$where);

        if(empty($goodsTort)) return false;
        return $goodsTort;

    }

    /**
     * 获得 所有侵权类型
     * @return array
     */
    public static function getTortType(){

        $ordertype = OrderDealHelp::getOrderType(0);
        $ordertype = get_array_column($ordertype,'typename','typename');
        $ordertype = array_diff($ordertype,array('EBAY订单','EBAY订单-US','EBAY订单-UK','EBAY订单-UK','EBAY订单-AU','WISH海外仓','WISH-US','复制订单','测试订单','AMZ-FBA','ALIE-HBA','ALI-EXPRESS'));// 不展示的类型
        $ordertype = array_merge(array('EBAY','SMT'),$ordertype);
        $ordertype['GBC'] = 'GBC';

        return $ordertype;
    }

    // 更新SKU的最新采购供应商
    public static function updateGoodsPartner($goods_sn,$partner_id){
        if($partner_id == '深圳峰灏瀚传奇' OR empty($partner_id)){
            return false;
        }
        $res = DB::Update('ebay_goods',array('factory' => $partner_id),"goods_sn='$goods_sn'");
        if($res){
            $res = DB::Update('ebay_newgoods',array('factory' => $partner_id),"goods_sn='$goods_sn'");
            return true;
        }else{
            return false;
        }
    }

}