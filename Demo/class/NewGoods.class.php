<?php
include_once dirname(dirname(__FILE__)).'/Help/DB.class.php';

/**
 * 新产品开发
 * Class NewGoods
 */
class NewGoods
{
    private static $tablename = 'ebay_newgoods';
    private static $tablenameLog = 'ebay_newgoodslog';
    private static $tablenameParents = 'ebay_newgoods_parents';
    private static $truename = null;

    // 状态流 当前状态 可以到达的上一个状态、下一个状态
    public static $stateFlow = array(
        '100' => array('nextstatus' => '200'),
        '200' => array('laststatus' => '100','nextstatus' => '300,301'),
        '300' => array('laststatus' => '200','nextstatus' => '400,500'),
        '301' => array('laststatus' => '200','nextstatus' => '200'),
        '400' => array('laststatus' => '300','nextstatus' => '500'),
        '500' => array('laststatus' => '300,400','nextstatus' => '600'),
        '600' => array('laststatus' => '500','nextstatus' => '999'),
        '999' => array('laststatus' => '600'),
        '-1000' => array('nextstatus' => '100'),
    );

    // 状态值对应的中文名称
    public static $stateName = array(
        '100' => '新建',
        '200' => '提交审批',
        '300' => '审批通过',
        '301' => '退回重申',
        '400' => '描述处理',
        '500' => '拍照',
        '600' => '美工处理',
        '999' => '已完成',
        '-1000' => '已终止',
    );

    // 操作事件数值对应的名称
    public static $eventName = array(
        '1'  => '添加备注',
        '10' => '修改状态',
        '15' => '产品添加',
        '20' => '产品信息更新',
        '30' => '添加图片',
        '35' => '删除图片',
        '40' => '编辑描述',
        '45' => '编辑文案',
        '50' => '同步图片到图片库',
        '100' => '同步已完成产品',

        '200' => '产品认领',
        '201' => '产品认领Item URL'
    );

    public static function init(){
        global $truename;
        self::$truename = $truename;
    }


    /**
     * 获取单个产品信息
     * @param string $pdid 新产品编号
     * @param string $goods_sn 产品编码
     * @return bool|type
     */
    public static function getNewGoodsInfo($pdid,$goods_sn = ''){
        if(empty($goods_sn) AND empty($pdid)) // 参数缺失
            return false;

        $where = '1';
        if(!empty($goods_sn))
            $where .= " AND goods_sn='$goods_sn' ";

        if(!empty($pdid))
            $where .= " AND pdid='$pdid' ";

        return DB::Find('ebay_newgoods',$where);
    }

    /**
     * 判断父SKU是否已经存在
     * @param string $parent_sku 父SKU编码
     * @param bool $goodsList 是否附加子SKU列表
     * @return bool|type
     */
    public static function checkParentSkuExists($parent_sku,$goodsList = false){

        $where = " parent_sku='$parent_sku' ";
        $info = DB::Find(self::$tablenameParents,$where);
        if(empty($info)) return false;

        if($goodsList === true){
            $goodsList = self::getAllSubGoodsSnList('',$parent_sku);
            $info['goodsList'] = $goodsList;
        }

        return $info;
    }

    /**
     * 获取当前状态的上一个状态和下一个状态，返回两者的数组
     * @param  $nowStatus
     * @return array
     */
    public static function getLastAndNextStatus($nowStatus){

        $lastStatus = self::$stateFlow[$nowStatus]['laststatus'];// 上一个状态
        $nextStatus = self::$stateFlow[$nowStatus]['nextstatus'];// 下一个状态

        $lastStatusArr = array();
        $nextStatusArr = array();
        if(!empty($lastStatus)){
            if(strpos($lastStatus,',')){// 多个状态，打散为数组
                $lastStatusArr = explode(',',$lastStatus);
            }else{
                $lastStatusArr = array($lastStatus);
            }
        }

        if(!empty($nextStatus)){
            if(strpos($nextStatus,',')){
                $nextStatusArr = explode(',',$nextStatus);
            }else{
                $nextStatusArr = array($nextStatus);
            }
        }

        $state = array(
            'laststatus' => $lastStatusArr,
            'nextstatus' => $nextStatusArr
        );
        return $state;
    }

    /**
     * 改变产品的状态
     * @param string $pdid 新产品id
     * @param string $goods_sn 产品编码
     * @param int $toStatus  目标状态
     * @param string $notes  备注信息
     * @return array
     */
    public static function changeStatus($pdid,$goods_sn,$toStatus,$notes = ''){

        // 产品信息验证
        $newGoodsInfo = self::getNewGoodsInfo($pdid,$goods_sn);

        if(empty($newGoodsInfo)){
            return array('code' => '0X0001','msg' => '该SKU不存在');
        }

        if(empty($goods_sn)) $goods_sn = $newGoodsInfo['goods_sn'];

        $nowStatus  = $newGoodsInfo['nowstatus'];// 产品当前状态
        $state      = self::getLastAndNextStatus($nowStatus);
        $lastStatus = $state['laststatus'];
        $nextStatus = $state['nextstatus'];

        // -1000 已终止状态：任何状态下都可以修改到已终止
        // 999|-1000 状态(已完成和已终止)下不能修改
        if( $toStatus != '-1000' AND (empty($nextStatus) OR (!in_array($toStatus,$nextStatus) AND !in_array($toStatus,$lastStatus))  OR in_array($nowStatus,array('999')))  ){
            return array('code' => '0X0001','msg' => '该SKU不能执行该操作(状态不对)');
        }

        $res = self::doChangeStatus($newGoodsInfo,$nowStatus,$toStatus,$notes);// 改变状态

        if($res){
            $return = array('code' => '0X0000','msg' => '状态更改成功');
        }else{
            $return = array('code' => '0X0001','msg' => '状态更改失败');
        }

        return $return;
    }

    /**
     * 改变产品的状态
     * @param string $newGoodsInfo 产品信息
     * @param int $nowStatus 当前状态
     * @param int $toStatus 目标状态
     * @param string $remark 备注信息
     * @return bool|type
     */
    public static function doChangeStatus($newGoodsInfo,$nowStatus,$toStatus,$remark = ''){
        self::init();

        $goods_sn = $newGoodsInfo['goods_sn'];

        if(empty($goods_sn)) // 参数缺失
            return false;

        $where = '1';
        if(!empty($goods_sn))
            $where .= " AND goods_sn='$goods_sn' ";

        $state = self::getLastAndNextStatus($toStatus);
        $nextstatus = $state['nextstatus'];

        $update = array(
            'laststatus'=> $nowStatus,// 当前状态为目标状态的上一状态
            'nowstatus' => $toStatus,// 目标状态
            'nextstatus' => implode(',',$nextstatus), // 目标状态的下一状态
            'remark' => empty($newGoodsInfo['remark'])?$remark:($newGoodsInfo['remark'].','.$remark)
        );

        if($toStatus == 300 OR $toStatus == 301){// 审核状态：审核员
            $update['audituser']  = self::$truename;
        }elseif($toStatus == 100){// 改成新建状态是清空审核人
            $update['audituser']    = '';
            $update['endeduser']    = '';
        }elseif($toStatus == -1000 ){// 终止人员
            $update['endeduser']  = self::$truename;
        }

        $res = DB::Update(self::$tablename,$update,$where);

        $notes = '修改状态从['.self::$stateName[$nowStatus].']到['.self::$stateName[$toStatus].']';
        if($remark) $notes .= 'Note:'.$remark;

        self::addNewGoodsLog($goods_sn,10,$notes);

        return $res;
    }

    /**
     * 删除一个图片地址
     * @param string $goods_sn  SKU编码
     * @param string $imgUrl    目标图片地址
     * @param int    $allSub    是否查找同父SKU下所有子SKU并删除目标地址 0.仅当前SKU,1.查找子SKU
     * @return bool
     */
    public static function removeImageBySku($goods_sn,$imgUrl,$allSub = 0){

        if($allSub == 1){
            $goodsList = self::getAllSubGoodsSnList($goods_sn);
        }else{
            $goodsList[$goods_sn] = self::getNewGoodsInfo('',$goods_sn);
        }

//        print_r($goodsList);exit;


        if($goodsList){
            foreach ($goodsList as $key => $value){
                $n_goods_sn     = $value['goods_sn'];
                $n_goods_pic    = $value['goods_pic'];
                $n_goods_imgs   = $value['goods_imgs'];

                $nowGoodPic     = DB::Find('ebay_newgoodspic',"goods_sn='$n_goods_sn'");

                $diff   = array_diff(explode(';',$nowGoodPic['picture_url']),array('',$imgUrl));
                $diff_1 = array_diff(explode(';',$nowGoodPic['picture_url1']),array('',$imgUrl));
                $diff_2 = array_diff(explode(';',$nowGoodPic['picture_url2']),array('',$imgUrl));
                $diff_3 = array_diff(explode(';',$nowGoodPic['picture_url3']),array('',$imgUrl));
                $diff_4 = array_diff(explode(';',$nowGoodPic['picture_url4']),array('',$imgUrl));

                $update = array(
                    'picture_url'  => implode(';',$diff),//
                    'picture_url1' => implode(';',$diff_1),//
                    'picture_url2' => implode(';',$diff_2),//
                    'picture_url3' => implode(';',$diff_3),//
                    'picture_url4' => implode(';',$diff_4),//
                );
                DB::Update('ebay_newgoodspic',$update,"goods_sn='$n_goods_sn'");

                $goods_imgs = array_diff(explode(';',$n_goods_imgs),array('',$imgUrl));
                $update     = array('goods_imgs' => implode(';',$goods_imgs));
                if($n_goods_pic == $imgUrl){// 如果删除的图片是显示图片则重新获取显示图片
                    $diff_all = array_merge($diff_1,$diff_3,$diff_4,$diff_2);// 数组合并（优先级:$diff_1>$diff_3>$diff_4）
                    if(current($diff_all)){
                        $n_goods_pic = current($diff_all);
                    }else{
                        $n_goods_pic = '';
                    }
                    $update['goods_pic'] = $n_goods_pic;
                    DB::Update('ebay_goods',array('goods_pic' => '','goods_imgs' => ''),"goods_sn='$n_goods_sn'");
                }
                DB::Update('ebay_newgoods',$update,"goods_sn='$n_goods_sn'");

                self::addNewGoodsLog($n_goods_sn,35,'删除图片['.$goods_sn.'],路径:'.$imgUrl);
            }
        }

        return false;
    }

    /**
     * 查找目标SKU编码的同父SKU下所有子SKU编码
     * @param $goods_sn
     * @param $parent_sku
     * @return array|bool|type
     */
    public static function getAllSubGoodsSnList($goods_sn,$parent_sku = ''){
        if(empty($parent_sku)){
            $parent_sku = self::getNewGoodsInfo('',$goods_sn);
            $parent_sku = $parent_sku['parent_sku'];
        }

        if($parent_sku){
            $goodsList = DB::Select(self::$tablename,"parent_sku='$parent_sku' ",'pdid,goods_id,UPPER(goods_sn) AS goods_sn,goods_name,goods_pic,goods_imgs,addtim ');
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
     * 获取SKU下顶级分类ID
     * @param $goods_sn
     * @return int
     */
    public static function getTopCategoryId($goods_sn){// 换成递归好不好
        $goodInfo = DB::Find('ebay_newgoods',"goods_sn='$goods_sn' AND addtim>0 ","goods_sn,parent_sku,goods_category ");
        $goods_category = $goodInfo['goods_category'];
        $goods_category_pid = DB::Find('ebay_goodscategory',"id='$goods_category' ");
        $goods_category_pid = $goods_category_pid['pid'];
        if($goods_category_pid){
            $goods_category_pid = DB::Find('ebay_goodscategory',"id='$goods_category_pid' ");
            $goods_category_pid = $goods_category_pid['pid'];
            if($goods_category_pid){
                $goods_category_pid = DB::Find('ebay_goodscategory',"id='$goods_category_pid' ");
                $goods_category_pid = $goods_category_pid['pid'];
            }
        }
        if(!$goods_category_pid) $goods_category_pid = $goods_category;

        return $goods_category_pid;
    }

    /**
     * 添加备注（纯添加备注，不产生任何副操作）
     * @param $pdid   编号ID
     * @param $goods_sn   产品编码
     * @param string $remark  备注信息
     * @return bool
     */
    public static function addNote($pdid,$goods_sn,$remark = ''){

        // 产品信息验证
        $newGoodsInfo = self::getNewGoodsInfo($pdid,$goods_sn);

        $update = array(
            'remark' => empty($newGoodsInfo['remark'])?$remark:($newGoodsInfo['remark'].';'.$remark)
        );

        $where = "pdid='$pdid' ";
        DB::Update(self::$tablename,$update,$where);

        self::addNewGoodsLog($goods_sn,1,$remark);

        return false;
    }

    /**
     * 更新备注
     * @param $pdid   编号ID
     * @param $goods_sn   产品编码
     * @param string $remark  备注信息
     * @return bool
     */
    public static function updateNote($pdid,$goods_sn,$remark = ''){
        $update = array(
            'remark' => $remark
        );

        $where = "pdid='$pdid' ";
        DB::Update(self::$tablename,$update,$where);

        self::addNewGoodsLog($goods_sn,1,$remark);

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
                $eventName[] = self::$eventName[$value];
            }

            return $eventName;
        }else{
            return self::$eventName[$numbers];
        }
    }

    /**
     * 插入新产品操作日志
     * @param string $goods_sn 产品编码
     * @param string $envent  事件类型
     * @param string $notes  事件描述
     */
    public static function addNewGoodsLog($goods_sn,$envent,$notes){
        self::init();

        $add = array(
            'goods_sn' => $goods_sn,
            'event' => self::$eventName[$envent],
            'notes' => $notes,
            'operuser' => self::$truename,
            'opertime' => time()
        );

        DB::Add(self::$tablenameLog,$add);
    }

    /**
     * 获得产品操作日志
     * @param string    $goods_sn  产品编码
     * @param int|array $event    日志事件类型
     * @param string $order_by
     * @return array|type
     */
    public static function getNewGoodsLog($goods_sn,$event,$order_by = ''){
        if(is_array($event)){
            $event = self::getEventNameByNumber($event);
            $event = implode("','",$event);
            $where = "goods_sn='$goods_sn' AND event IN('$event') ".$order_by;
        }else{
            $event = self::$eventName[$event];
            $where = "goods_sn='$goods_sn' AND event='$event' ".$order_by;
        }
        $list = DB::Select(self::$tablenameLog,$where);

        if(empty($list)) return array();
        return $list;
    }


    /**
     * 判断父SKU与SKU的开发时间是否一致
     * @param $parent_sku
     * @param $goods_add_date
     * @return bool true|false 一致|不一致
     */
    public static function checkParentAndSubSkuDate($parent_sku,$goods_add_date){

        $parent_sku_info = DB::Find(self::$tablenameParents,"parent_sku='$parent_sku'");
        if(empty($parent_sku_info)) return true;

        if(is_numeric($parent_sku_info['add_time'])){
            $parent_sku_date = date('Y-m-d',$parent_sku_info['add_time']);
        }else{
            $parent_sku_date = date('Y-m-d',strtotime($parent_sku_info['add_time']));
        }

        if(is_numeric($goods_add_date)){
            $goods_add_date = date('Y-m-d',$goods_add_date);
        }else{
            $goods_add_date = date('Y-m-d',strtotime($goods_add_date));
        }
        if($parent_sku_date == $goods_add_date){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 添加一个父SKU
     * @param $condition
     * @return bool
     */
    public static function addParentSku($condition){
        if(empty($condition['parent_sku'])) return false;

        $condition['parent_sku'] = trim($condition['parent_sku']);
        $have = DB::Find(self::$tablenameParents,"parent_sku='".$condition['parent_sku']."'");
        if($have) return true;

        self::init();

        $add['parent_sku'] = $condition['parent_sku'];
        if($condition['add_time']){
            if(is_numeric($condition['add_time'])){
                $add['add_time'] = date('Y-m-d H:i:s',$condition['add_time']);
            }else{
                $add['add_time'] = $condition['add_time'];
            }
        }else{
            $add['add_time'] = date('Y-m-d H:i:s');
        }

        $add['store_id'] = 32;
        $add['add_user'] = self::$truename;

        if(DB::Add(self::$tablenameParents,$add)){
            return true;
        }else{
            return false;
        }
    }



}