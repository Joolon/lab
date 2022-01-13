<?php

/**
 * Created by JoLon.
 * User: kdw
 * Date: 15-2-9
 * Time: 下午9:13
 * 规范收货人地址中的省市区
 */
class Normstate
{
	/*
	 * @param $state 直辖市/省份/自治区
	 */
    public static function norm_state($state)
    {
        $str = mb_substr($state, 0, 2);
        switch ($str) {
            case '北京':
                return '北京市';
                break;
            case '天津':
                return '天津市';
                break;
            case '上海':
                return '上海市';
                break;
            case '重庆':
                return '重庆市';
                break;
            case '内蒙':
                return '内蒙古自治区';
                break;
            case '新疆':
                return '新疆维吾尔自治区';
                break;
            case '宁夏':
                return '宁夏回族自治区';
                break;
            case '广西':
                return '广西壮族自治区';
                break;
            case '西藏':
                return '西藏自治区';
                break;
            default:
                return $state;
                break;
        }
    }
}
