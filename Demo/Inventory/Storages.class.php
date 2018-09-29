<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Help/DB.class.php';


include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'IOBase.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'HhStock.class.php';

/**
 * 仓库仓位SKU业务逻辑操作类
 * Class Storages
 */
class Storages extends IOBase
{

    /**
     * 创建仓位
     * $condition = array(
     *  'store_id' => '仓库ID',
     * 'storage_sn' => '新仓位编码',
     * )
     * @param $condition
     * @return bool|int
     */
    public static function createStorage($condition){
        self::init();

        $store_id   = intval($condition['store_id']);
        $storage_sn = strtoupper(trim($condition['storage_sn']));

        if(empty($store_id) OR empty($storage_sn)){
            self::$error = '仓库|仓位缺失';
            return false;
        }

        $find = self::findStorage($store_id,$storage_sn);
        if($find){
            self::$error = '新增失败，该仓位已经存在';
            return false;
        }
        $add = array();
        $add['store_id'] = $store_id;
        $add['storage_sn'] = $storage_sn;
        $add['storage_area'] = substr($storage_sn,0,1);
        $add['storage_first'] = substr($storage_sn,0,strpos($storage_sn,'.'));
        $add['adduser'] = self::$username;
        $add['addtime'] = date('Y-m-d H:i:s');
        $add['isuse'] = 1;

        $res = DB::Add('ebay_storages',$add);
        if($res){
            $storage_id = mysql_insert_id();
            return $storage_id;
        }else{
            self::$error = '新增失败';
            return false;
        }

    }

    /**
     * 查找 仓库下指定的仓位
     * @param int $store_id  仓库ID
     * @param string $storage_sn  仓位编码
     * @param bool $goods 是否查询产品列表(false.不带产品,true.带产品)
     * @return array|type
     */
    public static function findStorage($store_id,$storage_sn,$goods = false){

        $where = " store_id='$store_id' AND storage_sn='$storage_sn' ";
        $info = DB::Find('ebay_storages',$where);
        if($info AND $goods === true){
            $condition['store_id'] = $store_id;
            $condition['storage_sn'] = $storage_sn;
            $goodsList = HhStock::getGoodsListByStorage($condition);
            $info['goodsList'] = $goodsList;
        }

        if(empty($info)) return array();
        return $info;
    }


    /**
     * 查找指定仓库、仓位、SKU 的记录
     * @param $sku
     * @param $store_id
     * @param string $storage_sn
     * @return array|type
     */
    public static function findSkuByStorage($sku,$store_id,$storage_sn = ''){
        $where = " sku ='$sku' ";
        if($store_id){
            $where .= " AND store_id='$store_id'  ";
        }
        if($storage_sn){
            $where .= " AND storage_sn='$storage_sn' ";
        }
//        echo $where;exit;
        $info = DB::Find('ebay_storage_sku',$where);

        if(empty($info)) return array();
        return $info;

    }

    /**
     * 查找 仓位库存 的记录
     * @param array $condition  至少有一个元素
     *      $condition = array(
     *          'storage_id' => '仓位ID',
     *          'storage_sn' => '仓位编码',
     *          'sku' => '产品编码',
     *          'store_id' => '仓库ID',
     *      )
     * @param string $order_by 排序字段（默认库存数amount）
     * @param string $order_by_dir 排序方式（默认升序 asc）     
	 * @return array|bool
     */
    public static function findSkuStorageList($condition,$order_by = 'amount',$order_by_dir = 'asc'){
        $where = '1';

        if(isset($condition['storage_id'])){
            $where .= " AND id='{$condition['storage_id']}' ";
        }
        if(isset($condition['storage_sn'])){
            $where .= " AND storage_sn='{$condition['storage_sn']}' ";
        }
        if(isset($condition['sku'])){
            $where .= " AND sku='{$condition['sku']}' ";
        }
        if(isset($condition['store_id'])){
            $where .= " AND store_id='{$condition['store_id']}'  ";
        }

        if($where == '1'){
            self::$error = '查询条件参数缺失';
            return false;
        }

        $where .= " ORDER BY {$order_by} {$order_by_dir} ";
        $info = DB::Select('ebay_storage_sku',$where);
        if(empty($info)) return array();
		return $info;
        // $info1 = array();
        // foreach($info AS $v){//过滤虚拟仓位  2018-05-03 tian
                // $length = strlen(substr($v['storage_sn'],strripos($v['storage_sn'],'.')+1));
                // $U = substr(strtoupper(trim($v['storage_sn'])), 0,1);
                // if($length>=4 && $U!='U') {
                        // continue;
                // }
                // $info1[]=$v;
        // }
		
        // return $info1;

    }


    /**
     * 获取单个指定仓位信息
     * @param $id
     * @return array|type
     */
    public static function getStorageById($id){
        $where = " id='$id'  ";
        $info = DB::Find('ebay_storages',$where);
        if(empty($info)) return array();
        return $info;
    }

    /**
     * 更新仓位
     * @param $condition
     *   array(
            'id' => '仓位ID',
     *      'store_id' => '仓库ID',
     *      'storage_sn' => '仓位',
     *      'isuse' => '是否可用(0|1)'
     * )
     * @param bool $updateGoods 是否更新到仓位库存SKU管理表
     * @return bool
     */
    public static function updateStorage($condition,$updateGoods=true){
        self::init();

        $have = self::findStorage($condition['store_id'],$condition['storage_sn']);
        if($have){
            self::$error = '仓位已经存在，请勿重复添加';
            return false;
        }

        $update = array();
        if(isset($condition['store_id'])){
            $update['store_id'] = $condition['store_id'];
        }
        if(isset($condition['storage_sn'])){
            $update['storage_sn'] = $condition['storage_sn'];
        }
        if(isset($condition['isuse'])){
            $update['isuse'] = (int)$condition['isuse'];
        }

        $update['adduser'] = self::$username;
        $update['addtime'] = date('Y-m-d H:i:s');

        try{
            $res = DB::Update('ebay_storages',$update,"id='".$condition['id']."'");
            if($res){
                $update = array();
                if(isset($condition['store_id']))
                    $update['store_id'] = $condition['store_id'];
                if(isset($condition['storage_sn']))
                    $update['storage_sn'] = $condition['storage_sn'];
                DB::Update('ebay_storage_sku',$update,"storage_id='".$condition['id']."'");

                return true;
            }else{
                self::$error = '更新失败';
                return false;
            }
        }catch (Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 更新产品资料里面的仓位号
     * @param $sku
     * @param $storage_sn
     * @return bool
     */
    public static function updateStorageOnGoodsInfo($sku,$storage_sn){
        if(empty($sku) OR empty($storage_sn)){
            return false;
        }
        $update = array('goods_location' => $storage_sn);
        $result = DB::Update('ebay_goods',$update,"goods_sn='$sku'");
        if($result){
            $result = DB::Update('ebay_newgoods',$update,"goods_sn='$sku'");
            return true;
        }else{
            return false;
        }

    }

    /**
     * 绑定一个SKU到仓库库位上
     * @param $storage_id
     * @param $new_sku
     * @param bool $check_storage_count 是否验证仓位的个数（针对不需要验证仓位个数的功能，如：库存转移）
     * @return bool
     */
    public static function bingSkuByStorage($storage_id,$new_sku,$check_storage_count = true){
        self::init();

        $storageInfo = self::getStorageById($storage_id);
        if(empty($storageInfo) || empty($new_sku)){
            self::$error = '未找到对应的仓位或SKU为空';
            return false;
        }
        $store_id = $storageInfo['store_id'];

        if(self::findSkuByStorage($new_sku,$storageInfo['store_id'],$storageInfo['storage_sn'])){
            self::$error = '该仓位上已存在该SKU';
            self::updateStorageOnGoodsInfo($new_sku,$storageInfo['storage_sn']);
            return false;
        }

		// 最多只能有1个实体仓位
        $skuSnList = self::findSkuStorageList(array('sku' => $new_sku,'store_id' => $storageInfo['store_id']));

        if($check_storage_count === true){// 针对不需要验证仓位个数的功能，如：库存转移
            $flag   = false;
            $delete = 0;
            foreach($skuSnList as $st_val){
                $st_id      = $st_val['id'];
                $right_str  = substr($st_val['storage_sn'],strrpos($st_val['storage_sn'],'.')+1 );
                if(strlen($right_str) >= 4 ){// 判断是否是虚拟仓位
                    if($st_val['amount'] <= 0){
                        $res = DB::Delete('ebay_storage_sku',"id='$st_id' LIMIT 1");// 删除虚拟仓位
                        if($res){
                            $delete ++;
                        }
                    }else{
                        self::$error = '此SKU存在库存大于0的虚拟仓位,请先处理';
                        return false;
                    }
                }else{
                    $flag = true;
                }
            }

            if($delete == count($skuSnList)){// 仓位都是虚拟仓位且都删除 则重置总库存为0
                DB::Update('ebay_onhandle',array('goods_count' => 0),"goods_sn='$new_sku' AND store_id='$store_id'");
            }
            if($flag === true){
                self::$error = '此SKU已存在实体仓位，不可再绑定该仓位';
                return false;
            }
        }

        $add = array();
        $add['sku'] = $new_sku;
        $add['store_id'] = $storageInfo['store_id'];
        $add['storage_id'] = $storageInfo['id'];
        $add['storage_sn'] = $storageInfo['storage_sn'];
        $add['amount'] = 0;
        $add['add_time'] = date('Y-m-d H:i:s');
        $add['add_user'] = self::$username;
        $add['isuse'] = 1;

        $res = DB::Add('ebay_storage_sku',$add);
        if($res){
			self::updateStorageOnGoodsInfo($new_sku,$storageInfo['storage_sn']);
            return true;
        }else{
            self::$error = '插入失败';
            return false;
        }
    }

    /**
     * 解绑SKU（设置为停用状态或删除）
     * @param int $storage_sku_id 仓位库存记录ID
     * @param bool $is_remove false|true 设置状态|删除记录
     * @return bool
     */
    public static function unbindSkuByStorage($storage_sku_id,$is_remove = false){
        self::init();
        if(empty($storage_sku_id)){
            self::$error = '参数缺失';
            return false;
        }

        if($is_remove === true){
            $res = DB::Delete('ebay_storage_sku',"id='$storage_sku_id'");
        }else{
            $update = array('isuse' => 0);
            $res = DB::Update('ebay_storage_sku',$update,"id='$storage_sku_id'");
        }

        if($res){
            return true;
        }else{
            self::$error = '停用失败';
            return false;
        }
    }

    /**
     * 查找仓库下所有仓位信息
     * @param $store_id
     * @return array|type
     */
    public static function getStorageSnListByStoreId($store_id){
        $list = DB::Select('ebay_storages'," store_id='$store_id' ");

        if(empty($list)) return array();
        return $list;
    }

    /**
     * @param int $store_id  仓库ID
     * @param string $sku 产品编码
     * @param string $storage_sn  仓位
     * @return array|type
     */
    public static function getBindedStorageSnList($store_id,$sku = '',$storage_sn=''){
        $where = ' 1 ';
        if($store_id) $where .= " AND store_id='$store_id' ";
        if($sku) $where .= " AND sku='$sku' ";
        if($storage_sn) $where .= " AND storage_sn='$storage_sn' ";

        $list = DB::Select('ebay_storage_sku',$where,'id,sku,store_id,storage_id,storage_sn,amount');
        if(empty($list)) return array();
        return $list;

    }


    /**
     * 获取有效的仓位
     * @param int $store_id  仓库ID
     * @param string $sku 产品编码
     * @param string $storage_sn  仓位
     * @param string $order_by 排序字段（默认库存数amount）
     * @param string $order_by_dir 排序方式（默认升序 asc）
     * @return array|type
     */
    public static function getValidStorageSnList($store_id,$sku = '',$storage_sn='',$order_by = 'add_time',$order_by_dir = 'desc'){
        $where = ' 1 ';
        if($store_id) $where .= " AND store_id='$store_id' ";
        if($sku) $where .= " AND sku='$sku' ";
        if($storage_sn) $where .= " AND storage_sn='$storage_sn' ";

        $where .= " AND LENGTH( SUBSTRING_INDEX(storage_sn,'.',-1)) < 4 ";// 去除虚拟仓位

        $where .= " ORDER BY $order_by $order_by_dir";
//        echo $where;exit;

        $list = DB::Select('ebay_storage_sku',$where,'id,sku,store_id,storage_id,storage_sn,amount');
        if(empty($list)) return array();
        return $list;

    }

    /**
     * 验证仓位编号是否合法
     * @param $storage_sn 仓位编号
     * @param string $store_id  仓库ID
     * @return bool
     */
    public static function checkStorageIsValid($storage_sn,$store_id = ''){

        if(empty($store_id) OR empty($storage_sn)){
            return true;
        }
        $storage_sn = trim($storage_sn);
        $head = strtoupper(substr($storage_sn,0,1));

        if($store_id == 37){
            if($head != 'U'){
                self::$error = '义务仓库只能是U仓';
                return false;
            }
        }elseif($store_id != 37){
            if($head == 'U'){
                self::$error = '非义务仓库不能有U仓';
                return false;
            }
        }

        return true;
    }


}