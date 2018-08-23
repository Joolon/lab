<?php

/**
 * Created by PhpStorm.
 * 数组处理的工具类
 * User：Jolon
 * Time: 15-2-9 下午9:13
 */
class TimeTool
{




    /**
     * 从一个字符串中提取第一个日期并验证日期，验证成功返回日期，失败返回false
     * @param string $string  目标字符串
     * @param boolean $check  是否检测日期是否合法
     * @return bool|string  string.匹配到的日期  false.没有日期或日期不合法
     */
    public static function getDateFromStringCheck($string,$check = true){
        $pattern = "/\d{1,4}((-|.|\/)\d{1,2}){2}/";
        preg_match($pattern,$string,$match);

        if(empty($match)) return false;

        $date       = $match[0];
        $date_break = explode('-',$date);

        if($check){
            $result     = checkdate($date_break[1],$date_break[2],$date_break[0]);// 验证日期是否合法

            return $result?$date:false;
        }else{
            return $date;
        }

    }


    /**
     * 返回含有毫秒的当前时间（毫秒数四舍五入）
     */
    public static function getMilliSecond(){
        // $usec 微秒部分（*1000取整即为毫秒） $sec 秒数部分
        list($usec, $sec) = explode(" ", microtime());
        return array(
            'msec' => date('YmdHis') . sprintf('%03d', round($usec * 1000)),// 求得3位长度的毫秒值（不足3位前面补0）
            'sec' => $sec
        );
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


    /**
     * 去除字符串里面的中文空格，中文全角空格，换行符制表符回车符
     * @param $string
     * @return mixed
     */
    public static function customStrReplace($string){
        $search = array(" ","　"," ","\n","\r","\t");
        $replace = array("","","","","");

        $string = str_replace($search,$replace ,$string);
        return $string;
    }

}
