<?php
/**
 * 同步 指定账号下 1688已铺货的商品信息
 */

$aliProduct = new AliGoodsApi();


$sqlSelProductList  = "SELECT product_id FROM ali1688_products WHERE 1 ";
$sqlSelProductCount = "SELECT COUNT(1) AS num FROM ali1688_products WHERE 1 ";

// 商品总个数
$sqlSelProductCount = DB::QuerySQL($sqlSelProductCount);
$sqlSelProductCount = $sqlSelProductCount[0]['num'];


$time = time();
$date = date('Y-m-d H:i:s',$time);

$pageSize   = 200;
$pageIndex  = 1;
$offset     = 0;

for($i = 0; $i< $sqlSelProductCount;$i += $pageSize){
    $productList = $sqlSelProductList." LIMIT $i,$pageSize ";
    $productList = DB::QuerySQL($productList);

    if(empty($productList)) break;

    $product_id_list = get_array_column($productList,'product_id');


//    print_r($product_id_list);
//    exit;

    foreach($product_id_list as $product_id){

        $aliProductInfo = $aliProduct->getProductInfo($product_id);
//        print_r($aliProductInfo);exit;
        if($aliProductInfo['success']){
            $aliProductInfo = $aliProductInfo['productInfo'];
            $status = $aliProductInfo['status'];

            // 更新商品状态
            $update_data = array('status' => $status,'updateuser' => 'otw','updatetime' => $date);
            DB::Update('ali1688_products',$update_data,"product_id='$product_id' LIMIT 1");

            $skuInfos   = $aliProductInfo['skuInfos'];

            $priceRanges = $aliProductInfo['saleInfo']['priceRanges'];// 购买件数梯度价格（如买10件单价10元，买50件单价8，买100件单价6元）
            $priceRanges = arr_sort($priceRanges,'price','desc');

//            print_r($priceRanges);exit;


            $skuInfoRecombine = $aliProduct->parseSkuListBySkuInfos($skuInfos);
//            print_r($skuInfoRecombine);exit;
            foreach($skuInfoRecombine as $ali_pro_spec_val){
                $spec_id        = $ali_pro_spec_val['specId'];

                if(isset($ali_pro_spec_val['price'])){
                    $price      = $ali_pro_spec_val['price'];
                }else{
                    $price      = $priceRanges[0]['price'];
                }
                $retailPrice    = $ali_pro_spec_val['retailPrice'];
                $amountOnSale   = $ali_pro_spec_val['amountOnSale'];
//                print_r($price);exit;

                // 更新数量
                $update_data_spec =  array('amount_on_sale' => $amountOnSale,'price' => $price,'retail_price' => $retailPrice);
                $where = "product_id='$product_id' AND spec_id='$spec_id' LIMIT 1 ";
//                print_r($update_data_spec);exit;

                DB::Update('ali1688_products_skuinfos',$update_data_spec,$where);
            }
        }

    }



}


echo '同步产品完成<br>';
//exit;









