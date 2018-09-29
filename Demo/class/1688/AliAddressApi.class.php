<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AliBaseApi.class.php';

/**
 * Class AliAddressApi
 * 阿里巴巴 1688批发采购平台 用户地址操作API
 * @author:zwl
 * @date 2018-03-05
 */
class AliAddressApi extends AliBaseApi {

    /**
     * 获取 用户的地址信息（1688批发采购平台后台保存的买家收货地址）
     * @return mixed|string
     */
    public function getReceiveAddress(){
        $urlPath = 'param2/1/com.alibaba.trade/alibaba.trade.receiveAddress.get/';

        $systemData = array(
            'access_token'  => AliAccount::$accessToken
        );
        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;

    }




}