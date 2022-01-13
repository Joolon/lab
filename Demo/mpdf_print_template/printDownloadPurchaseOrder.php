<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<style>
    .ui-container {
        width: 297mm;
        padding: 0 10mm;
    }
    .ui-box {
        width: 277mm;
    }
    .ui-box-title {
        text-align: center;
        padding: 5px 0;
        margin-right: .5px;
        border-top: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black; 
    }
    table, td {
        border:1px solid black;
        padding-left: 5px;
    }
    table {
        border-collapse: collapse;
    }
    .ui-pd-td {
        padding: 5px;
    }
    .ui-product-img {
        width: 10mm;
        height: 10mm;
    }
    .ui-content-table {
        width: 277mm;
    }
    .ui-table-font-size-4{
        font-size: 4mm;
    }
    /* 无边框 */
    .ui-td-border{
        border-top: none;
        border-bottom: none;
    }
    /* top边框 */
    .ui-td-border-top{
        border-top: 1px solid black;
    }
    /* 底部边框 */
    .ui-td-border-bottom{
        border-bottom: 1px solid black;
    }
    
</style>
<?php
$data = json_decode(file_get_contents('php://input'), true);
if(!$data && count($data) == 0){
    echo "没有数据!";die();
}
function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}
?>
<body>
<div class="ui-container">
    <div class="ui-box">
        <div class="ui-box-title ui-table-font-size-4"><?php echo $data["supplier"]; ?>  送货单</div>
        <table class="ui-content-table ui-table-font-size-4" style="width: 190mm;">
            <tr>
                <td class="ui-pd-td" colspan="5">TO: <?php echo $data["purchaser"]; ?></td>
                <td class="ui-pd-td" colspan="3">下单时间：<?php echo isset($data["audit_time"])?$data["audit_time"]:''; ?></td>
                <td class="ui-pd-td" colspan="4">送货时间：</td>
            </tr>
            <tr>
                <td class="ui-pd-td" colspan="5">收货地址：<?php echo $data["address"]; ?></td>
                <td class="ui-pd-td" colspan="3">收货人： <?php echo $data["addressee"]; ?></td>
                <td class="ui-pd-td" colspan="4">电话：<?php echo $data["phone"]; ?></td>
            </tr>
            <tr>
                <td style="width: 15mm;">序号</td>
                <td style="width: 15mm;">采购单号</td>
                <td style="width: 15mm;">SKU</td>
                <td style="width: 20mm;">产品图片</td>
                <td style="width: 62mm;">产品名称</td>
                <td style="width: 25mm;">包装类型</td>
                <td style="width: 20mm;">采购数量<br />(PCS)</td>
                <td style="width: 25mm;">采购仓库</td>
                <td style="width: 15mm;">实际发货数量</td>
                <td style="width: 15mm;">运费</td>
                <td style="width: 15mm;">箱数</td>
                <td style="width: 52mm;">备注</td>
            </tr>
            <?php
            if(isset($data["value"]) && count($data["value"]) > 0){
                foreach ($data['value'] as $key => $val) {
                    $c_x = 0;
                    // 中位数
                    $val_len = count($val);
                    $zws = $val_len > 3 ? ceil($val_len / 2): $val_len > 1 ? 1 : 0;
                    foreach ($val as $k => $v) {
                        $td_border = '';
                        if($c_x == 0)$td_border = 'ui-td-border-top';
                        if($c_x > 1 && $c_x == ($val_len - 1))$td_border = 'ui-td-border-bottom';
                        ?>
                        <tr>
                            <td style="width: 15mm;"><?php echo $v['id']; ?></td>
                            <?php
                                if($c_x == $zws){
                            ?>
                            <td style="width: 15mm;" class="ui-td-border"><?php echo $key; ?></td>
                            <?php
                                }else{
                            ?>
                            <td style="width: 15mm;" class="ui-td-border <?php echo $td_border; ?>"></td>
                            <?php
                                }
                            ?>
                            <td style="width: 15mm;"><?php echo $v['sku']; ?></td>
                            <td style="width: 20mm;"><img class="ui-product-img" src="<?php echo $v['product_img_url']; ?>"></td>
                            <td style="width: 62mm;">
                                <?php
                                $prod = $v['product_name'];
                                if(empty($prod))echo '';
                                $prod_arr = str_split_unicode($prod, 14);
                                $new_str = '';
                                $x = 0;
                                foreach ($prod_arr as $val_p){
                                    $x ++;
                                    $br = "<br />";
                                    if(count($prod_arr) == $x)$br = "";
                                    $new_str .= $val_p.$br;
                                }
                                echo $new_str;
                                ?>
                            </td>
                            <td style="width: 25mm;"><b><?php echo $v['purchase_package']; ?></b></td>
                            <td style="width: 20mm;"><?php echo $v['confirm_amount']; ?></td>
                            <td style="width: 25mm;"><?php echo $v['warehouse_code']; ?></td>
                            <td style="width: 15mm;"></td>
                            <td style="width: 15mm;"></td>
                            <td style="width: 15mm;"></td>
                            <td style="width: 52mm;"></td>
                        </tr>
                        <?php
                        $c_x ++;
                    }
                }
            }else{
            ?>
                <tr>
                    <td colspan="12">没有发货数据</td>
                </tr>
            <?php
            }
            ?>
            <tr>
                <td class="ui-pd-td" style="width: 15mm;">合计</td>
                <td class="ui-pd-td" style="width: 15mm;"><?php echo $data["purchase_sum"]; ?></td>
                <td class="ui-pd-td" style="width: 15mm;"><?php echo $data["sku_sum"]; ?></td>
                <td class="ui-pd-td" style="width: 20mm;"></td>
                <td class="ui-pd-td" style="width: 62mm;"></td>
                <td class="ui-pd-td" style="width: 25mm;"></td>
                <td class="ui-pd-td" style="width: 20mm;"><?php echo $data["pcs"]; ?></td>
                <td class="ui-pd-td" style="width: 25mm;"></td>
                <td class="ui-pd-td" style="width: 15mm;"></td>
                <td class="ui-pd-td" style="width: 15mm;"></td>
                <td class="ui-pd-td" style="width: 15mm;"></td>
                <td class="ui-pd-td" style="width: 52mm;"></td>
            </tr>
            <tr>
                <td class="ui-pd-td" colspan="12" style="width: 277mm;">
                    注意：<br />
                    1、请按照此发货单发货，核对产品（规格、型号、颜色），产品图，确定无误后发货,实际发货数量必须手填。<br />
                    2、产品包装必须符合以上注明的包装类型，否则会异常退货，易碎产品要加气泡袋或者泡沫，外箱贴易碎标。<br />
                    3、打包时不同SKU,相似品严禁混放,每种SKU必须做好分包隔离,且标示清楚,方便区分。<br />
                    4、所有快递面单/外箱/送货单务必附有采购单号，且须核准无误，否则仓库拒收甲方概不负责,出货时封箱前最后放上此送货清单。<br />
                    5、涉及补货、备品、配件等，此送货清单必须填写对应所属订单号,SKU,备注清楚“补货”“备品”“配件”等字样。<br />
                    6、拒收到付件和自提件。
                </td>
            </tr>
            <tr>
                <td class="ui-pd-td" style="width: 95mm;padding-bottom: 70px;" colspan="5"> 送货单位及经手人（盖章/签字）：</td>
                <td class="ui-pd-td" style="width: 95mm;padding-bottom: 70px;" colspan="7"> 收货单位及经手人（盖章/签字）：</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
