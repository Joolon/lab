<?php

/**
 * Created by Lon
 * User: Lon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */

class StockAction extends BaseAction
{

    public $username = '';
    public $active_store_name = '';

    /**
     * 入库上架界面
     */
    public function putInStock(){
        $this->username = $this->getActiveUsername();
        $this->active_store_name = $this->getActiveStore('Name');

        $this->display('putinstock');
    }

    /**
     * 库存转移界面
     */
    public function updateLocation(){
        $this->username = $this->getActiveUsername();
        $this->active_store_name = $this->getActiveStore('Name');

        $this->display('updatelocation');
    }

    /**
     * 库存盘点界面
     */
    public function stockCount(){
        $this->username = $this->getActiveUsername();
        $this->active_store_name = $this->getActiveStore('Name');

        $this->display('stockcount');
    }

    /**
     * 库存查询界面
     */
    public function queryStock(){
        $this->username = $this->getActiveUsername();
        $this->active_store_name = $this->getActiveStore('Name');

        $this->display('querystock');
    }

}