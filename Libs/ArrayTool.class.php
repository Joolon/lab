<?php

/**
 * Created by JoLon.
 * 数组处理的工具类
 * User：Jolon
 * Time: 15-2-9 下午9:13
 */
class ArrayTool
{


    /**
     * 递归计算数组所有元素的和
     *      该函数用于解决array_sum的短板，支持N维数组。
     * @param array $arr  多维数组
     * @return float|int  若不是数组则返回原参数
     */
    public static function arraySumRecursive($arr){
        static $sum = 0;

        if(is_array($arr)){
            foreach($arr as $value){
                if(is_array($value)){
                    self::arraySumRecursive($value);// 递归求和
                }
            }
            $sum += array_sum($arr);// array_sum只计算一维数组的和，即元素是数组类型的不参与计算
        }else{
            $sum = $arr;
        }

        return $sum;
    }



    /**
     * 取得数组中的某一列的 集合
     * @param array  $array 目标数组
     * @param string $column 目标列名
     * @param $indexKey
     * @return array
     */
    public static function getArrayColumn($array,$column,$indexKey = null){
        if(function_exists('array_column')){
            return array_column($array,$column,$indexKey);
        }else{
            $res = array();
            foreach ($array as $key => $value) {
                foreach ($value as $k1 => $v1){
                    if( $k1 === $column){
                        if($indexKey){
                            $res[$value[$indexKey]] = $v1;
                        }else{
                            $res[] = $v1;
                        }
                    }
                }

            }
            return $res;
        }
    }


    /**
     * 二维数组指定列的值作为键
     */
    public static function arrayColumnsToKey($array,$column){

        $arr_tmp = array();
        if(count($array)){
            foreach($array as $value){
                $arr_tmp[$value[$column]] = $value;
            }
        }

        return $arr_tmp;

    }


    /**
     * 二维数组根据 指定键名 排序
     * @param $array
     * @param $key
     * @param string $order
     * @return array
     */
    public static function arrSort($array,$key,$order = "asc"){//asc是升序 desc是降序
        $arr_num   =   $arr    =   array();
        foreach($array as $k => $v){
            $arr_num[$k] = $v[$key];
        }
        if($order == 'asc'){
            asort($arr_num);
        }else{
            arsort($arr_num);
        }

        foreach($arr_num as $k => $v){
            $arr[$k] = $array[$k];
        }
        return $arr;
    }


    /**
     * 单元素的二维数组转一维数组
     * @param $input
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    public static function arrayColumns($input, $columnKey, $indexKey = NULL){
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
        $result = array();

        foreach ((array)$input AS $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }

            $result[$key] = $tmp;
        }

        return $result;
    }

    /**
     * 去掉一维数组中的前后导空格
     * @param $array
     */
    public static function arrayWalkTrim(&$array){
        function element_trim(&$element, $key)
        {
            $element = trim($element);
        }

        array_walk($array, "element_trim");
    }

    /**
     *  将数组的元素用指定字符拼接起来
     * @param $arr
     * @param $separator
     * @return string
     */
    public static function arrayToStr($arr, $separator){
        $str = '';
        if ($arr and !empty($arr)) {
            foreach ($arr as $e) {
                $str .= $str ? $separator . "'" . $e . "'" : "'" . $e . "'";
            }
        }

        return $str;
    }

    /**
     * 将数组的键值指定为新数组的键名
     * @param $arr
     * @param $key
     * @param null $obj
     * @return array
     */
    public static function arrayValueToKey($arr, $key, $obj = NULL)
    {
        $return = array();
        foreach ($arr as $arr_key => $arr_value) {
            if ($obj) {
                if (is_array($obj)) {
                    foreach ($obj as $value_arr_key => $value_arr_value) {
                        $return[$arr_value[$key]][$value_arr_value] = $arr_value[$value_arr_value];
                    }
                } else {
                    $return[$arr_value[$key]] = $arr_value[$obj];
                }
            } else {
                $return[$arr_value[$key]] = $arr_value;
            }
        }
        return $return;
    }

    /**
     * 按$value为二维数组分类
     * $key分类键值 筛选键值$value(可为array)
     * @param $arr
     * @param $key
     * @param $value
     * @return array 键名为$value[]的三维数组
     */
    public static function arrayGrouping($arr, $key, $value)
    {
        $arr_push = array();
        foreach ($arr as $arr_key => $arr_value) {
            if (is_array($value)) {
                if (in_array($value, $arr_value[$key])) {
                    $arr_push[$arr_value[$key]][] = $arr_value;
                }
            } else {
                if ($arr_value[$key] == $value) {
                    $arr_push[$value][] = $arr_value;
                }
            }
        }
        return $arr_push;
    }

    /**
     * 按$value为二维数组分组
     * @param array $arr
     * @param $obj
     * @return array  键名为$value[]的三维数组
     */
    public static function arrayGroupingWithoutJudge(array $arr, $obj)
    {
        $arr_push = array();
        foreach ($arr as $val) {
            $arr_push[$val[$obj]][] = $val;
        }
        return $arr_push;
    }

    /**
     * 对象转化成数组
     * @param $obj
     * @return array
     */
    public static function objectToArray($obj){
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        if ($_arr) {
            foreach ($_arr as $key => $val) {
                $val = (is_array($val) || is_object($val)) ? self::objectToArray($val) : $val;
                $arr[$key] = $val;
            }
        }
        return isset($arr) ? $arr : array();
    }

    /**
     * kdw
     * 取出二维数组中的某一列的值
     * @param $input 传入的二维数组
     * @param $columnKey  某一列名
     * @param $indexKey 新数组的key
     * PHP 5.4版本自带
     * @return array
     */
    public static function arrayColumn($input, $columnKey, $indexKey = NULL)
    {
        if (!function_exists('array_column')) {
            $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
            $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
            $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
            $result = array();

            foreach ((array)$input AS $key => $row) {
                if ($columnKeyIsNumber) {
                    $tmp = array_slice($row, $columnKey, 1);
                    $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
                } else {
                    $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
                }
                if (!$indexKeyIsNull) {
                    if ($indexKeyIsNumber) {
                        $key = array_slice($row, $indexKey, 1);
                        $key = (is_array($key) && !empty($key)) ? current($key) : NULL;
                        $key = is_null($key) ? 0 : $key;
                    } else {
                        $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                    }
                }

                if (!in_array($tmp, $result)) {
                    $result[$key] = $tmp;
                }
            }

            return $result;
        } else {
            return array_column($input, $columnKey, $indexKey);
        }
    }

}
