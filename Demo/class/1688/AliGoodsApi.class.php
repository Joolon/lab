<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AliBaseApi.class.php';

/**
 * Class Ali1688BaseApi
 * 阿里巴巴 1688批发平台 商品信息获取API
 * @author:zwl
 * @date 2018-03-05
 */
class AliGoodsApi extends AliBaseApi {

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 从商品链接中提取 商品ID
     * @param $productId
     * @return mixed
     */
    public function parseProductIdByLink($productId){
        if(strpos($productId,'http') !== false){
            preg_match("/offer\/[\w]+\.html/",$productId,$out);
            if($out){
                $productId = $out[0];
                $productId = str_replace(array('offer/','.html'),'',$productId);
            }
        }

        return $productId;
    }

    /**
     * 跨境场景获取商品详情
     * @param $productId
     * @return mixed|string
     */
    public function getProductInfo($productId){
        $productId = $this->parseProductIdByLink($productId);

        $urlPath = 'param2/1/com.alibaba.product/alibaba.cross.productInfo/';
        $systemData = array(
            'access_token' => AliAccount::$accessToken,
            'productId' => $productId,
        );
        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;

    }

    /**
     * （获取指定ID的商品）跨境场景获取商品列表
     * @param $productIdList
     * @return mixed|string
     */
    public function getProductList($productIdList){
        $urlPath = 'param2/1/com.alibaba.product/alibaba.cross.productList/';

        $productIdList = json_encode($productIdList);
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
            'productIdList' => $productIdList,
        );

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;

    }

    /**
     * 跨境场景下将商品加入铺货列表
     */
    public function syncProductListPushed($productIdList){
        $urlPath = 'param2/1/com.alibaba.product.push/alibaba.cross.syncProductListPushed/';

        $productIdList = json_encode($productIdList);
        $systemData = array(
            'access_token'  => AliAccount::$accessToken,
            'productIdList' => $productIdList,
        );

        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;
    }


    /**
     * 商品列表的属性 解析、重组
     * @param $skuInfos
     * @return array
     */
    public function parseSkuListBySkuInfos($skuInfos){
        $skuList = array();
        foreach($skuInfos as $value){
            $attributes = $value['attributes'];

            $attrIds = get_array_column($attributes,'attributeID');
            $attrValues = get_array_column($attributes,'attributeValue');
            $attrIdsCombine = implode(':',$attrIds);
            $attrValuesCombine = implode(':',$attrValues);

            unset($value['attributes']);
            $now_sku = $value;
            $now_sku['attrIdsCombine'] = $attrIdsCombine;
            $now_sku['attrValuesCombine'] = $attrValuesCombine;

            $skuList[$now_sku['specId']] =  $now_sku;
        }

        return $skuList;
    }






}