<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<?php
    $data = json_decode($_GET['data'], true);
    $compact_data = $data['compact_data'];
    $sku_data =  $data['sku_data'];
?>
<body>
<div>
    <h1 class="ui-title"> YIBAI TECHNOLOGY LTD采购合同dd</h1>
    <div class="ui-box">
        <table class="ui-basicInformation">
            <tr>
                <td class="ui-half-td" colspan='3'>甲方</td>
                <td class="ui-half-td" colspan='5'>乙方</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td">公司名</td>
                <td class="ui-quarter-td" colspan='2'><?php echo $compact_data['a_company_name']?></td>
                <td class="ui-quarter-td">公司名</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['b_company_name']?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td">地址</td>
                <td class="ui-quarter-td" colspan='2'><?php echo $compact_data['a_address']?></td>
                <td class="ui-quarter-td">地址</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['b_address']?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td">联系人</td>
                <td class="ui-quarter-td" colspan='2'><?php echo $compact_data['a_linkman']?></td>
                <td class="ui-quarter-td">联系人</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['b_linkman']?></td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td">电话</td>
                <td class="ui-quarter-td" colspan='2'><?php echo $compact_data['a_phone']?></td>
                <td class="ui-quarter-td">电话</td>
                <td class="ui-quarter-td" colspan='4'><?php echo $compact_data['b_phone']?></td>
            </tr>
            <tr class="ui-purchase-num-tr">
                <td class="ui-quarter-td">采购单号</td>
                <td>sku</td>
                <td>品名</td>
                <td class="ui-quarter-td">单价</td>
                <td class="ui-one-eight-td">数量(Pcs)</td>
                <td class="ui-one-eight-td">总金额(RMB)</td>
                <td class="ui-one-eight-td">运费</td>
                <td class="ui-one-eight-td">备注</td>
            </tr>
            <tr class="ui-purchase-num-tr" v-for="(item, index) in compact_details">
                <td class="ui-quarter-td"><?php echo $sku_data['purchase_number']?></td>
                <td><?php echo $sku_data['sku']?></td>
                <td><?php echo $sku_data['product_name']?></td>
                <td class="ui-quarter-td"><?php echo $sku_data['purchase_unit_price']?></td>
                <td class="ui-one-eight-td"><?php echo $sku_data['purchase_amount']?></td>
                <td class="ui-one-eight-td"><?php echo $sku_data['sku_total_price']?></td>
                <td class="ui-one-eight-td"><?php echo $compact_data['is_freight']?></td>
                <td class="ui-one-eight-td"></td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <h4 style="line-height: 30px;">送货方式</h4>
                </td>
                <td colspan='7'><?php echo $compact_data['ship_method']?></td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <h4 style="line-height: 30px;">总金额</h4>
                </td>
                <td colspan='7'><?php echo $compact_data['total_price']?></td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <h4 style="line-height: 30px;">付款说明</h4>
                </td>
                <td colspan='7'>
                        <span>
                            <el-input><?php echo $compact_data['payment_explain']?></el-input>
                        </span>
                </td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <h4 style="line-height: 20px;">合作要求</h4>
                </td>
                <td colspan='7'>
                    <p class="ui-cooperation"><?php echo $compact_data['cooperate_require'][1] ?></p>
                    <span class="ui-redColor"><?php echo $compact_data['cooperate_require'][2] ?></span>
                    <p class="ui-cooperation"><?php echo $compact_data['cooperate_require'][3] ?></p>
                </td>
            </tr>
            <tr class="ui-summary-title">
                <td>
                    <h4 style="line-height: 30px;">汇款信息</h4>
                </td>
                <td colspan='7' class="ui-cooperation"><?php echo $compact_data['remit_information']?></td>
            </tr>
            <tr class="ui-summary-title">
                <td colspan='8'>
                    <h3 style="line-height: 30px;">合约要求</h3>
                </td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>1、合同公章扫描件具有法律效力。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>2、甲方向乙方下达采购订单，乙方需在2个工作日内确认并回复，将订单盖章回传（必须为公章或合同专用章）.卖方应严格按照订单确认交期交货，如交期延迟，并至少提前五个工作日内以书面方式通知甲方协商，且甲方有权取消订单或更改订单。乙方未及时通知,自逾期起每日向甲方赔偿本订单全款金额的5‰作为违约金。如甲方取消订单且甲方已支付订金，乙方需在2个工作日内根据原支付途径退回订金。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>3、乙方按经甲方签字确认的样品安排生产及交货，乙方负责全检、甲方负责抽检。
                    乙方每批次到货时，因发现产品质量问题而影响到甲方仓库入库进度时，需甲方配合全检或二次包装或额外要求甲方质检人员配合时，产生的相关人工检测费用或额外加工费由乙方支付。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>4、抽检产品合格率应在99%以上，产品出现批量不良，乙方需重新按甲方签字确认的样品生产，因此给甲方造成的损失由乙方承担。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>5、产品的质量保证期为12个月，如有非人为损坏的质量问题产品，乙方应负责换货。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>6、乙方应避免其提供的产品有任何知识产权侵权行为，因产品侵权问题 产生的全部责任由乙方承担，由此给甲方造成任何损失的，乙方负责全额赔偿。</td>
            </tr>

            <tr class="ui-summary-title">
                <td colspan='8'>
                    <h3 style="line-height: 30px;">质检要求</h3>
                </td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>甲方所下达订单，乙方需在交货期前5天完成并通知甲方采购员，以便甲方确认是否按排人员前去乙方验货。所
                    有甲方人员验货时，乙方均需提供OQC检验报表以及其他相关功能测试报表以供参考。
                    甲方有权对乙方的生产现场、生产流程、作业方式等进行审核，并提出改善建议；乙方对甲方的质量稽查须予 以支持和配合，不允许有以任何形式隐瞒产品质量的现象。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>一、当满足以下条件之一时，甲方可安人员排对产品及乙方的生产进行稽核，并对乙方提出生 产改善建议。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>1、初次下单采购的产品（甲方条件允许的情况下，样品审核阶段，甲方将对乙方进行生产考核）；</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>2、对于功能复杂、需对产品功能做严格测试的产品；</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>3、在销售过程中，产品在某个问题上出现批量异常或各类型问题累计过多；
                </td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>4、甲方未安排人员去乙方检验，多次来货后检测合格率偏低的产品。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>经检验当满足以下条件之一时，甲方有权拒收产品：
                </td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>1、甲方正常提出验货，乙方拒不配合</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>2、甲方在检验过程中，不良品超出AQL验收标准，乙方拒不全检。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>3、甲方在检验过程中，不良品未超出AQL验收标准，乙方拒不更换不良品。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>4、甲方提出改善建议，并与乙方达成一致后，再次订货乙方未将改善方案实施。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>二、验收标准：</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>1、确定被检验货品数量（假设数量为500）</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>2、确定抽样方案，在没有特别要求下，应按“一般检验标准Ⅱ”进行抽样。</td>
            </tr>
            <tr class="ui-contract-content">
                <td colspan='8'>3、在合格质量水平栏找出要求的AQL值，在没有特别要求下，以AQL值为2.5作检验标准；2.5对应的检验标准 为：AC(可接受)=2，Re(不可接受)=3。</td>
            </tr>
            <tr class="ui-base-tr">
                <td class="ui-quarter-td">订购方签章</td>
                <td class="ui-quarter-td" colspan='2'>
                    <div class="ui-signatrue-confirm">
                        <p>经办人签字:</p>
                        <p>负责人签字:</p>
                        <p>单位盖章:</p>
                        <p>日期:</p>
                    </div>
                </td>
                <td class="ui-quarter-td">供应商签章</td>
                <td class="ui-quarter-td" colspan='4'>
                    <div class="ui-signatrue-confirm">
                        <p class=".ui-signatrue-confirm">经办人签字:</p>
                        <p>负责人签字:</p>
                        <p>单位盖章:</p>
                        <p>日期:</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<style>
    .ui-title {
        font-size: 28px;
        padding-top: 10px;
        font-weight: 540;
        margin-left: 350px;
    }
    .ui-box {
        display: flex;
        justify-content: center;
        padding: 10px 30px 20px 30px;
        width: 900px;
    }
    .ui-basicInformation {
        text-align: center;
        font-size: 14px;
        border-spacing: 1px;
        border-collapse: collapse;
    }
    td {
        line-height: 24px;
    }
    .ui-half-td {
        width: 50%;
        min-width: 420px;
    }
    .ui-base-tr.ui-quarter-td {
        width: 5%;
        min-width: 100px; 
    }
    /* td:nth-child(2n) {
        width: 40% !important;
    } */
    .ui-purchase-num-tr .ui-one-eight-td {
        min-width: 100px;
        height: 80px;
    }
    tr:nth-child(6) td {
        font-weight: 550;
        height: 24px !important;
    }
    .ui-summary-title {
        font-size: 16px;
        font-weight: 600;
    }
    .ui-contract-content {
        text-align: left;
    }
    .ui-contract-content td {
        padding-left: 3px;
    }
    .ui-signatrue-confirm {
        text-align: left;
        padding-left: 6px;
        line-height: 24px;
    }
    .ui-redColor {
        font-size: 14px;
        line-height: 16px!important;
    }
    .ui-cooperation {
        text-align: left;
        padding-left: 6px;
    }
</style>
</body>
</html>
