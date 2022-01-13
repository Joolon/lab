<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'AliBaseApi.class.php';

/**
 * Class Ali1688BaseApi
 * 阿里巴巴 采购订单对接下单操作API
 * @author:zwl
 * @date 2018-03-05
 */
class AliSupplierApi extends AliBaseApi {


    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  获取店铺 Link 里面的店铺域名
     * @param $domain
     * @return string
     */
    public function parseDomainByLink($domain){

        if(strpos($domain,'http') !== false){// 若是HTTP的Link则获取Link里面的域名
            $param = parse_url($domain);
            $domain = $param["host"];
        }

        return $domain;
    }

    /**
     * 根据旺铺域名或店铺链接 获取旺铺的供应商公司基本信息
     * @param string $domain
     * @return array
     */
    public function getUserInfo($domain){
        $domain = $this->parseDomainByLink($domain);

        $urlPath = 'param2/1/com.alibaba.trade/alibaba.member.getUserInfo/';
        $systemData = array(
            'access_token' => AliAccount::$accessToken,
            'domin' => $domain,
        );
        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;

    }

    /**
     * 获取非授权用户的基本信息
     * @param string $loginId
     * @param string $domain
     * @return mixed|string
     */
    public function getAccountAgentBasic($loginId = '',$domain = ''){
        $domain = $this->parseDomainByLink($domain);

        $urlPath = 'param2/1/com.alibaba.account/alibaba.account.agent.basic/';
        $systemData = array(
            'access_token' => AliAccount::$accessToken,
            'loginId' => $loginId,
            'domain' => $domain,
        );
        $this->setSignature($urlPath,AliAccount::$appKey,AliAccount::$appSecret,$systemData);
        $requestUrl = $this->getRequestUrl($urlPath,AliAccount::$appKey,$systemData);

        $response = $this->curlPost($requestUrl);

        return $response;

    }




}