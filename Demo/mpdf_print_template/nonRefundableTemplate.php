<!-- 非退税模板 -->
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

$compact_number = isset($_GET['compact_number'])?$_GET['compact_number']:'';
$data_json        = $redis->get($key.'ntax_compact'.'-'.$compact_number);
$data             = json_decode($data_json, true);
$compact_data     = $data['compact_data'];
$sku_data         = $data['sku_data'];
$price_list       = $data['price_list'];
$cancel_total_real_money= $data['cancel_total_real_money'];
$settlement_ratio = $compact_data['settlement_ratio'];

/**
 * 切割 中文字符，每行按指定字符数排列
 * @param $str
 * @return array|string
 */
function mb_str_split($str){
    $arr = preg_split('/(?<!^)(?!$)/u', $str);
    $arr = array_chunk($arr,8);
    $str = [];
    foreach($arr as $value){
        $str[] = implode('',$value);
    }
    $str = implode("\n",$str);
    return $str;
}

?>
<body>
<div>
    <div class="ui-box">
        <table class="ui-basicInformation">
            <tr>
                <td class="ui-half-td ui-font-weight" colspan='12'><?php echo $compact_data['a_company_name']?>采购订单合同</td>
            </tr>
            <tr>
                <td class="ui-half-td ui-font-weight" colspan='12'>合同编号: <?php echo $compact_data['compact_number']?></td>
            </tr>
            <tr>
                <td class="ui-half-td ui-font-weight ui-table-font-size-1" colspan='5'>甲方信息</td>
                <td class="ui-half-td ui-font-weight" colspan='7'>乙方信息</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">公司名</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['a_company_name']?></td>
                <td class="ui-quarter-td ui-font-weight">公司名</td>
                <td class="ui-quarter-td" colspan='6'><?php echo $compact_data['b_company_name']?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">地址</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['a_address']?></td>
                <td class="ui-quarter-td ui-font-weight">地址</td>
                <td class="ui-quarter-td" colspan='6'><?php echo $compact_data['b_address']?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">联系人</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['a_linkman']?></td>
                <td class="ui-quarter-td ui-font-weight">联系人</td>
                <td class="ui-quarter-td" colspan='6'><?php echo $compact_data['b_linkman']?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td ui-font-weight">电话</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['a_phone']?></td>
                <td class="ui-quarter-td ui-font-weight">电话</td>
                <td class="ui-quarter-td" colspan='6'><?php echo $compact_data['b_phone']?></td>
            </tr>
            <tr class="ui-purchase-num-tr">
                <td class="ui-quarter-td ui-font-weight">采购单号</td>
                <td class="ui-quarter-td ui-font-weight">sku</td>
                <td class="ui-quarter-td ui-font-weight">品名</td>
                <td class="ui-one-eight-td ui-font-weight">单价</td>
                <td class="ui-one-eight-td ui-font-weight">数量</td>
                <td class="ui-one-eight-td ui-font-weight">取消数量</td>
                <td class="ui-one-eight-td ui-font-weight">金额</td>
                <td class="ui-one-eight-td ui-font-weight">取消金额</td>
                <td class="ui-one-eight-td ui-font-weight">图片</td>
                <td class="ui-one-eight-td ui-font-weight">收货地址</td>
                <td class="ui-one-eight-td ui-font-weight">备注</td>
            </tr>

            <?php
            $total_sku_price = 0;
            foreach ($sku_data as $key => $value_list){
                $num = 0;
                $count =  count($value_list);
                foreach($value_list as $value){
                    $num ++;
                    $total_sku_price += $value['purchase_unit_price']*$value['confirm_amount'];
                    ?>
                    <tr class="ui-purchase-num-tr">
                        <?php if($num ==1){ ?>
                            <td class="ui-quarter-td" style="width: 100px;"><?php echo $value['purchase_number'] ?></td>
                        <?php }else{ ?>
                            <td class="ui-quarter-td" style="width: 100px;"><?php echo $value['purchase_number'] ?></td>
                        <?php } ?>
                        <td style="width: 100px;"><?php echo $value['sku'] ?></td>
                        <td  style="width:180px" class="ui-quarter-td"><?php echo mb_str_split($value['product_name'])?></td>
                        <td class="ui-one-eight-td"><?php echo $value['purchase_unit_price'] ?></td>
                        <td class="ui-one-eight-td"><?php echo $value['confirm_amount'] ?></td>
                        <td class="ui-one-eight-td"><?php echo $value['cancel_ctq'] ?></td>
                        <td class="ui-one-eight-td"><?php echo $value['sku_total_price'] ?></td>
                        <td class="ui-one-eight-td"><?php echo $value['cancel_total_price'] ?></td>
                        <td class="ui-one-eight-td"><img src="<?php echo $value['product_img_url'] ?>" width="40px" height="40px"></td>
                        <td class="ui-one-eight-td"><?php echo $value['warehouse_name'] ?></td>
                        <td class="ui-one-eight-td"></td>
                    </tr>
                <?php }
            } ?>

            <tr class="ui-summary-title">
                <td colspan='2'>
                    <p style="line-height: 20px;">运费支付：<?php echo $compact_data['is_freight']?> </p>
                </td>
                <td colspan='4'></td>
                <td><?php echo $total_sku_price ?></td>
                <td><?php echo $cancel_total_real_money ?></td>
                <td colspan='3'></td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <p style="line-height: 30px;">送货方式</p>
                </td>
                <td colspan='6'>乙方需运送至甲方公司指定仓库地址并安排人员卸货</td>
                <td colspan='2'>交货日期</td>
                <td colspan='3'><?php echo $compact_data['delivery_date']?></td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <p style="line-height: 20px;">收货地址</p>
                </td>
                <td colspan='11'><?php echo $compact_data['receive_address'] ?></td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <p style="line-height: 20px;">总金额</p>
                </td>
                <td colspan='1'><?php echo bcsub($compact_data['product_money'], $cancel_total_real_money, 3); ?></td>
                <td colspan='10' class="ui-contract-content"><?php
                    if($settlement_ratio[0]!='100%'){
                        $dj = isset($price_list['dj'])?$price_list['dj']:0;
                        $tail_money = isset($price_list['wk'])?$price_list['wk']:0;
                        if($tail_money<0){
                            $tail_money =0;
                        }
                        echo ' 订金：'.$dj;
                        echo ' 尾款：'.$tail_money;
                    }
                    ?>
                </td>
            </tr>

            <tr class="ui-summary-title" style='font-size: 13px;'>
                <td rowspan="<?php echo count($compact_data['payment_explain'])+1; ?>">
                    <p>付款说明</p>
                </td>
            </tr>
            <?php if($compact_data['payment_explain']){
                foreach($compact_data['payment_explain'] as $html_key => $html_value){
                    ?>
                    <tr>
                        <td colspan='11' class="ui-contract-content" ><?php echo $html_key.'、'.$html_value;?></td>
                    </tr>
            <?php
                }
            }?>

            <tr class="ui-summary-title">
                <td rowspan="4">
                    <p style="line-height: 80px;">包装要求</p>
                </td>
                <tr>
                    <td colspan='11' class="ui-contract-content">1：产品单个包装，每套包装请用中性无logo内盒，如产品原厂包装为彩盒时，必须用非透明包装袋进行二次包装，包装不符合规定的，甲方可拒收产品并要求乙方重新包装直至达到甲方要求，包装严重不符合规定的，甲方可要求退货，乙方应退回相应货款及承担甲方因此导致的损失。
                    </td>
                </tr>
                <tr>
                    <td colspan='11' class="ui-contract-content">2：发货时，每个外箱必须有唛头，唛头内容包括（“PO NO.” “SKU” “产品名称” “采购员” “订单数量” “箱内数量” “箱 数” “毛重”“是否出口退税” “备注”在备注栏标注对应的订单属性。
                    </td>
                </tr>
                <tr>
                    <td colspan='11' class="ui-contract-content">3：每批产品发货前，将发货清单，放置第一箱，并在外箱上标注，箱内有发货清单。
                    </td>
                </tr>
            </tr>
            <tr class="ui-summary-title">
                <td><p style="line-height: 20px;">合作要求</p></td>
                <td colspan='11' class="ui-contract-content">如乙方有知悉或怀疑甲方员工有索要回扣等商业贿赂行为，可与甲方联系，联系人：易佰网络投诉建议中心， 举报电话：18165762495 （微信同号），举报邮箱：3004486039@qq.com。甲方会对所有信息提供者及所提供的全部资料严格保密。
                </td>
            </tr>

            <tr class="ui-summary-title">
                <td><p style="line-height: 20px;">汇款信息</p></td>
                <td colspan='11' class="ui-contract-content"><?php echo $compact_data['remit_information'] ?></td>
            </tr>

            <tr>
                <td colspan='12' class="ui-summary-title">
                    <span style="line-height:25px;">合约要求</span>
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    1、加盖公章后，合同扫描件与原件具有同等法律效力，因合同所引起的争议和纠纷，双方应通过谈判和协商解决，协商不能达成一致，在深圳市龙岗区人民法院提起诉讼。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    2、甲方向乙方下达采购订单，乙方需在2个工作日内确认并回复，将订单盖章回传（必须为公章或合同专用章）。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    3、乙方需严格按照订单确认交期交货，如需延长交货日期，乙方需于交期前5个自然日向甲方发出书面申请，在征得甲方同意后，按照双方另行确定的交期进行交货，否则，视为乙方逾期交货，乙方逾期5个自然日未能交货的，甲方有权单方解除本订单合同，乙方需在2个自然日内根据原支付途径退回甲方已支付的全部款项，同时，乙方需自逾期之日起每日向甲方支付本订单全款金额的5‰作为违约金。
                </td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='12' class="ui-contract-content" >
                    4、乙方未发货前，须经甲乙双方协商一致且书面确认后，甲方可变更或解除本订单合同，若甲方已向乙方支付相关款项的（货款、运费等），乙方需在2个工作日内根据原支付途径退回前述款项。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    5、乙方按经甲方签字确认的样品安排生产及交货，乙方负责全检、甲方负责抽检。乙方每批次到货时，因发现产品质量问题而影响到甲方仓库入库进度时，需甲方配合全检或二次包装或额外要求甲方质检人员配合时，产生的相关人工检测费用或额外加工费由乙方支付。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    6、抽检产品合格率需符合甲方验收标准，产品出现批量不良，乙方需重新按甲方签字确认的样品生产，因此给甲方造成的损失由乙方承担。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    7、产品的质量保证期为12个月，如有非人为损坏的质量问题产品，乙方应负责换货。因产品质量问题对甲方造成任何损失的，乙方负责全 额赔偿。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    8、乙方应避免其提供的产品有任何知识产权侵权行为，因产品侵权问题产生的全部责任由乙方承担，由此给甲方造成任何损失的，乙方负 责全额赔偿。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    9、如需外验，经甲乙方双方确认后，甲方安排质检部门到乙方工厂验货，因未完成生产或质量问题而导致多次检测的，所产生的所有费用 由乙方承担。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-summary-title">
                    <span style="line-height:25px;">质检要求</span>
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    甲方所下达订单，乙方需在交货期前5天完成并通知甲方采购员，以便甲方确认是否按排人员前去乙方验货。所有甲方人员验货时，乙方均需提供OQC检验报 表以及其他相关功能测试报表以供参考。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    甲方有权对乙方的生产现场、生产流程、作业方式等进行审核，并提出改善建议；乙方对甲方的质量稽查须予 以支持和配合，不允许有以任何形式隐瞒产品质
                    量的现象。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    一、当满足以下条件之一时，甲方可安排对产品及乙方的生产进行稽核，并对乙方提出生产改善建议。
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    1、初次下单采购的产品（甲方条件允许的情况下，样品审核阶段，甲方将对乙方进行生产考核）；
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    2、对于功能复杂、需对产品功能做严格测试的产品；
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    3、在销售过程中，产品在某个问题上出现批量异常或各类型问题累计过多；
                </td>
            </tr>
            <tr>
                <td colspan='12' class="ui-contract-content">
                    4、甲方未安排人员去乙方检验，多次来货后检测合格率偏低的产品。
                </td>
            </tr>
            <tr >
                <td colspan='12' class="ui-contract-content">二、经检验当满足以下条件之一时，甲方有权拒收产品：</td>
            </tr>
            <tr >
                <td colspan='12' class="ui-contract-content">1、甲方正常提出验货，乙方拒不配合。</td>
            </tr>
            <tr >
                <td colspan='12' class="ui-contract-content">2、甲方在检验过程中，不良品超出AQL验收标准，乙方拒不全检。</td>
            </tr>
            <tr >
                <td colspan='12' class="ui-contract-content">3、甲方在检验过程中，不良品未超出AQL验收标准，乙方拒不更换不良品。</td>
            </tr>
            <tr >
                <td colspan='12' class="ui-contract-content">4、甲方提出改善建议，并与乙方达成一致后，再次订货乙方未将改善方案实施。</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td">订购方签章</td>
                <td class="ui-contract-content" colspan='4'>
                    <div>
                        <p>经办人签字:</p>
                        <p>负责人签字:</p>
                        <p>单位盖章:</p>
                        <p>日期:</p>
                    </div>
                </td>
                <td class="ui-quarter-td">供应商签章</td>
                <td class="ui-contract-content" colspan='6'>
                    <div>
                        <p>经办人签字:</p>
                        <p>负责人签字:</p>
                        <p>单位盖章:</p>
                        <p>日期:</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
<style>
</style>
