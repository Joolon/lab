<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<?php
define('BASEPATH', './');
$key = 'PUR_WEB_REDIS_EXPRESS_';
require_once dirname(dirname(dirname(__FILE__)))."/end/config/redis.php";
$redis_default = $config['redis_default'];
$redis         = new Redis();
$redis->connect($redis_default['host'], $redis_default['port']);
$redis->auth($redis_default['password']);

$statement_number = isset($_GET['statement_number'])?$_GET['statement_number']:'';
$data_json        = $redis->get($key.'print_statement'.'-'.$statement_number);
$data             = json_decode($data_json, true);

$statement_main     = $data['statement_main'];
$statement_item_list         = $data['value'];

?>
<body>
<div>
    <div class="ui-box">
        <table class="ui-basicInformation">
            <tr>
                <td class="ui-half-td ui-font-weight" colspan='18'><?php echo $statement_main['purchase_name']; ?><br/><h3>采购对账单</h3>编号:<?php echo $statement_main['statement_number']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight" colspan='2'>采购主体</td>
                <td class="ui-quarter-td" colspan='16'><?php echo $statement_main['purchase_name']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight" colspan='2'>采购主体</td>
                <td class="ui-quarter-td" colspan='16'><?php echo $statement_main['statement_number']; ?></td>
            </tr>

            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight" colspan='2'>供应商</td>
                <td class="ui-quarter-td" colspan='16'><?php echo $statement_main['supplier_name']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight" colspan='2'>对账联系人</td>
                <td class="ui-quarter-td" colspan='<?php echo  $statement_main['is_drawback']==1 ? 7: 6;?>'><?php echo $statement_main['statement_name']; ?></td>
                <td class="ui-quarter-td ui-font-weight" colspan='2'>联系方式</td>
                <td class="ui-quarter-td" colspan='<?php echo  $statement_main['is_drawback']==1 ? 7: 5; ?>'><?php echo $statement_main['statement_phone']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight" colspan='2'>结算方式</td>
                <td class="ui-quarter-td" colspan='<?php echo  $statement_main['is_drawback']==1 ? 7: 6;?>'><?php echo $statement_main['account_type']; ?></td>
                <td class="ui-quarter-td ui-font-weight" colspan='2'>供应商地址</td>
                <td class="ui-quarter-td" colspan='<?php echo  $statement_main['is_drawback']==1 ? 7: 5;?>'><?php echo $statement_main['supplier_address']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight" colspan='2'>对账日期</td>
                <td class="ui-quarter-td" colspan='<?php echo  $statement_main['is_drawback']==1 ? 7: 6;?>'><?php echo $statement_main['create_time']; ?></td>
                <td class="ui-quarter-td ui-font-weight" colspan='2'>本期采购额</td>
                <td class="ui-quarter-td" colspan='<?php echo  $statement_main['is_drawback']==1 ? 7: 5;?>'><?php echo $statement_main['current_purchases_price']; ?></td>
            </tr>
            <tr class="ui-purchase-num-tr">
                <td class="ui-font-weight">序号</td>
                <td class="ui-quarter-td ui-font-weight">采购员</td>
                <td class="ui-quarter-td ui-font-weight">合同号</td>
                <td class="ui-quarter-td ui-font-weight">采购单号</td>
                <td class="ui-quarter-td ui-font-weight">采购仓库</td>
                <td class="ui-quarter-td ui-font-weight">SKU</td>
                <td class="ui-quarter-td ui-font-weight">产品名称</td>
                <td class="ui-quarter-td ui-font-weight">入库数量</td>

                <?php if($statement_main['is_drawback']): ?>
                <td class="ui-quarter-td ui-font-weight">单价</td>
                <td class="ui-quarter-td ui-font-weight">含税单价</td>
                <td class="ui-quarter-td ui-font-weight">币种</td>
                <td class="ui-quarter-td ui-font-weight">税率</td>
                <td class="ui-quarter-td ui-font-weight">金额</td>
                <td class="ui-quarter-td ui-font-weight">税额</td>
                <td class="ui-quarter-td ui-font-weight">价税合计</td>
                <?php else: ?>
                <!-- <td class="ui-quarter-td ui-font-weight">未税单价</td> -->
                <td class="ui-quarter-td ui-font-weight">币种</td>
                <!-- <td class="ui-quarter-td ui-font-weight">未税金额</td> -->
                <td class="ui-quarter-td ui-font-weight">运费</td>
                <td class="ui-quarter-td ui-font-weight">优惠额</td>  
                <?php endif; ?>

                <td class="ui-quarter-td ui-font-weight">已付金额</td>
                <td class="ui-quarter-td ui-font-weight">未付金额</td>
            </tr>
            <?php if(!empty($statement_item_list)): ?>
            <?php foreach ($statement_item_list as $key => $value): ?>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td"><?php echo $key+1; ?></td>
                <td class="ui-quarter-td"><?php echo $value['buyer_name']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['compact_number']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['purchase_number']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['warehouse_name']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['sku']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['product_name']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['upselft_amount']; ?></td>
                <?php if($statement_main['is_drawback']): ?>
                <td class="ui-quarter-td"><?php echo $value['untaxed_unit_price']; ?></td><!--单价(未税)-->
                <td class="ui-quarter-td"><?php echo $value['purchase_unit_price']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['currency_code']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['tax_rate']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['untaxed_unit_price_sum']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['tax_amount_price']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['tax_total_price']; ?></td>
                <?php else: ?>
                <!-- <td class="ui-quarter-td"><?php echo $value['purchase_unit_price']; ?></td> -->
                <td class="ui-quarter-td"><?php echo $value['currency_code']; ?></td>
                <!-- <td class="ui-quarter-td"><?php echo $value['untaxed_unit_price_sum']; ?></td> -->
                <td class="ui-quarter-td"><?php echo $value['freight']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['discount']; ?></td>
                <?php endif; ?>
                <td class="ui-quarter-td"><?php echo $value['amount_paid_price']; ?></td>
                <td class="ui-quarter-td"><?php echo $value['unpaid_amount_price']; ?></td>
                
            </tr>
           <?php endforeach; ?> 
           <?php endif; ?> 

            <tr class="ui-base-tr ui-base-tr-red">
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td">总计</td>
                <td class="ui-quarter-td"><?php echo $statement_main['total_upselft_amount']; ?></td>
                <?php if($statement_main['is_drawback']): ?>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"></td>
                <td class="ui-quarter-td"><?php echo $statement_main['total_untaxed_unit_price_sum']; ?></td>
                <td class="ui-quarter-td"><?php echo $statement_main['total_tax_amount_price']; ?></td>
                <td class="ui-quarter-td"><?php echo $statement_main['total_tax_total_price']; ?></td>
                <?php else: ?>
                <td class="ui-quarter-td"></td>
                <!-- <td class="ui-quarter-td"><?php echo $statement_main['total_untaxed_unit_price_sum']; ?></td> -->
                <td class="ui-quarter-td"><?php echo $statement_main['total_tax_amount_price']; ?></td>
                <td class="ui-quarter-td"><?php echo $statement_main['total_tax_total_price']; ?></td>
                <?php endif; ?>


                <td class="ui-quarter-td"><?php echo $statement_main['total_amount_paid_price']; ?></td>
                <td class="ui-quarter-td"><?php echo $statement_main['total_unpaid_amount_price']; ?></td>
            </tr>
        </table>
        <div style="height: 20px;"></div>
        <table class="ui-basicInformation">
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight" rowspan="2">月货款应开票：</td>
                <td class="ui-quarter-td" >含税</td>
                <td class="ui-quarter-td" colspan='15'>
                <?php 
                if($statement_main['is_drawback']){
                    echo $statement_main['total_tax_total_price'];    
                }else{
                    echo 0.000;
                }
                ?>      
                </td>
            </tr>
            <tr>
                <td class="ui-quarter-td" >不含税</td>
                <td class="ui-quarter-td" colspan='15'>
                <?php
                if($statement_main['is_drawback']){
                    echo $statement_main['total_untaxed_unit_price_sum'];
                }else{
                    echo $statement_main['current_purchases_price'];  
                }
                ?>      
                </td>
            </tr>


            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">签名：</td>
                <td class="ui-quarter-td" colspan='16'><?php echo $statement_main['create_user_name'];?>(联系方式：<?php echo $statement_main['phone']; ?>)</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">对账日期</td>
                <td class="ui-quarter-td" colspan='16'></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-contract-content" colspan='17'>1、请将此采购对账单以EXCEL表格和扫描件（加盖公章或财务章）发到我司采购部核对，财务部复核；</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-contract-content" colspan='17'>2、请贵公司于每月的5日前将对账资料发给我司，以免延误货款结算周期（举例：核对10月货款，11月5日前将对账资料发给我司）；</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-contract-content" colspan='17'>3、请务必将本对账单打印出来盖章和发票一起寄到我司，否则发票退回，延长付款周期；</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-contract-content" colspan='17'>4、对账单邮寄地址：深圳市龙岗区坂田街道里浦街7号TOD科技中心易佰大厦5楼财务部    黎伍培收  18826102937</td>
            </tr>

        </table>
        <div>
            <p><span>供应商名称:</span><?php echo $statement_main['supplier_name']; ?></p>
            <p>盖章：</p>
        </div>



    </div>
</div>
</body>
</html>
