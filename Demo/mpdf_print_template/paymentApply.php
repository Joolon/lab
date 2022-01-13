<?php
$data = json_decode($_GET['data'], true);

/**
 * 切割 中文字符，每行按指定字符数排列
 * @param $str
 * @return array|string
 */
function mb_str_split($str){
    $arr = preg_split('/(?<!^)(?!$)/u', $str);
    $arr = array_chunk($arr,40);
    $str = [];
    foreach($arr as $value){
        $str[] = implode('',$value);
    }
    $str = implode("\n",$str);
    return $str;
}
?>

<div>
    <div class="ui-box" >
        <table class="ui-basicInformation" border="1">
            <tr>
                <td class="ui-title" colspan="12">付款申请书</td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td" colspan="2">付款公司主体</td>
                <td class="ui-td" colspan="2"><?php echo $data['invoice_looked_up'] ?></td>
                <td class="ui-td">日期</td>
                <td class="ui-td"><?php echo isset($data['create_time'])?$data['create_time']:''?></td>
                <td class="ui-td">合同号</td>
                <td class="ui-td"><?php echo isset($data['compact_number'])?$data['compact_number']:''?></td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td" colspan="2">收款单位</td>
                <td class="ui-td" colspan="2"><?php echo isset($data['receive_unit'])?$data['receive_unit']:''?></td>
                <td class="ui-td" rowspan="5" colspan="1">付款原因</td>
                <td class="ui-td" rowspan="5" colspan="4">
                    <!--<el-input type="textarea" resize="none" :rows="14" v-model='applyPaymentContractForm.reason' placeholder="请输入付款原因"></el-input>-->
                    <!--<input type="text" placeholder="请输入付款原因">-->
                    <span style='border: none;' name="content" rows="14" cols="80" placeholder="请输入付款原因"><?php  echo  mb_str_split($data['payment_reason']);?></span>
                </td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td" colspan="2">账号</td>
                <td class="ui-td" colspan="2"><?php echo isset($data['receive_account'])?$data['receive_account']:''?></td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td" colspan="2">开户行</td>
                <td class="ui-td" colspan="2"><?php echo isset($data['payment_platform_branch'])?$data['payment_platform_branch']:''?></td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td" colspan="2">金额</td>
                <td class="ui-td" colspan="2"><?php echo isset($data['pay_price_cn'])?$data['pay_price_cn']:''?></td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td" colspan="2">总金额</td>
                <td class="ui-td" colspan="2"><?php echo isset($data['pay_price'])?$data['pay_price']:''?></td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td">财务主管</td>
                <td class="ui-td"><img src="<?php echo isset($data['financial_supervisor_name'])?$data['financial_supervisor_name']:''?>"></td>
                <td class="ui-td">记账</td>
                <td class="ui-td"><?php echo isset($data['approver_name'])?$data['approver_name']:''?></td>
                <td class="ui-td">采购经理</td>
                <td class="ui-td"><img src="<?php echo isset($data['auditor_name'])?$data['auditor_name']:''?>"></td>
                <td class="ui-td">制单人</td><td class="ui-td"><?php echo isset($data['applicant_name'])?$data['applicant_name']:''?></td>
            </tr>
            <tr class="ui-tr">
                <td class="ui-td">财务经理</td>
                <td class="ui-td"><img src="<?php echo isset($data['financial_manager_name'])?$data['financial_manager_name']:''?>"></td>
                <td class="ui-td">财务总监</td>
                <td class="ui-td"><img src="<?php echo isset($data['financial_officer_name'])?$data['financial_officer_name']:''?>"></td>
                <td class="ui-td">供应链总监</td>
                <td class="ui-td"><img src="<?php echo isset($data['waiting_name'])?$data['waiting_name']:''?>"></td>
                <td class="ui-td">总经办</td>
                <td class="ui-td"><img src="<?php echo isset($data['general_manager_name'])?$data['general_manager_name']:''?>"></td>
            </tr>
        </table>
    </div>
</div>
