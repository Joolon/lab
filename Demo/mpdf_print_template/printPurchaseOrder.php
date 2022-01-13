<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="GBK" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>LogisticRequest</title>
</head>
<style>
    .ui_head{
        text-align: center;
    }
    .ui_head .ui_title {
        font-size: 28px;
        padding-top: 10px;
        font-weight: 540;
    }
    .ui_head .ui_baseInfo p{
        margin: 10px 0px;
    }
    .ui_head .ui_baseInfo p span{
        padding: 0px 25px;
    }

    .ui-box {
        display: flex;
        justify-content: center;
        padding: 20px 30px 20px 30px;
        width: 1000px;
    }
    .ui-basicInformation {
        text-align: center;
        font-size: 14px;
        border-spacing: 1px;
        border-collapse: collapse;
        margin-top:-20px;
    }
    td {
        background-color: white;
        line-height: 20px;
        border: 1px solid gray;
    }
    th{
        width:2%;
        min-width:20px;
        min-height:40px;
    }
</style>
<?php

     $data = json_decode(file_get_contents('php://input'), true);
      $table_data = $data['data'];
?>
<body>
<div class="ui_head">
         <h1 class="ui_title">采购单</h1>
        <div class="ui_baseInfo">
            <p>
                <span class="baseInfo">日期：<?php echo $data['create_time']; ?></span>
                <span class="baseInfo">供应商：<?php echo $data['supplier_name'] ?></span>
            </p>
            <p>
                <span class="baseInfo">采购员：<?php echo $data['buyer_name']; ?> </span>
                <span class="baseInfo">收货地址：<?php echo $data['address'] ?></span>
            </p>
        </div>
        <div class="ui-box">
            <table class="ui-basicInformation" border="1">
                <tr class="ui-base-tr">
                    <th>PO</th>
                    <th>产品图片</th>
                    <th>SKU</th>
                    <th style="width: 4%">包装类型</th>
                    <th style="width: 4%">产品名称</th>
                    <th>下单数量</th>
                    <th>单价</th>
                    <th>运费</th>
                    <th>优惠额</th>
                    <th>入库数量</th>
                    <th>不良品数量</th>
                    <th>入库人</th>
                    <th>入库时间</th>
                </tr>
                <?php foreach ($table_data as  $value) { ?>
                <tr>
                    <td><?php echo $value['purchase_number']?></td>
                    <td><img src="<?php echo $value['product_img_url']; ?>" alt="" width="75" height="75"></td>
                    <td><?php echo $value['sku']?></td>
                    <td><?php echo $value['purchase_packaging']?></td>
                    <td><?php echo $value['product_name']?></td>
                    <td><?php echo $value['purchase_amount']?></td>
                    <td><?php echo $value['purchase_unit_price']?></td>
                    <td><?php echo $value['freight']?></td>
                    <td><?php echo $value['discount']?></td>
                    <td><?php echo $value['instock_qty']?></td>
                    <td><?php echo $value['bad_qty']?></td>
                    <td><?php echo $value['instock_user_name']?></td>
                    <td><?php echo $value['instock_date']?></td>
                </tr>
                <?php  } ?>
                <tr class="ui-base-tr">
                    <td class="ui-quarter-td"></td>
                    <td class="ui-quarter-td"></td>
                    <td class="ui-quarter-td"></td>
                    <td class="ui-quarter-td"></td>
                    <td class="ui-quarter-td">总计：</td>
                    <td class="ui-quarter-td"><?php echo $data['total']?></td>
                    <td></td>
                    <td class="ui-quarter-td"><?php echo $data['total_freight']?></td>
                    <td class="ui-quarter-td"><?php echo $data['total_discount']?></td>
                    <td class="ui-quarter-td">sku数：<?php echo $data['product_number']?></td>
                    <td class="ui-quarter-td" colspan="2">总金额：<?php echo $data['total_price']?></td>
                    <td class="ui-quarter-td">RMB</td>
            </table>
        </div>
    </div>
</body>
</html>
