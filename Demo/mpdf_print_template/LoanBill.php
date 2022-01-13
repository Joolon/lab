<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="X-UA-Compatible" content="ie=edge" />
		<title>借款单</title>
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
		.ui-container_time {
			text-align: center;
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
			width: 140px;
		}
		.ui-container_table tr td:nth-child(2) {
			width: 320px;
		}
		.ui-container_table tr td:nth-child(3) {
			width: 120px;
		}
		.ui-container_table tr td:nth-child(4) {
			width: 320px;
		}
		.ui-contain_total span {
			display: inline-block;
			width: 72px;
		}
		.ui-bottom_date {
			position: relative;
		}
		.ui-bottom_date li{
		    position: absolute;
		    bottom: 0px;
		    right: 28px;
		}
		.ui-bottom_date li span {
			display: inline-block;
			width: 50px;
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
//        exit();
    ?>
	<body>
		<div class="ui-container_box">
			<div class="ui_container_area">
                <h4 style="left: 10px">
                    <span>归属组织：</span>
                    <span><?php echo $data['org_id_cn'];?></span>
                </h4>
				<h2>借款单</h2>
				<h4>
					<span>单据编号：</span>
					<span class="bill_no"><?php echo $data['cost_order_no'];?></span>
				</h4>
			</div>
			<div class="ui-container_time">
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
			<table border="1" class="ui-container_table">
				<tr>
					<td>部门</td>
					<td><?php echo $data['department_cn']?></td>
					<td>姓名</td>
					<td><?php echo $data['receive_username']?></td>
				</tr>
				<tr>
					<td>借款类型</td>
					<td><?php echo $data['consume_type_cn']?></td>
					<td>费用类型</td>
					<td><?php echo $data['pay_type_cn']?></td>
				</tr>
				<tr>
					<td>借款事由</td>
					<td colspan="3"><?php echo $data['consume_cause']?></td>
				</tr>
				<tr>
					<td>借款金额（大写）</td>
					<td colspan="3" class="ui-contain_total">
						<span><?php echo $data['apply_money'][7]?></span>拾
						<span><?php echo $data['apply_money'][6]?></span>万
						<span><?php echo $data['apply_money'][5]?></span>仟
						<span><?php echo $data['apply_money'][4]?></span>佰
						<span><?php echo $data['apply_money'][3]?></span>拾
						<span><?php echo $data['apply_money'][2]?></span>元
						<span><?php echo $data['apply_money'][1]?></span>角
						<span><?php echo $data['apply_money'][0]?></span>分
					</td>
				</tr>
				<tr>
					<td>预估还款报销日期</td>
					<td><?php echo $data['return_time']?></td>
					<td>合计</td>
					<td><?php echo $data['order_amount'];?></td>
				</tr>
				<tr>
					<td rowspan="2">审批意见</td>
					<td rowspan="2">同意。</td>
					<td rowspan="2">借款人签收</td>
					<td rowspan="2" class="ui-bottom_date">
						<li>
							<span></span>年
							<span></span>月
							<span></span>日
						</li>
					</td>
				</tr>
				<tr></tr>
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
