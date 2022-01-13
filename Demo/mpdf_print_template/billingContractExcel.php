<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
</head>
<?php

define('BASEPATH', './');
$key = 'PUR_WEB_REDIS_EXPRESS_';
require_once dirname(dirname(dirname(__FILE__)))."/end/config/redis.php";
$redis_default = $config['redis_default'];
$redis         = new Redis();
$redis->connect($redis_default['host'], $redis_default['port']);
$redis->auth($redis_default['password']);

$data_json        = $redis->get($key.'billing_compact_excel');
$data             = json_decode($data_json, true);
$information_info     = $data['information_info'];
$supplier_info         = $data['supplier_info'];
$invoice_list       = $data['invoice_list'];
?>
<body>
<div>
    <div class="ui-box";>
        <table class="ui-basicInformation"  border="1"  style="border-collapse: collapse">
            <tr>
                <td class="ui-half-td ui-font-weight" align="center" colspan='11'><?php echo $information_info['unit_name']; ?>开票合同</td>
            </tr>
            <tr>
                <td class="ui-half-td ui-half-align ui-font-weight" align="right" colspan='11'>
                    开票合同单号: <?php echo $information_info['invoice_number']; ?><br>
                    开票合同生成时间: <?php echo $information_info['create_time']; ?>
                </td>
            </tr>
            <tr>
                <td class="ui-half-td ui-font-weight" align="center" colspan='6'>甲方信息</td>
                <td class="ui-half-td ui-font-weight" align="center" colspan='5'>乙方信息</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">公司名</td>
                <td class="ui-quarter-td" colspan='5' align="center" ><?php echo $information_info['unit_name']; ?></td>
                <td class="ui-quarter-td ui-font-weight">公司名</td>
                <td class="ui-quarter-td" colspan='4' align="center" ><?php echo $supplier_info['supplier_name']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">地址</td>
                <td class="ui-quarter-td" colspan='5' align="center" ><?php echo $information_info['company_address']; ?></td>
                <td class="ui-quarter-td ui-font-weight">地址</td>
                <td class="ui-quarter-td" colspan='4' align="center" ><?php echo $supplier_info['register_address']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">联系人</td>
                <td class="ui-quarter-td" colspan='5' align="center" ><?php echo $information_info['buyer_name']; ?></td>
                <td class="ui-quarter-td ui-font-weight">联系人</td>
                <td class="ui-quarter-td" colspan='4' align="center" ><?php echo $supplier_info['compact_linkman']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">电话</td>
                <td class="ui-quarter-td" colspan='5' align="center" ><?php echo $information_info['iphone']; ?></td>
                <td class="ui-quarter-td ui-font-weight">电话</td>
                <td class="ui-quarter-td" colspan='4' align="center" ><?php echo $supplier_info['compact_phone']; ?></td>
            </tr>
            <tr>
                <td  rowspan="4">开票资料</td>
                <td colspan='10' align="left">单位名称：<?php echo $information_info['unit_name']; ?></td>
            </tr>
            <tr>
                <td colspan='10' align="left">纳税人识别码：<?php echo $information_info['taxpayer_code']; ?></td>
            </tr>
            <tr>
                <td colspan='10' align="left">地址、电话：<?php echo $information_info['address']; ?>  <?php echo $information_info['phone']; ?></td>
            </tr>
            <tr>
                <td colspan='10' align="left">开户行及账号：<?php echo $information_info['opening_bank']; ?> <?php echo $information_info['opening_account']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">sku</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">产品名称</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">开票品名</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">开票单位</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">单价(含税)</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">开票数量</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 75px;">总金额</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">采购单号</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">采购员</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 125px;">下单时间</td>
                <td class="ui-one-eight-td ui-font-weight td_width" style="width: 50px;">出口海关编码</td>
            </tr>
            <?php foreach ($invoice_list as $key => $value): ?>
                <tr class="ui-base-tr">
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['sku']; ?></td>
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['product_name']; ?></td>
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['export_cname']; ?></td>
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['declare_unit']; ?></td>
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['unit_price']; ?></td>
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['invoiced_qty']; ?></td>
                    <td class="ui-one-eight-td" style="width: 75px;"><?php echo $value['total_price']; ?></td>
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['purchase_number']; ?></td>
                    <td class="ui-one-eight-td" style="width: 50px;"><?php echo $value['buyer_name']; ?></td>
                    <td class="ui-one-eight-td" align="left" style="width: 125px;"><?php echo $value['order_create_time']; ?></td>
                    <td class="ui-one-eight-td" style="width: 75px;"><?php echo $value['customs_code']; ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="ui-base-tr">
                <td class="ui-quarter-td">开票合同的修改开票注意事项：</td>
                <td class="ui-contract-content" colspan='10'>
                    <div>
                        <p>a.开票品名、单价、单位一致的可以合并数量开票。不一致的需分行开；</p>
                        <p>b.开票单价以开票合同上的单价为准，不能取平均单价开票；</p>
                        <p>c.发票上“规格型号”栏为空，无需填写；</p>
                        <p>d.发票印章必须清晰可见，不能模糊；</p>
                        <p>e.开票合同必须加印公章或发票章,随发票一起统一用顺丰快递寄出。"（如缺少开票合同全部拒收）</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
