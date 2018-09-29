<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AliBaseApi.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AliAddressApi.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AliSupplierApi.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AliDealHelp.class.php';
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'PurchaseOrder.class.php';

/**
 * Class Ali1688BaseApi
 * 阿里巴巴 采购订单对接操作API
 * @author:zwl
 * @date 2018-03-05
 */
class AliOrderApi extends AliBaseApi {

    private $_webSite = '1688';// 属于国际站（alibaba）还是1688网站（1688）

    public function get1688OrderInfo($orderId){
        $urlPath = 'param2/1/com.alibaba.trade/alibaba.trade.get.buyerView/';
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
            'orderId'       => $orderId,
            // 查询结果中包含的域，GuaranteesTerms：保障条款，NativeLogistics：物流信息，RateDetail：评价详情，OrderInvoice：发票信息。
            'includeFields' => 'GuaranteesTerms,NativeLogistics,OrderInvoice',
            'webSite'       => $this->_webSite,

        );

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);
        $response = $this->curlPost($requestUrl);

        return $response;

    }


    /**
     * API https://open.1688.com/api/api.htm?spm=0.0.0.0.u90fnW&ns=com.alibaba.trade&n=alibaba.trade.getBuyerOrderList&v=1
     * @param $condition
     * @return mixed|string
     */
    public function get1688OrderInfoList($condition){
        $params = array();
        if(isset($condition['bizTypes'])){
            $params['bizTypes'] = json_encode($condition['bizTypes']);
        }
        if(isset($condition['createStartTime'])){
            $params['createStartTime'] = $condition['createStartTime'];
        }
        if(isset($condition['createEndTime'])){
            $params['createEndTime'] = $condition['createEndTime'];
        }

        if(isset($condition['isHis'])){
            $params['isHis'] = boolval($params['isHis']);
        }

        if(isset($condition['modifyStartTime'])){
            $params['modifyStartTime'] = $condition['modifyStartTime'];
        }
        if(isset($condition['modifyEndTime'])){
            $params['modifyEndTime'] = $condition['createEndTime'];
        }

        if(isset($condition['orderStatus'])){
            $params['orderStatus'] = $condition['orderStatus'];
        }
        if(isset($condition['page'])){
            $params['page'] = $condition['page'];
        }
        if(isset($condition['pageSize'])){
            $params['pageSize'] = $condition['pageSize'];
        }
        if(isset($condition['refundStatus'])){
            $params['refundStatus'] = $condition['refundStatus'];
        }
        if(isset($condition['sellerMemberId'])){
            $params['sellerMemberId'] = $condition['sellerMemberId'];
        }
        if(isset($condition['sellerRateStatus'])){
            $params['sellerRateStatus'] = $condition['sellerRateStatus'];
        }
        if(isset($condition['tradeType'])){
            $params['tradeType'] = $condition['tradeType'];
        }
        if(isset($condition['productName'])){
            $params['productName'] = $condition['productName'];
        }
        if(isset($condition['needBuyerAddressAndPhone'])){
            $params['needBuyerAddressAndPhone'] = $condition['needBuyerAddressAndPhone'];
        }
        if(isset($condition['needMemoInfo'])){
            $params['needMemoInfo'] = $condition['needMemoInfo'];
        }

//        print_r($params);exit;

        $urlPath = 'param2/1/com.alibaba.trade/alibaba.trade.getBuyerOrderList/';
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
        );
        $systemData = $systemData + $params;

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);
        $response = $this->curlPost($requestUrl);

        return $response;

    }

    /**
     * V2系统订单 转换 成 1688API下单请求数据
     * @param $io_ordersn
     * @return array
     */
    public function convertAliOrder($io_ordersn){
        $orderInfo = PurchaseOrder::getPurchaseOrder(array('io_ordersn' => $io_ordersn),true);
        $ali_supplier_name  = $orderInfo['ali_supplier_name'];
        $aliSupplierInfo = AliDealHelp::getAliSupplierInfo(array('company_name' => $ali_supplier_name));
        $ali_sullpier_user_id = $aliSupplierInfo['user_id'];

//        print_r($orderInfo);exit;
        $aliOrderInfo = array();
        $aliOrderInfo['flow'] = 'general';
        //$aliOrderInfo['message'] = '货里请放发货清单，清单上注明订单号，写上SKU，谢谢！';
        $aliOrderInfo['message'] = '发货时请填写正确的快递（或物流）单号，标注好重量在外箱，请提供发货清单，备注货号：没有清单或货号（切记）仓库，会拒收，手写的也行，谢谢配合与支持。不要发安能京东，全一，速腾，运通，平安达，宅急送，源安达,全峰，国通，中铁丰通快递';

        $caigou_user    = $orderInfo['io_purchaseuser'];
        $addressId      = $orderInfo['1688addressid'];
        $sku            = $orderInfo['sku_details'][0]['goods_sn'];
        $tagged         = "$caigou_user [$sku]";

        $fixedAddress = AliDealHelp::getAliBuyerAddress($addressId);

        // 重设地址信息(如果设置了 addressId 则以addressId对应的地址)
        $receiveAddress['fullName'] = $fixedAddress['full_name'];
        $receiveAddress['mobile'] = $fixedAddress['mobile_phone'];
        $receiveAddress['phone'] = $fixedAddress['phone'];
        $receiveAddress['postCode'] = $fixedAddress['post'];
        $address_code_text = $fixedAddress['address_code_text'];
        $shengShiQu = explode(' ',$address_code_text);// 省市区

        $receiveAddress['cityText'] = $shengShiQu[1];
        $receiveAddress['provinceText'] = $shengShiQu[0];
        $receiveAddress['areaText'] = $shengShiQu[2];
        $receiveAddress['townText'] = $fixedAddress['town_name'];
        $receiveAddress['address'] = $fixedAddress['address']."($tagged)";// 添加采购员标记
        $receiveAddress['districtCode'] = $fixedAddress['address_code'];

        $cargoParamList = array();
        foreach($orderInfo['sku_details'] as $val_sku){
            $aliProductSkuInfo = AliDealHelp::getAliProductSkuList(array('goods_sn' => $val_sku['goods_sn'],'user_id' => $ali_sullpier_user_id));
//            print_r($aliProductSkuInfo);exit;
            $aliProductSkuInfo = $aliProductSkuInfo[0];

            $skuInfo['offerId']     = $aliProductSkuInfo['product_id'];
            if(trim($aliProductSkuInfo['product_id']) != trim($aliProductSkuInfo['spec_id'])){// 两者相等则为单属性产品
                $skuInfo['specId']      = $aliProductSkuInfo['spec_id'];
            }
            $skuInfo['quantity']    = $val_sku['goods_count'];
            $cargoParamList[] = $skuInfo;
        }

        $aliOrderInfo['addressParam'] = json_encode($receiveAddress);
        $aliOrderInfo['cargoParamList'] = json_encode($cargoParamList);
        $invoiceParam = array(
            'invoiceType' => 0
        );
        $aliOrderInfo['invoiceParam'] = json_encode($invoiceParam);


        $aliOrderInfo['tradeType'] = 'alipay';

        return $aliOrderInfo;
    }

    /**
     * 创建订单前预览数据接口（只是用来验证订单是否满足要求，不会创建订单，可以用来获取优惠券ID等）
     * @param $aliOrderInfo
     * @return mixed|string
     */
    public function createOrderPreviewOn1688($aliOrderInfo){
        $urlPath = 'param2/1/com.alibaba.trade/alibaba.createOrder.preview/';

        $systemData = array(
            'access_token'  => AliAccount::$accessToken
        );
        $systemData = $systemData + $aliOrderInfo;
        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);
        $response   = $this->curlPost($requestUrl);
//            print_r($response);exit;

        return $response;
    }

    /**
     * 在1688平台上创建一个采购订单
     * @param $aliOrderInfo
     * @return mixed|string
     */
    public function createOrderOn1688($aliOrderInfo){
        if(isset($aliOrderInfo['invoiceParam'])){// 不传发票类型了（需要发票的其他信息）
            unset($aliOrderInfo['invoiceParam']);
        }
        if(true){
            $urlPath = 'param2/1/com.alibaba.trade/alibaba.trade.createCrossOrder/';

            $systemData = array(
                'access_token'  => AliAccount::$accessToken
            );
            $systemData = $systemData + $aliOrderInfo;
            $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
            $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);
            $response   = $this->curlPost($requestUrl);

        }else{
            $response['success'] = false;
            $response['message'] =  '（V2）订单转换失败[保存订单记录时错误]';
        }

        return $response;
    }

    /**
     * 取消1688平台上的订单
     * @param $orderId
     * @param string $cancelReason
     * @return mixed|string
     */
    public function cancelOrderOn1688($orderId,$cancelReason = '取消订单'){
        $urlPath = 'param2/1/com.alibaba.trade/alibaba.trade.cancel/';
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
            'webSite'       => $this->_webSite,
            'tradeID'       => $orderId,
            'cancelReason'  => $cancelReason,
            'remark'        => '..取消订单..'
        );

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;

    }

    /**
     * 获取1688平台采购订单的付款地址
     * @param $orderId
     * @return mixed|string
     */
    public function payUrlOrderOn1688($orderId){
        $urlPath = 'param2/1/com.alibaba.trade/alibaba.alipay.url.get/';
        $orderIdList = json_encode(array($orderId));
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
            'orderIdList'   => $orderIdList,
        );
//        print_r($systemData);exit;

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;

    }

    /**
     * 获取交易订单的物流信息(买家视角)
     * @param $orderId
     * @return mixed|string
     */
    public function getLogisticsInfo($orderId){
        $urlPath = 'param2/1/com.alibaba.logistics/alibaba.trade.getLogisticsInfos.buyerView/';
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
            'orderId'       => $orderId,
            'fields'        => '',
            'webSite'       => $this->_webSite,

        );

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);
        $response = $this->curlPost($requestUrl);

        return $response;
    }

    /**
     * 获取交易订单的物流跟踪信息(买家视角)
     * @param string $orderId
     * @param string $logisticsId
     * @return mixed|string
     */
    public function getLogisticsTraceInfo($orderId = '',$logisticsId = ''){
        $urlPath = 'param2/1/com.alibaba.logistics/alibaba.trade.getLogisticsTraceInfo.buyerView/';
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
            'orderId'       => $orderId,
            'logisticsId'   => $logisticsId,
            'webSite'       => $this->_webSite,
        );

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);
        $response = $this->curlPost($requestUrl);

        return $response;

    }







}