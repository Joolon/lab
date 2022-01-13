<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>装箱明细</title>
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
        padding: 0px 5px;
    }
    .ui_box {
        margin: 0px auto;
        width: 350mm;
        /* border: 1px solid red; */
    }
    .ui_basicInformation {
        text-align: center;
        font-size: 3.5mm;
        border-spacing: 1px;
        border-collapse: collapse;
        width: 100%;
    }
    td {
        background-color: white;
        line-height: 4mm;
        border: 1px solid gray;
        word-break: break-all;
    }
    th{
        /* min-width:10mm;
        min-height:40px; */
    }
</style>
<?php

$data = json_decode(file_get_contents('php://input'), true);
$table_data = $data['box_detail'];
?>
<body>
<div>
    <div class="ui_head">
        <h1 class="ui_title">装箱明细</h1>
        <div class="ui_baseInfo">
            <p><?php echo $data['supplier_name'] ?></p>
            <p>
                <span class="baseInfo"><?php echo $data['ship_address'] ?></span>
                <span class="baseInfo"><?php echo $data['contact_list']['contact_person'] ?></span>
                <span class="baseInfo"><?php echo $data['contact_list']['contact_number'] ?></span>
            </p>
        </div>
    </div>
    <div class="ui_box">
        <table class="ui_basicInformation" border="1">
            <tr class="ui_base-tr">
                <th >箱号</th>
                <th style="width: 27mm;">sku</th>
                <th style="width: 26mm;">PO</th>
                <th style="width: 9mm;">图片</th>
                <th>产品名称</th>
                <th style="width: 18mm;">产品品牌<br />产品型号</th>
                <th style="width: 13mm;">箱内数</th>
                <th style="width: 36mm;">外箱尺寸</th>
                <th style="width: 25mm;">单箱体积-导入</th>
                <th style="width: 25mm;">净重KG-导入<br />总净重KG-导入</th>
                <th style="width: 25mm;">毛重KG-导入<br />总毛重KG-导入</th>
                <th style="width: 20mm;">采购单价<br />总金额</th>
                <th style="width: 11mm;">是否退税</th>
                <th style="width: 14mm;">箱号是否有效</th>
                <th style="width: 17mm;">最新更新时间</th>
            <?php foreach ($table_data as  $value) { ?>
                <tr>
                    <td><?php echo $value['box_detail_sn']?></td>
                    <td><?php echo $value['sku']?></td>
                    <td><?php echo $value['purchase_number']?></td>
                    <td><img src="<?php echo $value['product_img_url']; ?>" alt="" width="75" height="75"></td>
                    <td><?php echo $value['product_name']?></td>
                    <td><?php echo $value['product_brand'].'<br/>'.$value['product_model'];?></td>
                    <td><?php echo $value['in_case_qty']?></td>
                    <td><?php echo $value['size']?></td>
                    <td><?php echo $value['volume']?></td>
                    <td><?php echo $value['net_weight_per']."<br/>".$value['net_weight']?></td>
                    <td><?php echo $value['rought_weight_per']."<br/>".$value['rought_weight']?></td>
                    <td><?php echo $value['purchase_unit_price']."<br/>".$value['purchase_price_total']?></td>
                    <td><?php echo $value['is_drawback']?></td>
                    <td><?php echo $value['enable']?></td>
                    <td><?php echo $value['update_time']?></td>
                </tr>
            <?php  } ?>
        </table>
    </div>
</div>
</body>
</html>
