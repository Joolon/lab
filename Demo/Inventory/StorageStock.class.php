<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Storages.class.php';

/**
 * 仓位库存操作基础类
 * Class StorageStock
 */
class StorageStock extends Storages
{

    /**
     * 仓位库存转移操作方法
     * @param string $sku               目标SKU
     * @param string $store_id          目标仓库
     * @param string $from_storage_sn   目标仓库的起始仓位
     * @param string $to_storage_sn     目标仓库的目标仓位
     * @param string $amount            转移数量（默认完全转移）
     * @param bool   $remove_from_sku_storage true|false 是|否 移除库存记录（仅在完全转移时有效）
     * @return bool
     */
    public static function stockShift($sku,$store_id,$from_storage_sn,$to_storage_sn,$amount = '',
                                      $remove_from_sku_storage = true){

        if(empty($sku) OR empty($store_id) OR empty($from_storage_sn) OR empty($to_storage_sn)){
            self::$error = '库存转移参数缺失';
            return false;
        }

        DB::QuerySQL('BEGIN');

        // 验证起始仓位情况
        $fromStorageSkuInfo = self::findSkuByStorage($sku,$store_id,$from_storage_sn);
        if(empty($fromStorageSkuInfo)){
            self::$error = '库存转移SKU起始仓位不存在';
            return false;
        }
        // 验证目标仓位情况
        $toStorageSkuInfo = self::findSkuByStorage($sku,$store_id,$to_storage_sn);
        if(empty($toStorageSkuInfo)){
            $storageInfo = self::findStorage($store_id,$to_storage_sn);
            if(empty($storageInfo)){
                self::$error = '目标仓位不存在';
                return false;
            }else{
                $res = self::bingSkuByStorage($storageInfo['id'],$sku,false);
                if($res){
                    self::$success .= '目标仓位与SKU绑定成功';
                }else{
                    self::$error = '目标仓位与SKU绑定失败：'.self::getError();
                    return false;
                }
            }
        }

        // 开始转移库存
        // 不设置转移数量则转移全部数量
        if(empty($amount)){
            $amount = $fromStorageSkuInfo['amount'];
        }

        $res = HhStock::warehouseOut($store_id,$sku,$amount,$from_storage_sn);
        if(empty($res)){
            DB::QuerySQL('ROLLBACK');
            self::$error = '库存转移出库操作失败';
            return false;
        }
        $res = HhStock::warehouseEntry($store_id,$sku,$amount,$to_storage_sn);
        if(empty($res)){
            DB::QuerySQL('ROLLBACK');
            self::$error = '库存转移入库操作失败';
            return false;
        }

        // 库存转移数量等于起始仓位库存则删除库粗记录
        if($remove_from_sku_storage === true ){
            $res = self::unbindSkuByStorage($fromStorageSkuInfo['id'],true);
            if(empty($res)){
                DB::QuerySQL('ROLLBACK');
                self::$error = '库存转移入库操作失败';
                return false;
            }
        }

        DB::QuerySQL('COMMIT');
        return true;


    }



}