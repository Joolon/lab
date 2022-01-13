<?php
include_once dirname(__FILE__).'/EUBLabel.class.php';
include_once dirname(dirname(__FILE__)). '/help/PDF.class.php';
/*
 * @name  云途物流操作类
 * @add time  2017-05-04  tian
 */

class YuntuApi {

    private $userCode = "C12673";
    private $apiSecret = "DNZGbvGGsjk=";
    private $url;
    private $token;
    private $postHeader;
    private $requestData;

    function __construct() {
        $this->token = base64_encode($this->userCode . "&" . $this->apiSecret);
        $this->postHeader = array('Content-Type:application/json;charset=UTF-8', 'Accept:text/json;', 'Accept-Language:zh-cn', 'Authorization: Basic ' . $this->token);
    }

    /*
     * @name 获取所有运输方式
     * @param1[option] 国家简码
     * @return array
     */

    function getShippingCarrier($ebay_couny = '') {
        $url = 'http://api.yunexpress.com/LMS.API/api/lms/Get';
        $res = $this->curlGetSend($url);
        return (json_decode($res, true));
    }

    /*
     * @name 获取物流渠道code编码
     * @param1 渠道名称
     * @return array
     */

    function getShippingCarrierCode($ebay_carrier) {
        switch ($ebay_carrier) {
            case 'CN小包' :
                $shippingCode = 'CNDWA';
                break;
            case '中华邮政小包' :
                $shippingCode = 'TWYZA';
                break;
            case '中华邮政小包挂号' :
                $shippingCode = 'TWYZR';
                break;
            case '中美专线' :
                $shippingCode = 'USZXR';
                break;
            case '云途中欧专线挂号' :
                $shippingCode = 'EUDDP';
                break;
            case '云途比邮小包' :
                $shippingCode = 'BPA';
                break;
            case '云途加拿大专线' :
                $shippingCode = 'CAZXR';
                break;
            case '云途中澳专线' :
                $shippingCode = 'AUSP';
                break;
            case '云途中英专线' :
                $shippingCode = 'GBZXR';
                break;
            case '云途英国专线平邮' :
                $shippingCode = 'GBRYMA';
                break;
            case '云途法国专线挂号' :
                $shippingCode = 'FRZXR';
                break;
            case '德国专线挂号' :
                $shippingCode = 'DEZXR';
                break;
        }
    }
    /*
     * @name 取回跟踪号
     * @param1 客户订单号
     * @return array
     */
    function getTracknumber($ebay_id) {
        $url = 'http://api.yunexpress.com/LMS.API/api/WayBill/GetTrackNumber?orderId=fzc' . $ebay_id;
        $res = $this->curlGetSend($url);
        $res = json_decode($res, true);
        return $res;
    }

    //curl get request
    private function curlGetSend($url,$method='') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->postHeader);
        if($method=='post'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestData);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    /*
     * @name 处理云途福州小包 波多黎各买家国家字段填美国
     * @param1 订单号
     * @return 波多黎各国家简称与全程
     */
    function setClientCountry() {
        $countryArr = array('ebay_couny' => 'PR', 'ebay_countryname' => 'Puerto Rico');
        return $countryArr;
    }
    //比利时平邮面单
    function blsLabel($dataArr){
        include dirname(dirname(__FILE__)).'/label/Labels/Yuntu/blsLabel.php';
        return $blsLabel;
    }
    //打印PDF面单
    function printPdf($ebay_id,$pickorder=true){
        $fEbay_id=$ebay_id;
        $url='http://api.yunexpress.com/LMS.API.Lable/Api/PrintUrl';
        $ebay_id_str='fzc'.$ebay_id;
        $this->requestData=json_encode(array($ebay_id_str));
        $res=$this->curlGetSend($url,'post');
        $res=json_decode($res,true);
        if($res['ResultCode']=='0000' && isset($res['Item'][0]['Url'])){
            $pdf=file_get_contents($res['Item'][0]['Url']);
            PDF::SavePdf($fEbay_id, $pdf, 'eub', $pickorder);
        }else{
            $ebay_id_str='ZFC'.$ebay_id;
            $this->requestData=json_encode(array($ebay_id_str));
            $res=$this->curlGetSend($url,'post');
            $res=json_decode($res,true);
            if($res['ResultCode']=='0000' && isset($res['Item'][0]['Url'])){
                $pdf=file_get_contents($res['Item'][0]['Url']);
                PDF::SavePdf($fEbay_id, $pdf, 'eub', $pickorder);
            }else{
                    return 'error';
            }
        }
    }
}
//$obj=new YuntuApi();
//$res=$obj->printPdf('25728117');
//var_dump($res);