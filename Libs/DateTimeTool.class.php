<?php
namespace Libs;

/**
 * Created by JoLon.
 * 日期时间处理的工具类
 * User：Jolon
 * Time: 2018-02-09 下午9:13
 */
class DateTimeTool
{
    /**
     * 从一个字符串中提取第一个日期并验证日期的合法性
     * @param string $text  目标字符串
     * @return bool|string  string.匹配到的日期  false.没有日期或日期不合法
     */
    public function getDateFromStringCheck($text){
        $pattern = "/\d{4}((-|\.|\/)?\d{1,2}){2}/";
        preg_match($pattern,$text,$match);

        if(empty($match)) return false;

        $date       = $match[0];
        if(strpos($date,'.') !== false){
            $date_break = explode('.',$date);
        }elseif(strpos($date,'-') !== false){
            $date_break = explode('-',$date);
        }else{
            $date_break = array();
            $date_break[0] = substr($date,0,4);
            $date_break[1] = substr($date,4,2);
            $date_break[2] = substr($date,6,2);
        }
        $result     = checkdate($date_break[1],$date_break[2],$date_break[0]);// 验证日期是否合法
        if($result){
            return $date;
        }else{
            return false;
        }
    }


}