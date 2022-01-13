<!DOCTYPE  HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta charset="GBK" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title></title>
</head>
<style>
    .ui-box-warp {
        width: 297mm;
        padding: 0 10mm;
    }
    .ui-title {
        width: 277mm;
        text-align: center;
        font-size: 5mm;
        font-weight: 650;
    }
    .ui-box {
        width: 277mm;
    }
    .ui-basicInformation {
        text-align: center;
        font-size: 14px;
        border-collapse: collapse;
        line-height: 1.2;
    }
    td {
        background-color: white;
        border: 1px solid gray;
    }
    .ui-quarter-td {
        padding: 5px 0;
    }
</style>

<?php
$data = json_decode(file_get_contents('php://input'), true);
$table_data = $data['data'];

?>
<body>
    <div class="ui-box-warp">
        <div class="ui-title">采购单</div>
        <div class="ui-box">
            <table class="ui-basicInformation">
                <tr class="ui-base-tr">
                    <td class="ui-quarter-td" style="width: 138.5mm;border-color: #fff" align="left">日期：<?php echo $data['create_time']; ?></td>
                    <td class="ui-quarter-td" style="width: 138.5mm;border-color: #fff" align="left">供应商：<?php echo $data['supplier_name'] ?></td>
                </tr>
                <tr>
                    <td class="ui-quarter-td" style="width: 138.5mm;border-color: #fff" align="left">采购员：<?php echo $data['buyer_name']; ?></td>
                    <td class="ui-quarter-td" style="width: 138.5mm;border-color: #fff" align="left">收货地址: <?php echo $data['address'] ?></td>
                </tr>
            </table>
        </div>
        <div class="ui-box">
            <table class="ui-basicInformation" border="1">
                <tr class="ui-base-tr">
                    <th class="ui-quarter-td" style="width: 25mm;">PO</th>
                    <th class="ui-quarter-td" style="width: 25mm;">产品图片</th>
                    <th class="ui-quarter-td" style="width: 20mm;">SKU</th>
                    <th class="ui-quarter-td" style="width: 25mm;">包装类型</th>
                    <th class="ui-quarter-td" style="width: 78mm;">产品名称</th>
                    <th class="ui-quarter-td" style="width: 16mm;">下单数量</th>
                    <th class="ui-quarter-td" style="width: 16mm;">单价</th>
                    <th class="ui-quarter-td" style="width: 16mm;">运费</th>
                    <th class="ui-quarter-td" style="width: 16mm;">优惠额</th>
                    <th class="ui-quarter-td" style="width: 16mm;">入库数量</th>
                    <th class="ui-quarter-td" style="width: 16mm;">不良品数量</th>
                    <th class="ui-quarter-td" style="width: 20mm;">入库人</th>
                    <th class="ui-quarter-td" style="width: 18mm;">入库时间</th>
                </tr>
                <?php foreach ($table_data as  $value) { ?>
                <tr>
                    <td style="width: 25mm;"><?php echo $value['purchase_number']?></td>
                    <td style="width: 25mm;"><img src="<?php echo $value['product_img_url']; ?>" alt="" width="75" height="75"></td>
                    <td style="width: 20mm;"><?php echo $value['sku']?></td>
                    <td style="width: 25mm;"><?php echo $value['purchase_packaging']?></td>
                    <td style="width: 78mm;"><?php echo $value['product_name']?></td>
                    <td style="width: 16mm;"><?php echo $value['purchase_amount']?></td>
                    <td style="width: 16mm;"><?php echo $value['purchase_unit_price']?></td>
                    <td style="width: 16mm;"><?php echo $value['freight']?></td>
                    <td style="width: 16mm;"><?php echo $value['discount']?></td>
                    <td style="width: 16mm;"><?php echo $value['instock_qty']?></td>
                    <td style="width: 16mm;"><?php echo $value['bad_qty']?></td>
                    <td style="width: 20mm;"><?php echo $value['instock_user_name']?></td>
                    <td style="width: 18mm;"><?php echo $value['instock_date']?></td>
                </tr>
                <?php  } ?>
                <tr class="ui-base-tr">
                    <td class="ui-quarter-td" colspan="2"></td>
                    <td class="ui-quarter-td" class="ui-quarter-td">产品总数</td>
                    <td class="ui-quarter-td" class="ui-quarter-td"><?php echo $data['product_number']?></td>
                    <td class="ui-quarter-td" class="ui-quarter-td">总数量</td>
                    <td class="ui-quarter-td" class="ui-quarter-td"><?php echo $data['total']?></td>
                    <td class="ui-quarter-td" class="ui-quarter-td"><?php echo $data['total_price']?></td>
                    <td class="ui-quarter-td" class="ui-quarter-td"><?php echo $data['total_freight']?></td>
                    <td class="ui-quarter-td" class="ui-quarter-td"><?php echo $data['total_discount']?></td>
                    <td class="ui-quarter-td" class="ui-quarter-td">RMB</td>
                    <td class="ui-quarter-td" colspan="3"></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
