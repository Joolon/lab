<?php
@session_start();
error_reporting(0);
set_time_limit(0);
$truename = $user = $_SESSION['user'] = $userFrode = 'otw';
include 'taskbase.php';
include BASE_PATH."include/config.php";
include_once BASE_PATH.'class/Goods.class.php';
include_once BASE_PATH.'class/NewGoods.class.php';
include_once BASE_PATH . 'class/Storages.class.php';

$startime	= time();
$uptSystemTask = "update system_task set taskstatus=1,taskstarttime=".time().",RunTime=0 where ID=90 ";
$dbcon->execute($uptSystemTask);

$hostUrl            = 'https://61.145.158.170:187';// 服务器地址

// 更新已经存在 ebay_goods中但出于未同步状态的产品
$sqlHaveExists = "UPDATE ebay_newgoods SET syncgood=1 WHERE syncgood='0' AND goods_sn IN(SELECT goods_sn FROM ebay_goods ) ";
$sql = $dbcon->update($sqlHaveExists);

// 查询已完成、已同步图片、未同步产品资料的记录
$list = "SELECT goods_sn,goods_name,goods_location FROM ebay_newgoods WHERE nowstatus='999' AND syncgood='0' ";
$list = $dbcon->execute($list);
$list = $dbcon->getResultArray($list);
//print_r($list);
//exit;

if(count($list)){
    foreach($list as $value){
        $goods_sn = $value['goods_sn'];
        $goods_name = $value['goods_name'];
        $goods_location = trim($value['goods_location']);

        // 根据SKU同步图片(999.已完成，syncpic=1.已同步图片，syncgood=0.未tongue产品)
        $insertSql = "INSERT INTO ebay_goods(goods_name,goods_name2,goods_sn,goods_price,goods_cost,goods_count,goods_unit,
                upc,goods_location,goods_weight,goods_weight2,goods_weight3,goods_note,goods_pic,goods_imgs,goods_length,goods_width,goods_height,
                goods_category,goods_status,goods_attribute,goods_ywsbmc,goods_hgbm,goods_zysbmc,goods_sbjz,goods_register,warehousesx,warehousexx,
                ebay_user,is_delete,color,`size`,materil,factory,factory2,isuse,storeid,goods_grade,parent_sku,salesuser,cguser,ebay_packingmaterial,
                capacity,ispacking,addtim,BtoBnumber,lastexport,lastpandian,lastmodweight,goods_price_gbp,goods_price_aud,lastruku,qty7,qty15,qty30,
                qingcangtime,offlinetime,isusechangetime,ebayvero,smtvero,wishvero,amazonvero,zhixiaotag,ebayqty,aliqty,wishqty,amazonqty,mainsku,title,
                `desc`,pics,`group`,catrgoryid,attr,keywords,review_note,chinese_links,english_links,ASIN,adduser,ebay_newppcost,ebay_firstdevelopcost,
                 suggest_price,sell_points,draw_remark,spare_gys,spare_china_link,spare_eng_link_1,spare_eng_link_2,main_platform,gross_margin)
            SELECT goods_name,goods_name2,goods_sn,goods_price,goods_cost,goods_count,goods_unit,
                upc,goods_location,goods_weight,goods_weight2,goods_weight3,goods_note,replace(goods_pic,'/netimg/','$hostUrl/netimg/'),
                replace(goods_imgs,'/netimg/','$hostUrl/netimg/'),goods_length,goods_width,goods_height,
                goods_category,goods_status,goods_attribute,goods_ywsbmc,goods_hgbm,goods_zysbmc,goods_sbjz,goods_register,warehousesx,warehousexx,
                ebay_user,is_delete,color,`size`,materil,factory,factory2,isuse,storeid,goods_grade,parent_sku,salesuser,cguser,ebay_packingmaterial,
                capacity,ispacking,addtim,BtoBnumber,lastexport,lastpandian,lastmodweight,goods_price_gbp,goods_price_aud,lastruku,qty7,qty15,qty30,
                qingcangtime,offlinetime,isusechangetime,ebayvero,smtvero,wishvero,amazonvero,zhixiaotag,ebayqty,aliqty,wishqty,amazonqty,mainsku,title,
                `desc`,pics,`group`,catrgoryid,attr,keywords,review_note,chinese_links,english_links,ASIN,adduser,ebay_newppcost,ebay_firstdevelopcost,
                suggest_price,sell_points,draw_remark,spare_gys,spare_china_link,spare_eng_link_1,spare_eng_link_2,main_platform,gross_margin
            FROM ebay_newgoods
            WHERE nowstatus='999' AND syncgood='0' AND goods_sn='$goods_sn' LIMIT 1 ";

//        echo $insertSql;exit;
        $res = $dbcon->execute($insertSql);
//        var_dump($res);
        if($res){// 同步成功
            $goods_id = mysql_insert_id();// 插入的产品ID
            $sqlHaveExists = "UPDATE ebay_newgoods SET syncgood=1,goods_id='$goods_id' WHERE goods_sn='$goods_sn' LIMIT 1 ";
            $sql = $dbcon->update($sqlHaveExists);

            $stockData = array(
                'goods_id' => $goods_id,
                'goods_sn' => $goods_sn,
                'goods_name' => $goods_name,
                'goods_count' => 0,
                'store_id' => 32,
            );
            Goods::addSkuToOnhandle($stockData);// 插入库存记录

            NewGoods::addNewGoodsLog($goods_sn,100,'同步新开发产品到库存管理资料库');// 插入同步记录

            // 创建仓位 并绑定SKU
            if($goods_location){
                $storageInfo = '';
                if(substr($goods_location,0,1) == 'U'){// 义乌仓SKU
                    $storageInfo = Storages::findStorage(37,$goods_location);// 判断仓位是否已经加入仓位资料
                    if(empty($storageInfo)){// 未加入则创建仓位
                        $st_res = Storages::createStorage(array('store_id' => 37,'storage_sn' => $goods_location));
                        if($st_res) {// 仓位创建成功
                            $storageInfo = Storages::findStorage(37, $goods_location);
                        }
                    }
                }else{// 坂田仓SKU
                    $storageInfo = Storages::findStorage(32,$goods_location);// 判断仓位是否已经加入仓位资料
                    if(empty($storageInfo)){// 未加入则创建仓位
                        $st_res = Storages::createStorage(array('store_id' => 32,'storage_sn' => $goods_location));
                        if($st_res) {// 仓位创建成功
                            $storageInfo = Storages::findStorage(32, $goods_location);
                        }
                    }
                }

                if($storageInfo){
                    Storages::bingSkuByStorage($storageInfo['id'],$goods_sn);// 绑定仓位到SKU
                }
            }

        }
    }
}

$usetime =(time()-$startime)/60;
$uptSystemTask="update system_task set TaskDetail='同步个数".count($list)."',Runtime=".time().",UseTime=".$usetime.",taskstatus=0 where ID=90 ";
$dbcon->execute($uptSystemTask);


//// 同步库存管理产品资料到新产品开发
if( in_array(date('H'),array('0','6','13','18')) ){

    // 同步库存管理产品状态到新产品开发
    $sqlUp = "UPDATE ebay_newgoods ng,  ebay_goods g
             SET ng.isuse = g.isuse
            WHERE ng.goods_sn=g.goods_sn 
            AND g.isuse != ng.isuse ";
    $dbcon->execute($sqlUp);


    // 同步库存管理产品备注到新产品开发
    $sqlUp = "UPDATE ebay_newgoods ng,  ebay_goods g
             SET ng.goods_note = g.goods_note
            WHERE ng.goods_sn=g.goods_sn 
            AND g.goods_note != ng.goods_note ";
    $dbcon->execute($sqlUp);


    // 同步库存管理产品备注到新产品开发
    $sqlUp = "UPDATE ebay_newgoods ng,  ebay_goods g
             SET ng.factory = g.factory
            WHERE ng.goods_sn=g.goods_sn 
            AND g.factory != ng.factory ";
    $dbcon->execute($sqlUp);

}



echo '同步完成';
exit;


