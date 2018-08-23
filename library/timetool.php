<?php

/**
 * Class TimeTool
 * 描述：时间处理工具函数类
 * User：Jolon
 * Time：2016-09-01 10:11:21
 */
class TimeTool
{

    public function __construct()
    {

    }


    /**
     * 返回含有毫秒的当前时间（毫秒数四舍五入）
     */
    public static function get_millisecond()
    {
        // $usec 微秒部分（*1000取整即为毫秒） $sec 秒数部分
        list($usec, $sec) = explode(" ", microtime());
        return array(
            'msec' => date('YmdHis') . sprintf('%03d', round($usec * 1000)),// 求得3位长度的毫秒值（不足3位前面补0）
            'sec' => $sec
        );
    }


    /**
     * 生成企业系统生成 36 位唯一序号
     */
    public static function getGuid()
    {
        // 判断是否有内置 GUID 生成函数
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000); // optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $guid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);

            return $guid;
        }
    }


    /**
     * 计算两个日期相差的天数
     * @param $date1
     * @param $date2
     * @return float
     */
    public static function diffDaysBetweenTwoDate($date1,$date2){
        $d1     = strtotime($date1);// 计算秒数
        $d2     = strtotime($date2);// 计算秒数
        $days   = round(( $d2 - $d1 )/3600/24);// 四舍五入计算相差的天数
        return $days;
    }

}