<?php

/**
 * Class InventoryReportHelp
 * 库存报表查询、数据统计帮助类
 * @author:zwl
 */
class Warehouse
{

    private static $dbcon       = '';
    public static $username     = '';

    public static function init()
    {
        global $dbcon, $truename;
        self::$dbcon    = $dbcon;
        self::$username = $truename;
    }


    /**
     * 获取仓库信息列表
     * @return array
     */
    public static function getWarehouseList(){
        self::init();

        $list = "SELECT id,store_name,store_sn FROM ebay_store WHERE ebay_user='otw' ";
        $list = self::$dbcon->query($list);
        $list = self::$dbcon->getResultArray($list);

        return empty($list)?array():$list;
    }

    /**
     * 获取所有仓库下的仓位区域
     * @return array
     */
    public static function getWareLocationAreaList(){
        self::init();

        $list = " SELECT UPPER(e_s.storage_area) AS area,e_s.store_id AS store_id FROM ebay_storages AS e_s 
             INNER JOIN ebay_storage_sku as e_s_s ON e_s.storage_sn=e_s_s.storage_sn
             WHERE 1 GROUP BY e_s.store_id,UPPER(e_s.storage_area) ";

        $list = self::$dbcon->query($list);
        $list = self::$dbcon->getResultArray($list);

        return empty($list)?array():$list;
    }

    /**
     * 获取所有仓库下的仓位区域的仓位头段
     * @return array
     */
    public static function findLocationByArea(){
        self::init();
        $list = " SELECT UPPER(e_s.storage_area) AS area,UPPER(e_s.storage_first) AS storage_first,e_s.store_id AS store_id FROM ebay_storages AS e_s 
             INNER JOIN ebay_storage_sku as e_s_s ON e_s.storage_sn=e_s_s.storage_sn
             WHERE 1 GROUP BY e_s.store_id,UPPER(e_s.storage_first) ";

        $location = self::$dbcon->query($list);
        $location = self::$dbcon->getResultArray($location);

        return empty($location)?array():$location;

    }


}

