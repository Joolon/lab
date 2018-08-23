<?php

/**
 * Created by PhpStorm.
 * 数组处理的工具类
 * User：Jolon
 * Time: 15-2-9 下午9:13
 */
class Arraytool
{

    /**
     * 单元素的二维数组转一维数组
     * @param unknown $input $columnKey
     * @return 指定键名的一维数组
     */
    public static function array_columns($input, $columnKey, $indexKey = NULL)
    {
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
     */
    public static function my_array_walk_trim(&$array)
    {
        function element_trim(&$element, $key)
        {
            $element = trim($element);
        }

        array_walk($array, "element_trim");
    }

    /**
     * 将数组的元素用指定字符拼接起来
     */
    public static function array_to_str($arr, $separator)
    {
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
     * return 一维数组
     */
    public static function array_value_to_key($arr, $key, $obj = NULL)
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
     * @return 键名为$value[]的三维数组
     */
    public static function array_grouping($arr, $key, $value)
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
     * 无条件
     * return 键名为$value[]的三维数组
     * 数组内容无删减
     */
    public static function array_grouping_without_judge(array $arr, $obj)
    {
        $arr_push = array();
        foreach ($arr as $val) {
            $arr_push[$val[$obj]][] = $val;
        }
        return $arr_push;
    }

    /**
     * 对象转化成数组
     */
    public static function object_to_array($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        if ($_arr) {
            foreach ($_arr as $key => $val) {
                $val = (is_array($val) || is_object($val)) ? self::object_to_array($val) : $val;
                $arr[$key] = $val;
            }
        }
        return isset($arr) ? $arr : array();
    }

    /**
     * kdw
     * 取出二维数组中的某一列的值
     * $input 传入的二维数组
     * $columnKey  某一列名
     * $indexKey 新数组的key
     * PHP 5.4版本自带
     */
    public static function array_column($input, $columnKey, $indexKey = NULL)
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
