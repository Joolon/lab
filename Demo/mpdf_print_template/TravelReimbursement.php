<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<title>TravelReimbursement</title>
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
		margin: 0px 8px;
	}
	.ui-container_data span {
		padding-right: 3px;
	}
	.bill_no {
		display: inline-block;
		width: 150px;
	}
	/*表格部分*/
	.ui-container_name{
		margin-top: 8px;
		height: 40px;
		border: 1px solid #333333;
		padding-inline-start: 0px
	}
	.ui-container_name li{
		float: left;
		height: 40px;
		line-height: 40px;
		border-right: 1px #333333 solid;
	}
	.ui-container_name li:nth-child(1) {
		width: 200px;
	}
	.ui-container_name li:nth-child(2) {
		width: 250px;
	}
	.ui-container_name li:nth-child(3) {
		width: 445px;
	}
	.ui-container_name li span:nth-child(1) {
		display: inline-block;
		text-align: center;
		width: 120px;
		border-right: 1px #333333 solid;
	}
	.ui-container_date {
		height: 40px;
		padding-inline-start: 0px;
		border-left: 1px #333333 solid;
    	border-right: 1px #333333 solid;
		
	}
	.ui-container_date li {
		float: left;
		height: 40px;
		line-height: 40px;
	}
	.ui-container_date li:nth-child(1) {
		width: 120px;
	}
	.ui-container_date li:nth-child(2) {
		width: 220px;
	}
	.ui-container_date li:nth-child(4) {
		width: 220px;
	}
	.ui-container_date li:nth-child(5) {
		width: 120px;
	}
	.ui-container_date li:nth-child(6) {
		width: 196px;
	}
	.ui-date_span span {
		display: inline-block;
		text-align: center;
		width: 50px;
	}
	.ui-container_table {
		width: 900px;
		text-align: center;
		border-collapse: collapse;
		border-color: #333333;
	}
	.ui-container_table tr {
		height: 40px;
		line-height: 40px;
	}
	.ui-container_table tr td {
		width: 100px;
	}
	.ui-bottom_table {
		width: 900px;
		border-collapse: collapse;
		border-color: #333333;
		border-top: none;
	}
	.ui-bottom_table tr {
		height: 50px;
		line-height: 50px;
	}
	.ui-bottom_table span {
		display: inline-block;
		width: 40px
		
	}
	.ui-bottom_total_span {
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
    //        exit();
    ?>
<body>
	<div class="ui-container_box">
		<div class="ui_container_area">
            <h4 style="left: 10px">
                <span>归属组织：</span>
                <span ><?php echo $data['org_id_cn'];?></span>
                <span style="margin-left: 5px;">归属地点：</span>
                <span><?php echo $data['city_cn'];?></span>
                <span style="margin-left: 5px;"><?php echo $data['is_repayment'] === '1' ? 'Repay' : '';?></span>
            </h4>
			<h2>差旅费报销单</h2>
			<h4>
				<span>单据编号：</span>
				<span class="bill_no"><?php echo $data['cost_order_no'];?></span>
				<span style="margin-left: 10px;"><?php echo $data['account_type'] === '2' ? 'Y' : 'N';?></span>
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
			</div>
			<div>
				<div class="ui-container_name">
					<li>
						<span>姓名：</span>
						<span><?php echo $data['receive_username'];?></span>
					</li>
					<li>
						<span>出差人职位：</span>
						<span></span>
					</li>
				</div>
				<div class="ui-container_date">
					<li>
						<span>出差起止日期：</span>
					</li>
					<li class="ui-date_span">
						<span><?php echo $data['timeStart'][0]?></span>年
						<span><?php echo $data['timeStart'][1]?></span>月
						<span><?php echo $data['timeStart'][2]?></span>日
					</li>
					<li><span>至</span></li>
					<li class="ui-date_span">
						<span><?php echo $data['timeEnd'][0]?></span>年
						<span><?php echo $data['timeEnd'][1]?></span>月
						<span><?php echo $data['timeEnd'][2]?></span>日
					</li>
					<li class="ui-date_span">
						共<span><?php echo $data['total_date']?></span>天
					</li>
					<li class="ui-date_span">
	                	单据及附件共<span></span>张
		            </li>
				</div>
				<table border="1" class="ui-container_table">
					<tr>
						<td>开始时间</td>
						<td>结束时间</td>
						<td>消费类型</td>
						<td>金额</td>
						<td>备注</td>
                        <td>出差事由</td>
					</tr>
                    <?php foreach ($data['detail_info'] as $key => $value) {?>
                        <tr>
                            <td><?php echo $value['start_time'];?></td>
                            <td><?php echo $value['end_time'];?></td>
                            <td><?php echo $value['cost_category_cn'];?></td>
                            <td><?php echo $value['amount'];?></td>
                            <td><?php echo $value['remarks'];?></td>
                            <?php echo $key === 0 ? '<td rowspan="'. count($data['detail_info'], 0) .'">'.$data['consume_cause'].'</td>' : '';?>
                        </tr>
                    <?php };?>
				</table>
				<table border="1" class="ui-bottom_table">
					<tr>
						<td>
							<span class="ui-bottom_total_span">合计金额（大写）</span>
                            <span><?php echo $data['apply_money'][7]?></span>拾
                            <span><?php echo $data['apply_money'][6]?></span>万
                            <span><?php echo $data['apply_money'][5]?></span>仟
                            <span><?php echo $data['apply_money'][4]?></span>佰
                            <span><?php echo $data['apply_money'][3]?></span>拾
                            <span><?php echo $data['apply_money'][2]?></span>元
                            <span><?php echo $data['apply_money'][1]?></span>角
                            <span><?php echo $data['apply_money'][0]?></span>分
							<span style="margin-left: 20px">预支:</span><span class="ui-border-bottom_span">&nbsp;</span>
							<span style="margin-left: 30px">补助:</span><span class="ui-border-bottom_span">&nbsp;</span>
						</td>
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
			</div>
		</div>
	</div>
	
	
</body>
</html>