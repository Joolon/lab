<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="X-UA-Compatible" content="ie=edge" />
		<title>CostReimbursement</title>
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
			margin: 0px 12px;
			
		}
		.ui-container_time .ui-container_data{
			margin: 0px 8px;
		}
		.ui-container_data span {
			padding-right: 3px;
		}
		.ui-cost_num {
			padding: 0 5px;
		}
		.bill_no {
			display: inline-block;
			width: 150px;
		}
		/*表格部分*/
		.ui-container_table{
			width: 900px;
			margin-top: 5px;
			text-align: center;
			border-collapse: collapse;
			border-color: #333333;
		}
		.ui-container_table tr,.ui-middle-table tr {
			height: 40px;
			line-height: 40px;
		}
		.ui-container_table tr td:nth-child(1) {
			width: 180px;
		}
		.ui-container_table tr td:nth-child(2) {
			width: 150px;
		}
		.ui-container_table tr td:nth-child(3) {
			width: 320px;
		}
		.ui-container_table tr td:nth-child(4) {
			width: 250px;
		}
		/*合计栏*/
		.ui-middle-table, .ui-bottom-table {
			border-collapse: collapse;
			border-color: #333333;
			border-top: none;
		}
		.ui-middle-table tr td:nth-child(1) {
			text-align: center;
			width: 180px;
		}
		.ui-middle-table tr td:nth-child(2) {
			width: 150px;
		}
		.ui-middle-table tr td:nth-child(3) {
			padding-left: 8px;
			width: 182px;
		}
		.ui-middle-table tr td:nth-child(4) {
			padding-left: 8px;
			width: 180px;
		}
		.ui-middle-table tr td:nth-child(5) {
			padding-left: 8px;
			width: 180px;
		}
		/*合计金额栏*/
		.ui-bottom-table tr {
			height: 50px;
			line-height: 50px;
		}
		.ui-bottom-table tr td:nth-child(1){
			width: 777px;
		}
		.ui-bottom-table tr td:nth-child(2){
			padding-left: 8px;
			width: 196px;
		}
		.ui-bottom-table span {
			display: inline-block;
			text-align: center;
			width: 50px
			
		}
		.ui-bottom-total_span {
			width: 140px!important;
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
		
	</style>
    <?php
    $data = json_decode($_GET['data'], true);
    //        print_r($data['apply_money']);
//    echo ;
//            exit();
    ?>
	<body>
		<div class="ui-container_box">
			<div class="ui_container_area">
                <h4 style="left: 10px">
                    <span>归属组织：</span>
                    <span><?php echo $data['org_id_cn'];?></span>
                    <span style="margin-left: 5px;">归属地点：</span>
                    <span><?php echo $data['city_cn'];?></span>
                    <span style="margin-left: 5px;"><?php echo $data['is_repayment'] === '1' ? 'Repay' : '';?></span>
<!--                    <span style="margin-left: 15px;">--><?php //echo $data['account_type'] === '2' ? 'Y' : 'N';?><!--</span>-->
                </h4>
				<h2>费用报销单</h2>
				<h4>
					<span>单据编号：</span>
					<span class="bill_no"><?php echo $data['cost_order_no'];?></span>
					<span style="margin-left: 15px;"><?php echo $data['account_type'] === '2' ? 'Y' : 'N';?></span>
				</h4>
				<div class="ui-container_time">
					<li>
                    	<span>报销部门：</span>
                    	<span><?php echo $data['department_cn']?></span>
		            </li>
					<li>
                    	
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
		            <li>
		                <span>单据及附件共<spanv class="ui-cost_num">&nbsp;&nbsp;</span>张</span>
		            </li>
				</div>
				<div>
					<table border="1" class="ui-container_table">
						<tr>
							<td>消费类型</td>
							<td>金额</td>
							<td>备注</td>
							<td>申请事由</td>
						</tr>
                        <?php foreach ($data['detail_info'] as $key => $value) {?>
                            <tr>
                                <td><?php echo $value['cost_category_cn'];?></td>
                                <td><?php echo $value['amount'];?></td>
                                <td><?php echo $value['remarks'];?></td>
                                <?php echo $key === 0 ? '<td rowspan="'. count($data['detail_info'], 0) .'">'.$data['consume_cause'].'</td>' : '';?>
                            </tr>
                        <?php };?>
					</table>
					<table border="1" class="ui-middle-table">
						<tr>
							<td>合计</td>
							<td><?php echo $data['order_amount'];?></td>
							<td>原借款：</td>
							<td>应退余额：</td>
							<td>申请人：<?php echo $data['receive_username'];?></td>
						</tr>
					</table>
					<table border="1" class="ui-bottom-table">
						<tr>
							<td>
								<span class="ui-bottom-total_span">合计金额（大写）</span>
                                <span><?php echo $data['apply_money'][7]?></span>拾
                                <span><?php echo $data['apply_money'][6]?></span>万
                                <span><?php echo $data['apply_money'][5]?></span>仟
                                <span><?php echo $data['apply_money'][4]?></span>佰
                                <span><?php echo $data['apply_money'][3]?></span>拾
                                <span><?php echo $data['apply_money'][2]?></span>元
                                <span><?php echo $data['apply_money'][1]?></span>角
                                <span><?php echo $data['apply_money'][0]?></span>分
							</td>
							<td>领款人：</td>
						</tr>
					</table>
				</div>
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
		</div>
	</body>
</html>
