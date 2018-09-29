<?php

/**
 * Class AliDealHelp
 * 阿里巴巴 1688批发平台 数据处理辅助类
 * @author:zwl
 * @date 2018-03-05
 */
class AliDealHelp {
    public static $username = '';

    public static function init()
    {
        global $truename;
        self::$username = $truename;
    }


    /**
     * 操作日志事件类型（1688日志列表的单据类型与此对应）
     * @var array
     */
    public static $eventName = array(
        'SUPPLIER'      => '供应商',// 供应商
        'ORDER_ID'      => '1688订单',// 1688订单
        'PRODUCT'       => '商品规格',
        'PRODUCT_D'     => '商品规格明细',//
        'IO_ORDERSN'    => '采购单编号',//
    );

    /**
     * 1688 批发平台 订单对应的状态
     * @var array
     */
    public static $orderStatus = array(

        // 自定义状态
        'v2createorder'         => '系统新建',
        'v2syncto1688'          => '同步到1688',

        // 1688平台采购单状态
        'waitbuyerpay'          => '等待买家付款',
        'waitsellersend'        => '等待卖家发货',
        'waitlogisticstakein'   => '等待物流公司揽件',
        'waitbuyerreceive'      => '等待买家收货',
        'waitbuyersign'         => '等待买家签收',
        'signinsuccess'         => '买家已签收',
        'confirm_goods'         => '已收货',
        'success'               => '交易成功',
        'cancel'                => '交易取消',
        'terminated'            => '交易终止',
    );

    /**
     * 插入操作日志
     * @param string $order_sn 单据编号
     * @param string $order_type 单据类型
     *  （枚举类型:IO_ORDER_SN.V2采购单编号,ORDER_1688.order_1688源数据表ID,ORDER_ID.1688订单ID,OTHER.其他类型）
     * @param string $event  事件类型
     * @param string $notes  事件描述
     * @param bool $operator 操作人
     */
    public static function addOperatorLog($order_sn,$order_type,$event,$notes,$operator = false){
        self::init();

        $add = array(
            'order_sn' => $order_sn,
            'order_type' => $order_type,
            'event' => $event,
            'notes' => $notes,
            'operuser' => $operator?$operator:self::$username,
            'opertime' => time()
        );

        DB::Add('ali1688_order_log',$add);
    }


    /**
     * 根据组合条件查询 已保存到系统中的供应商信息列表
     * @param $condition
     * ex. $condition = array(
     *          'login_id' => '供应商登录ID',
     *          'user_id' => '供应商user id',
     *          'member_id' => '供应商member id',
     *          'company_name' => '供应商名称',
     *          'partner_id' => 'V2供应商ID',
     *      )
     * @return bool|type
     */
    public static function getAliSupplierInfo($condition){
        $where = '1';
        if(isset($condition['login_id'])) $where .= " AND login_id='{$condition['login_id']}' ";
        if(isset($condition['user_id'])) $where .= " AND user_id='{$condition['user_id']}' ";
        if(isset($condition['member_id'])) $where .= " AND member_id='{$condition['member_id']}' ";
        if(isset($condition['company_name'])) $where .= " AND company_name='{$condition['company_name']}' ";
        if(isset($condition['partner_id'])) $where .= " AND partner_id='{$condition['partner_id']}' ";

        if($where == '1') return false;

        $infoList = DB::Find('ali1688_supplier',$where);
        if(empty($infoList)) return false;
        return $infoList;
    }

    /**
     * 保存1688供应商信息
     * @param $supplierInfo
     * @return bool|int
     */
    public static function saveSupplier($supplierInfo){
        self::init();

        $add_supplier_info = array();

        if(isset($supplierInfo['login_id']) AND $supplierInfo['login_id']){
            $add_supplier_info['login_id'] = trim($supplierInfo['login_id']);
        }
        if(isset($supplierInfo['user_id']) AND $supplierInfo['user_id']){
            $add_supplier_info['user_id'] = trim($supplierInfo['user_id']);
        }
        if(isset($supplierInfo['member_id']) AND $supplierInfo['member_id']){
            $add_supplier_info['member_id'] = trim($supplierInfo['member_id']);
        }
        if(isset($supplierInfo['company_name']) AND $supplierInfo['company_name']){
            $add_supplier_info['company_name'] = trim($supplierInfo['company_name']);
        }
        if(isset($supplierInfo['partner_id']) AND $supplierInfo['partner_id']){
            $add_supplier_info['partner_id'] = trim($supplierInfo['partner_id']);
        }
        if(isset($supplierInfo['category_name']) AND $supplierInfo['category_name']){
            $add_supplier_info['category_name'] = trim($supplierInfo['category_name']);
        }
        if(isset($supplierInfo['supplier_name']) AND $supplierInfo['supplier_name']){
            $add_supplier_info['supplier_name'] = trim($supplierInfo['supplier_name']);
        }
        if(isset($supplierInfo['match_url']) AND $supplierInfo['match_url']){
            $add_supplier_info['match_url'] = trim($supplierInfo['match_url']);
        }
        if(isset($supplierInfo['shop_url']) AND $supplierInfo['shop_url']){
            $add_supplier_info['shop_url'] = trim($supplierInfo['shop_url']);
        }


//        print_r($add_supplier_info);exit;
        $have = self::getAliSupplierInfo($add_supplier_info);
//        var_dump($have);exit;
        if($have){
            $add_supplier_info['updateuser'] = self::$username;
            $add_supplier_info['updatetime'] = date('Y-m-d H:i:s');

            $ali1688_supplier_id =  $have['id'];
            $res = DB::Update('ali1688_supplier',$add_supplier_info,"id={$ali1688_supplier_id}");

        }else{
            $add_supplier_info['adduser'] = self::$username;
            $add_supplier_info['addtime'] = date('Y-m-d H:i:s');

            $res = DB::Add('ali1688_supplier',$add_supplier_info);
            $ali1688_supplier_id = mysql_insert_id();

        }
        if(empty($res) AND $res !== 0) return false;

        $add_supplier_info['title_msg'] = '关联供应商';
        AliDealHelp::addOperatorLog($supplierInfo['user_id'],'SUPPLIER','关联供应商',json_encode($add_supplier_info));
        return $ali1688_supplier_id;
    }


    /**
     * 获取 以保存到系统的产品信息（附带产品多规格明细）
     * @param $product_id
     * @return bool|type
     */
    public static function getAliProductInfo($product_id){
        $where = " product_id='$product_id' ";
        $productInfo = DB::Find('ali1688_products',$where);

        if($productInfo){
            $sku_list = DB::Select('ali1688_products_skuinfos',$where);
            $productInfo['skuinfos'] = $sku_list;
        }

        if(empty($productInfo)) return false;
        return $productInfo;
    }

    /**
     * 获取 指定产品下 指定规格的明细
     * @param string $product_id
     * @param string $spec_id
     * @param string $goods_sn
     * @return bool|type
     */
    public static function getAliProductSpecInfo($product_id = '',$spec_id = '',$goods_sn = ''){
        $where = '1';
        if($product_id) $where .= " AND product_id='$product_id'";
        if($spec_id)    $where .= " AND spec_id='$spec_id'";
        if($goods_sn)   $where .= " AND goods_sn='$goods_sn'";
        $sku_spec = DB::Find('ali1688_products_skuinfos',$where);

        if(empty($sku_spec)) return false;
        return $sku_spec;
    }


    /**
     * 保存 1688的产品信息
     * @param $productInfo
     * @return bool|int
     */
    public static function saveAliProductInfo($productInfo){
        self::init();

        $add_product_info = array();
        $add_product_info['product_id'] = isset($productInfo['productID'])?$productInfo['productID']:'';
        $add_product_info['product_type'] = isset($productInfo['productType'])?$productInfo['productType']:'';
        $add_product_info['category_id'] = isset($productInfo['categoryID'])?$productInfo['categoryID']:'';
        $add_product_info['attributes'] = isset($productInfo['attributes'])?json_encode($productInfo['attributes']):'';
        $add_product_info['group_id'] = isset($productInfo['groupID'])?json_encode($productInfo['groupID']):'';
        $add_product_info['status'] = isset($productInfo['status'])?$productInfo['status']:'';
        $add_product_info['subject'] = isset($productInfo['subject'])?$productInfo['subject']:'';
        $add_product_info['description'] = isset($productInfo['description'])?mysql_escape_string($productInfo['description']):'';
        $add_product_info['language'] = isset($productInfo['language'])?$productInfo['language']:'';
        $add_product_info['period_of_validity'] = isset($productInfo['periodOfValidity'])?$productInfo['periodOfValidity']:'';
        $add_product_info['biz_type'] = isset($productInfo['bizType'])?$productInfo['bizType']:'';
        $add_product_info['biz_type'] = isset($productInfo['pictureAuth'])?$productInfo['pictureAuth']:'';
        $add_product_info['image'] = isset($productInfo['image'])?json_encode($productInfo['image']):'';
        $add_product_info['sale_info'] = isset($productInfo['saleInfo'])?json_encode($productInfo['saleInfo']):'';
        $add_product_info['unit'] = isset($productInfo['saleInfo']['unit'])?$productInfo['saleInfo']['unit']:'';
        $add_product_info['shipping_info'] = isset($productInfo['shippingInfo'])?json_encode($productInfo['shippingInfo']):'';
        $add_product_info['supplier_user_id'] = isset($productInfo['supplierUserId'])?$productInfo['supplierUserId']:'';
        $add_product_info['supplier_login_id'] = isset($productInfo['supplierLoginId'])?$productInfo['supplierLoginId']:'';
        $add_product_info['category_name'] = isset($productInfo['categoryName'])?$productInfo['categoryName']:'';
        $add_product_info['cross_border_offer'] = isset($productInfo['crossBorderOffer'])?$productInfo['crossBorderOffer']:'';
        $add_product_info['quality_level'] = isset($productInfo['qualityLevel'])?$productInfo['qualityLevel']:'';
        $add_product_info['match_url'] = isset($productInfo['match_url'])?$productInfo['match_url']:'';// 匹配商品时的URL


        $skuInfos = isset($productInfo['skuInfos'])?$productInfo['skuInfos']:array();

//        print_r($skuInfos);exit;
        $have = self::getAliProductInfo($add_product_info['product_id']);
        if($have){
            $ali_product_id = $have['id'];
            return $ali_product_id;// 暂时不更新SKU了

            $add_product_info['updateuser'] = self::$username;
            $add_product_info['updatetime'] = date('Y-m-d H:i:s');

            $res = DB::Update('ali1688_products',$add_product_info,"id={$ali_product_id}");
        }else{
            $add_product_info['adduser'] = self::$username;
            $add_product_info['addtime'] = date('Y-m-d H:i:s');

            $res = DB::Add('ali1688_products',$add_product_info);
            $ali_product_id = mysql_insert_id();
        }

        if(empty($res) AND $res !== 0){
            return false;
        }else{
            if(empty($skuInfos)){// 没有多属性则自己作为多属性
                if($productInfo['saleInfo']){
                    $priceRanges = $productInfo['saleInfo']['priceRanges'];
                    $priceRanges = arr_sort($priceRanges,'price','asc');
                    $first_price = current($priceRanges);
                    $price = $first_price['price'];
                    $amountOnSale = $productInfo['saleInfo']['amountOnSale'];
                }else{
                    $amountOnSale = 0;
                    $price = 0;
                }
                $val_sku['price'] = $price;
                $val_sku['amountOnSale'] = $amountOnSale;
                $val_sku['skuCode']     = $add_product_info['product_id'];
                $val_sku['skuId']       = $add_product_info['product_id'];
                $val_sku['specId']      = $add_product_info['product_id'];
                $skuInfos = array($val_sku);
            }
            if($skuInfos){
                foreach($skuInfos as $val_sku){
                    self::saveAliProductSku($add_product_info['product_id'],$val_sku,$add_product_info['supplier_user_id'],$add_product_info['supplier_login_id']);
                }
            }
        }

        return $ali_product_id;
    }

    /**
     * 对 一键下单到1688平台的订单做数据备份
     * @param $io_ordersn
     * @param $aliOrderInfo
     * @return bool|int
     */
    public static function save1688OrderInfo($io_ordersn,$aliOrderInfo){
        self::init();

        $orderInfoMain = array();
        $orderInfoMain['io_ordersn'] = $io_ordersn;
        $orderInfoMain['flow'] = $aliOrderInfo['flow'];
        $orderInfoMain['message'] = $aliOrderInfo['message'];
        $orderInfoMain['address_param'] = $aliOrderInfo['addressParam'];
        $orderInfoMain['cargo_param_list'] = $aliOrderInfo['cargoParamList'];
        $orderInfoMain['invoice_param'] = $aliOrderInfo['invoiceParam'];
        $orderInfoMain['trade_type'] = $aliOrderInfo['tradeType'];
        $orderInfoMain['shop_promotion_id'] = $aliOrderInfo['shopPromotionId'];
        $orderInfoMain['adduser'] = self::$username;
        $orderInfoMain['addtime'] = date('Y-m-d H:i:s');
        $orderInfoMain['status'] = 'v2createorder';

//        print_r($orderInfoMain);exit;
        $res = DB::Add('ali1688_order',$orderInfoMain);
        if($res){
            $id = mysql_insert_id();
            return $id;
        }else{
            return false;
        }
    }

    public static function getAliProductSkuList($condition){
        $where = '1';
        if(isset($condition['product_id'])){
            $product_id = $condition['product_id'];
            $where .= " AND product_id='$product_id' ";
        }
        if(isset($condition['spec_id'])){
            $product_sku_spec_id = $condition['spec_id'];
            $where .= " AND spec_id='$product_sku_spec_id' ";
        }
        if(isset($condition['goods_sn'])){
            if($condition['goods_sn']){
                $goods_sn = $condition['goods_sn'];
                $where .= " AND goods_sn='$goods_sn' ";
            }else{
                $where .= " AND (goods_sn='' OR goods_sn IS NULL) ";
            }
        }
        if(isset($condition['user_id'])){
            $user_id = $condition['user_id'];
            $where .= " AND supplier_user_id='$user_id' ";
        }

        $skuInfo = DB::Select('ali1688_products_skuinfos',$where);

        if(empty($skuInfo)) return false;
        return $skuInfo;
    }

    /**
     * 获取 1688买家收货地址列表（根据姓名递增排序，指定的用户名放在顶部）
     * @param $username
     * @return array|type
     */
    public static function getBuyerAddressList($username = ''){
        $where = '1';
        $where .= " ORDER BY convert(full_name using gbk) asc ";

        $list = DB::Select('ali1688_buyer_address',$where);

        if(empty($list)) return array();
        if($username){
            $listTmp = array();
            $nowUser = array();
            foreach($list as $l_val){
                if($l_val['full_name'] == $username){
                    $nowUser[] = $l_val;
                }else{
                    $listTmp[] = $l_val;
                }
            }
            $list = array_merge($nowUser,$listTmp);
        }

        return $list;
    }

    /**
     * 保存 1688商品多规格明细SKU的信息
     * @param $product_id
     * @param $skuInfo
     * @param $supplier_user_id
     * @param $supplier_login_id
     * @return bool|int
     */
    public static function saveAliProductSku($product_id,$skuInfo,$supplier_user_id,$supplier_login_id){
        if(empty($product_id) OR empty($skuInfo['specId'])) return false;

        $have = self::getAliProductSkuList(array('product_id' => $product_id,'spec_id' => $skuInfo['specId']));

        $attributes         = $skuInfo['attributes'];
        $attrIds            = get_array_column($attributes,'attributeID');
        $attrValues         = get_array_column($attributes,'attributeValue');
        $attrIdsCombine     = implode(':',$attrIds);
        $attrValuesCombine  = implode(':',$attrValues);

        $now_sku_info = array();
        $now_sku_info['sku_id']                 = isset($skuInfo['skuId'])?$skuInfo['skuId']:'';
        $now_sku_info['sku_code']               = isset($skuInfo['skuCode'])?$skuInfo['skuCode']:'';
        $now_sku_info['price']                  = isset($skuInfo['price'])?$skuInfo['price']:'';
        $now_sku_info['amount_on_sale']         = isset($skuInfo['amountOnSale'])?$skuInfo['amountOnSale']:'';
        $now_sku_info['cargo_number']           = isset($skuInfo['cargoNumber'])?$skuInfo['cargoNumber']:'';
        $now_sku_info['attributes']             = json_encode($attributes);
        $now_sku_info['retail_price']           = isset($skuInfo['retailPrice'])?json_encode($skuInfo['retailPrice']):'';
        $now_sku_info['price_range']            = isset($skuInfo['priceRange'])?json_encode($skuInfo['priceRange']):'';
        $now_sku_info['attr_ids_combine']       = $attrIdsCombine;
        $now_sku_info['attr_values_combine']    = $attrValuesCombine;
        $now_sku_info['supplier_user_id']       = $supplier_user_id;
        $now_sku_info['supplier_login_id']      = $supplier_login_id;

//        print_r($now_sku_info);exit;
        if($have){
            $ali_product_sku_id = $have[0]['id'];
            $res = DB::Update('ali1688_products_skuinfos',$now_sku_info,"id='{$ali_product_sku_id}'");
        }else{
            $now_sku_info['product_id'] = $product_id;
            $now_sku_info['spec_id'] = $skuInfo['specId'];

            $res = DB::Add('ali1688_products_skuinfos',$now_sku_info);
            $ali_product_sku_id = mysql_insert_id();
        }

        if(empty($res) AND $res !== 0) return false;
        return $ali_product_sku_id;
    }

    /**
     * 关联 商品ID与系统产品编码
     * @param $product_id
     * @param $product_spec_id
     * @param $goods_sn
     * @param $auto_create_spec
     * @return array
     */
    public static function combineGoodsAndProduct($product_id,$product_spec_id,$goods_sn,$auto_create_spec = false){
        self::init();

        $goods_have_combined = self::getAliProductSkuList(array('product_id' => $product_id,'goods_sn' => $goods_sn));
        if($goods_have_combined){
            $result = array('code' => 'ERROR','msg' => '此商品下的其他商品规格已经关联该产品编码（重复关联）');
            return $result;
        }

        $have = self::getAliProductSkuList(array('product_id' => $product_id,'spec_id' => $product_spec_id));
        if($have){
            $res = false;
            // 是否存在未关联的此商品规格
            $have_combined = self::getAliProductSkuList(array('product_id' => $product_id,'spec_id' => $product_spec_id,'goods_sn' => ''));
//            print_r($have_combined);exit;
            $match_update = array('goods_sn' => $goods_sn,'match_user' => self::$username);
            if($have_combined AND empty($have_combined[0]['goods_sn']) ){
                $ali_product_sku_id = $have_combined[0]['id'];
                $res = DB::Update('ali1688_products_skuinfos',$match_update,"id={$ali_product_sku_id}");
            }else if($auto_create_spec){
                if(self::createOneSpecBySpecId($product_id,$product_spec_id)){
                    $have_combined = self::getAliProductSkuList(array('product_id' => $product_id,'spec_id' => $product_spec_id,'goods_sn' => ''));
                    $ali_product_sku_id = $have_combined[0]['id'];
                    $res = DB::Update('ali1688_products_skuinfos',$match_update,"id={$ali_product_sku_id}");
                }
            }
            if(empty($res) AND $res !== 0){
                $result = array('code' => 'ERROR','msg' => '此商品规格已被关联');
                return $result;
            }else{
                $result = array('code' => 'SUCCESS','msg' => '操作成功');
                DB::Update('ebay_goods',array('combine_1688' => 1),"goods_sn='$goods_sn'");// 更新产品已关联1688
                return $result;
            }
        }else{
            $result = array('code' => 'ERROR','msg' => '此商品规格不存在');
            return $result;
        }
    }

    /**
     * 复制一个指定商品ID 指定商品规格的规格（为了可以一个规格对应多个 V2系统的产品）
     * @param $product_id  商品ID
     * @param $product_spec_id 商品规格
     * @return bool
     */
    public static function createOneSpecBySpecId($product_id,$product_spec_id){

        $sqlInsert = "INSERT INTO ali1688_products_skuinfos(product_id,spec_id,sku_id,sku_code,attr_ids_combine,attr_values_combine,attributes,
                    cargo_number,amount_on_sale,retail_price,price,price_range,online_status,supplier_user_id,supplier_login_id,supplier_member_id)
                SELECT product_id,spec_id,sku_id,sku_code,attr_ids_combine,attr_values_combine,attributes,
                    cargo_number,amount_on_sale,retail_price,price,price_range,online_status,supplier_user_id,supplier_login_id,supplier_member_id
                FROM ali1688_products_skuinfos WHERE product_id='$product_id' AND spec_id='$product_spec_id' LIMIT 1 ";

        if(DB::QuerySQL($sqlInsert)){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 根据采购单编号获得可以下单的1688供应商列表（验证SKU是否有关联）
     * @param $io_ordersn
     * @return array|bool
     */
    public static function getAblePlaceOrderSupplierOn1688($io_ordersn){
        $puOrderInfo = PurchaseOrder::getPurchaseOrder(array('io_ordersn' => $io_ordersn),true);// 获取采购单

        $flag                       = true;// 假设匹配到了
        $able_place_order_supplier  = array();// SKU关联的供应商
        $supplier_list              = array();// 所有SKU关联的所有供应商
        $supplier_list_amount       = array();// 缓存供应商下SKU的可售数量
        $supplier_list_price        = array();// 缓存供应商下SKU的单价


        // 判断采购单所有SKU是否有已经关联1688的供应商
        foreach($puOrderInfo['sku_details'] as $sku_val){
            $goods_sn       = trim($sku_val['goods_sn']);

            // 获取SKU可以下单的供应商名称
            $supplierList   = self::getAli1688Supplier($goods_sn);
            if($supplierList){// 当前SKU没有可下单的供应商
                foreach($supplierList as $sup_sku_val){
                    $company_name                   = $sup_sku_val['company_name'];
                    $supplier_list[$company_name]   = $company_name;

                    // 已关联的供应商
                    $able_place_order_supplier[$goods_sn][$company_name]    = $company_name;
                    // 缓存价格和数量
                    $supplier_list_price[$goods_sn][$company_name]          = $sup_sku_val['price'];
                    $supplier_list_amount[$goods_sn][$company_name]         = $sup_sku_val['amount_on_sale'];
                }
            }else{
                $flag = false;// 出现没有关联供应商
                break;
            }
        }


        // 若存在SKU匹配不到供应商则 直接返回失败
        if($flag === false) return false;

        $supplier_list      = array_unique($supplier_list);// 去除重复值
        $supplier_list_tmp  = $supplier_list;
//        print_r($supplier_list_price);exit;

        // 获取可下单 1688供应商列表
        // 验证每个SKU的供应商，若SKU没有关联到该供应商则从缓存列表中剔除，能留下来的则是所有SKU共有的供应商
        foreach($puOrderInfo['sku_details'] as $sku_val){
            $goods_sn       = trim($sku_val['goods_sn']);
            foreach($supplier_list as $sup_key => $sup_name){
                if(!isset($able_place_order_supplier[$goods_sn][$sup_name])){
                    unset($supplier_list_tmp[$sup_name]);
                }
            }
        }

//        print_r($supplier_list_price);exit;
        // 可下单的 1688供应商列表
        unset($supplier_list);
        $supplier_list_tmp = array_diff($supplier_list_tmp,array(''));// 去除为空的元素

        // 获取可下单的1688供应商 的SKU 售价 列表（去除 除可下单供应商之外的供应商）
        foreach($supplier_list_price as &$price_val){
            foreach($price_val as $sup_key => $pri_val){
                if(!isset($supplier_list_tmp[$sup_key])) unset($price_val[$sup_key]);
            }
        }
        // 获取可下单的1688供应商 的SKU 可售数量 列表（去除 除可下单供应商之外的供应商）
        foreach($supplier_list_amount as &$amount_val){
            foreach($amount_val as $sup_key => $amo_val){
                if(!isset($supplier_list_tmp[$sup_key])) unset($amount_val[$sup_key]);
            }
        }

        // 统计供应商的可售数量、单价的权值明细
        $supplier_list_priority = array();

        foreach($puOrderInfo['sku_details'] as $sku_val){
            $goods_sn       = trim($sku_val['goods_sn']);

            // SKU已关联供应商的单价的最大值与最小值的供应商
            $now_list_price = $supplier_list_price[$goods_sn];
            $sup_max_price = array_search(max($now_list_price), $now_list_price);
            $sup_min_price = array_search(min($now_list_price), $now_list_price);

            // SKU已关联供应商的可售数量的最大值与最小值的供应商
            $now_list_amount = $supplier_list_amount[$goods_sn];
            $sup_max_amount = array_search(max($now_list_amount), $now_list_amount);
            $sup_min_amount = array_search(min($now_list_amount), $now_list_amount);

            // 价格权值：价格越大分值越小，反之亦然。
            $supplier_list_priority[$sup_max_price]['price'][$goods_sn] = 1;
            $supplier_list_priority[$sup_min_price]['price'][$goods_sn] = 3;

            // 数量权值：数量越大分值越大，反之亦然。
            $supplier_list_priority[$sup_max_amount]['amount'][$goods_sn] = 3;
            $supplier_list_priority[$sup_min_amount]['amount'][$goods_sn] = 1;

            // 其他非最大值最小值对应的供应商 设置默认值为2。
            foreach($supplier_list_tmp as $vv){
                if(!isset($supplier_list_priority[$vv]['amount'][$goods_sn]))  $supplier_list_priority[$vv]['amount'][$goods_sn] = 2;
                if(!isset($supplier_list_priority[$vv]['price'][$goods_sn]))  $supplier_list_priority[$vv]['price'][$goods_sn] = 2;
            }
        }

        // 对 供应商权值明细进行汇总
        foreach($supplier_list_priority as $sup_key => &$pri_val){
            $ali_supplier1688_info  = self::getAliSupplierInfo(array('company_name' => $sup_key));
            $partner_info           = Partner::getPartner($ali_supplier1688_info['partner_id']);

            $pri_val['area_score']      = intval($partner_info['area_score']);
            $pri_val['priority_score']  = intval($partner_info['priority_score']);
            $pri_val['sku_amount']      = array_sum($supplier_list_priority[$sup_key]['amount']);
            $pri_val['sku_price']       = array_sum($supplier_list_priority[$sup_key]['price']);

            $pri_val['total_score']     = array_sum($pri_val);
            unset($pri_val['price'],$pri_val['amount']);
        }

        // 对汇总后的供应商按总权值降序排序
        $supplier_list_priority = arr_sort($supplier_list_priority,'total_score','desc');

        if(empty($supplier_list_priority)) return false;

        // 保存匹配结果
        self::addOperatorLog($io_ordersn,'IO_ORDERSN','匹配并计算供应商权值明细及汇总(保存匹配结果)',json_encode($supplier_list_priority));

        return $supplier_list_priority;

    }

    /**
     * 获取SKU已经关联的商品信息和供应商信息
     * @param $goods_sn
     * @return array|bool|resource
     */
    public static function getAli1688Supplier($goods_sn){
        global $dbcon,$truename;

        // 三者皆不能缺少
        $sqlSel = "SELECT p_s.goods_sn,p.product_id,p_s.spec_id,p_s.sku_id,p_s.amount_on_sale,p_s.price,p.supplier_user_id,p.supplier_login_id,s.member_id,s.company_name
            FROM ali1688_products_skuinfos AS p_s
            INNER JOIN ali1688_products AS p ON p.product_id=p_s.product_id
            INNER JOIN ali1688_supplier AS s ON p.supplier_user_id=s.user_id
            WHERE p_s.goods_sn='$goods_sn' ";

        $supplierList = $dbcon->query($sqlSel);
        $supplierList = $dbcon->getResultArray($supplierList);

        if(empty($supplierList)) return false;
        return $supplierList;
    }

    /**
     * 获取 备份的1688订单信息
     * @param $orderId
     * @return bool|type
     */
    public static function getAli1688OrderInfo($orderId){
        $where = "order_id='$orderId' ";
        $orderInfo = DB::Find('ali1688_order',$where);

        if(empty($orderInfo)) return false;
        return $orderInfo;
    }

    /**
     * 查询 一个 买家收货地址（优先查询默认收货地址）
     * @param string $addressId
     * @return bool|type
     */
    public static function getAliBuyerAddress($addressId = ''){
        $where = '1';
        if($addressId){
            $where .= " AND address_id='$addressId' ";
        }else{
            $where .= " AND is_default=1 ";
        }

        $address = DB::Find('ali1688_buyer_address',$where);
        if(empty($address)){
            $address = DB::Find('ali1688_buyer_address','1');
        }

        if(empty($address)) return false;
        return $address;
    }




}