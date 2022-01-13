<?php
include_once dirname(dirname(__FILE__)).'/Help/DB.class.php';

/**
 * 新产品开发-产品认领池
 * Class NewGoodsClaim
 * @author zwl
 * @date 2018-01-22
 */
class NewGoodsClaim
{
    private static $tablename   = 'ebay_newgoods_claim';
    private static $truename    = null;

    public static function init(){
        global $truename;
        self::$truename = $truename;
    }


    /**
     * 获得指定产品的认领状态
     * @param string    $goods_sn   产品编码
     * @return bool
     */
    public static function getClaimStatus($goods_sn){
        $result = DB::Find(self::$tablename,"goods_sn='$goods_sn'");
        if(empty($result)) return false;
        return $result['claim_status'];
    }

    /**
     * 获得指定产品的认领状态
     * @param string    $goods_sn   产品编码
     * @return bool
     */
    public static function getClaimItem($goods_sn){
        $result = DB::Find(self::$tablename,"goods_sn='$goods_sn'");
        if(empty($result)) return false;
        return $result['claim_item'];
    }

    /**
     * 更新指定产品的认领状态
     * @param string    $goods_sn       产品编码
     * @param string    $claim_info     认领状态信息
     * @return bool true|false          更新成功|失败
     */
    public static function setClaimStatus($goods_sn,$claim_info){
        $have = self::getClaimStatus($goods_sn);
        if($have !== false){
            $res = DB::Update(self::$tablename,array('claim_status' => $claim_info),"goods_sn='$goods_sn'");
        }else{
            $add = array('goods_sn' => $goods_sn,'claim_status' => $claim_info);
            $res = DB::Add(self::$tablename,$add);
        }
        // 设置是否已经认领
        if(empty($claim_info)){
            DB::Update('ebay_newgoods',array('claimed' => 0),"goods_sn='$goods_sn'");
        }else{
            DB::Update('ebay_newgoods',array('claimed' => 1),"goods_sn='$goods_sn'");
        }
        if($res) return true;
        return false;
    }

    /**
     * 更新指定产品刊登后的Item URL
     * @param string    $goods_sn       产品编码
     * @param string    $claim_item     认领状态信息
     * @return bool true|false          更新成功|失败
     */
    public static function setClaimItem($goods_sn,$claim_item){
        $have = self::getClaimStatus($goods_sn);
        if($have !== false){
            $res = DB::Update(self::$tablename,array('claim_item' => $claim_item),"goods_sn='$goods_sn'");
        }else{
            $add = array('goods_sn' => $goods_sn,'claim_item' => $claim_item);
            $res = DB::Add(self::$tablename,$add);
        }

        if($res) return true;
        return false;
    }


}