<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="X-UA-Compatible" content="ie=edge" />
		<title>PaymentRequest</title>
	</head>
	<style>
		* {
			margin: 0;
			padding: 0;
		}
		li{
			list-style: none;
		}
		/*外部容器*/
		.ui-container_box {
			width: 900px;
			padding: 5px 30px;
			border: 1px #333333 solid;
			margin: 0 auto;
		}
		.ui_container_area {
			position: relative;
		}
		/*标题*/
		h2 {
			width: 100%;
			display: inline-block;
			letter-spacing: 10px;
			color: #333333;
			text-align: center;
			margin-bottom: 15px;
			
		}
		h4 {
			position: absolute;
			top: 10px;
			right: 50px;
			color: #333333;
		}
		/*申请日期部门*/
		.ui-container_time li {
			display: inline-block;
			height: 32px;
			line-height: 32px;
			margin: 0px 30px;
			
		}
		.ui-container_time .ui-container_data{
			margin: 0px 12px;
		}
		.ui-container_data span {
			padding-right: 5px;
		}
		.bill_no {
			display: inline-block;
			width: 150px;
		}
		/*表格部分*/
		.ui-container_table{
			width: 900px;
			text-align: center;
			margin-top: 5px;
			border-collapse: collapse;
			border-color: #333333;
		}
		.ui-container_table tr{
			height: 50px;
			line-height: 50px;
		}
		.ui-container_table tr td:nth-child(1) {
			width: 100px;
		}
		.ui-container_table tr td:nth-child(2) {
			width: 500px;
		}
		.ui-container_table tr td:nth-child(3) {
			width: 300px;
		}
		.ui-contain_total span {
			display: inline-block;
			width: 34px;
		}
		.ui-bottom_amount li {
			float: left;
			width: 297px;
		}
		.ui-bottom_amount span {
			display: inline-block;
			width: 100px;
		}
		/*签字栏*/
		.ui-cost_sign ul {
			height: 32px;
			padding-inline-start: 5px;
		}
		.ui-cost_sign li {
			height: 32px;
			line-height: 32px;
			float: left;
			width: 200px;
		}
        .ui-font_small {
            font-size: 18px;
        }

	</style>
    <?php

    $data = json_decode(urldecode($_GET['data']), true);

    ?>
	<body>
		<div class="ui-container_box">
			<div class="ui_container_area">
                <h4 style="left: 10px">
                    <span>归属组织：</span>
                    <span><?php echo $data['org_id_cn'];?></span>
                    <span style="margin-left: 5px;">归属地点：</span>
                    <span><?php echo $data['city_cn'];?></span>
                </h4>
				<h2>付款申请单</h2>
				<h4>
					<span>单据编号：</span>
					<span class="bill_no"><?php echo $data['cost_order_no'];?></span>
					<span style="margin-left: 15px;"><?php echo $data['account_type'] === '2' ? 'Y' : 'N';?></span>
				</h4>
			</div>
			<div class="ui-container_time">
				<li>
                	<span>申请部门：</span>
                	<span><?php echo $data['department_cn']?></span>
	            </li>
	            <li class="ui-container_data">
	            	<span>申请时间：</span>
	                <span><?php echo $data['applyYear']?></span>年
	            </li>
	            <li class="ui-container_data">
	                <span><?php echo $data['applyMonth']?></span>月
	            </li>
	            <li class="ui-container_data">
	                <span><?php echo $data['applyDay']?></span>日
	            </li>
	            <li>
	                <span>币种：</span>
	                <span><?php echo $data['currency_cn']?></span>
	            </li>
	            <li>
	                <span>汇率：</span>
	                <span><?php echo $data['order_rate']?></span>
	            </li>
			</div>
			<table border="1" class="ui-container_table" >
				
				<tr>
					<td>支付类型</td>
					<td><?php echo $data['payment_type_cn'];?></td>
					<td>付款原因</td>
				</tr>
				<tr>
					<td>收款单位</td>
					<td><?php echo $data['receivables'];?></td>
                    <td rowspan="4"><?php echo $data['consume_cause'];?></td>
				</tr>
				<tr>
					<td><?php echo $data['payment_type'] === '1' ? '收款人' : '账号';?></td>
					<td><?php echo $data['payment_type'] === '1' ? $data['receive_username'] : $data['account'];?></td>
				</tr>
				<tr>
					<td><?php echo $data['payment_type'] === '3' ? '链接' : ($data['payment_type'] === '2' ? '开户行' : '/');?></td>
					<td><?php echo $data['payment_type'] === '3' ? $data['link'] : ($data['payment_type'] === '2' ? $data['opening_bank'] : '/');?></td>
				</tr>
				<tr>
					<td>付款类型</td>
					<td><?php echo $data['pay_type_cn'];?></td>
				</tr>
				<tr>
					<td>金额</td>
					<td class="ui-contain_total">
                        <span class="ui-font_small"><?php echo $data['apply_money'][8]?></span>佰
                        <span class="ui-font_small"><?php echo $data['apply_money'][7]?></span>拾
                        <span class="ui-font_small"><?php echo $data['apply_money'][6]?></span>万
                        <span class="ui-font_small"><?php echo $data['apply_money'][5]?></span>仟
                        <span class="ui-font_small"><?php echo $data['apply_money'][4]?></span>佰
                        <span class="ui-font_small"><?php echo $data['apply_money'][3]?></span>拾
                        <span class="ui-font_small"><?php echo $data['apply_money'][2]?></span>元
                        <span class="ui-font_small"><?php echo $data['apply_money'][1]?></span>角
                        <span class="ui-font_small"><?php echo $data['apply_money'][0]?></span>分
					</td>
                    <td style="text-align: left; width: inherit; padding-left: 10px;">申请人:<?php echo $data['receive_username'];?></td>
				</tr>
				<tr>
					<td colspan="2" class="ui-bottom_amount">
						<li style="border-right: 1px #333333 solid">
							单据及附件共<span class="ui-bottom_span">&nbsp;</span>张
						</li>
						<li>
							合计：<span><?php echo $data['order_amount'];?></span>
						</li>
					</td>
                    <td style="text-align: left; width: inherit; padding-left: 10px;">签收人:</td>
				</tr>
			</table>
			<div class="ui-cost_sign">
                <ul>
                    <?php foreach ($data['auditName'] as $value) {?>
                        <li>
                            <span>审批人：</span>
                            <span><?php echo $value?></span>
                        </li>
                    <?php };?>
                </ul>

                <ul>
                    <li>
                        <span>出纳：</span>
                        <span><?php echo $data['out_na'];?></span>
                    </li>
                </ul>
			</div>
		</div>
	</body>
</html>
