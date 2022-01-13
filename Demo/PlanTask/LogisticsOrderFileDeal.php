<?php
@session_start();
error_reporting(0);
set_time_limit(0);

$_SESSION['user'] = $userFrode = 'otw';
include 'taskbase.php';
//include BASE_PATH."include/config.php";
include BASE_PATH.'Help/DB.class.php';
include BASE_PATH."include/exportfunction.php";
include BASE_PATH."class/SysAutoloadFiles.class.php";
include BASE_PATH.'Help/OrderStatus.class.php';


$taskStarTime	    = time();
$uptSystemTask  = "update system_task set taskstatus=1,taskstarttime=".time().",RunTime=0 where ID=93 ";
DB::QuerySQL($uptSystemTask);


$taskList = SysAutoloadFiles::getTaskList(array('task_type' => 'LogisticOrder','status' => 10));

//print_r($taskList);
//exit;

foreach($taskList as $task) {
    $id = $task['id'];
    $condition['id'] = $id;
    $condition['status'] = '15';
    SysAutoloadFiles::updateTask($condition);
}


foreach($taskList as $task){
    $startTime = time();
    $taskId   = $task['id'];
    $taskType = $task['task_type'];
    $taskSn   = $task['task_sn'];
    $taskName = $task['task_name'];
    $filePath = $task['filepath'];
    $adduser  = $task['adduser'];


    $condition['id'] = $taskId;
    $condition['status'] = '15';
    $condition['start_time'] = date('Y-m-d H:i:s');
    $condition['remark'] = '已锁定:PlanTask/LogisticsOrderFileDeal.php';
    SysAutoloadFiles::updateTask($condition);

    $optional_field = $task['optional_field'];
    $optional_field = explode('&',$optional_field);

    $startdate  = $optional_field[0];
    $enddate    = $optional_field[1];



    // 发货时间查询条件
    $where = " 1 ";
    if($startdate){
        $startdate_timestamp = strtotime(" $startdate ");
        $where .= " AND ShippedTime>='$startdate_timestamp' ";
    }
    if($enddate){
        $enddate_timestamp = strtotime(" $enddate 23::59:59 ");
        $where .= " AND ShippedTime<='$enddate_timestamp' ";
    }



    $errors         = array();
    $skipIds        = '';
    $errorIds       = '';
    $repeatIds      = '';
    $updatedate     = date('Y-m-d H:i:s');
    $success        = $failure = $skip = $repeat = 0;


    $flag           = true;// 是否循环完成
    $lines          = 10000;// 每次读取的行数
    $offset         = 1;// 偏移量
    $totalCount     = 0;// 总行数
    while ($flag){

        $nowData  = read_csv_lines($filePath,$lines,$offset);
        if(empty($nowData) OR empty($nowData[0])){// 数据为空 读取完毕
            $flag = false;
            break;
        }

        $nowCount       = count($nowData);
        $totalCount     += $nowCount;
//    print_r($nowData);exit;

        foreach($nowData as $key => $value){
            $hisRcdOrderFlag = false;
            $nowUploadData = array();
            $nowUploadData['logistics_name']    = trim($value[0]);// 物流公司
            $order_number                       = trim($value[1]);
            $order_number2                      = str_replace('FZC', '', $order_number);//导入订单号(去掉前缀)
            $nowUploadData['order_number']      = $order_number2;
            $logistics_number                   = trim($value[2]);//  跟踪号
            $logistics_number                   = ltrim($logistics_number,"'");// 去除左边的单引号
            $nowUploadData['logistics_number']  = $logistics_number;


            if(empty($order_number) OR empty($logistics_number)){
                $errors[$order_number2] .= '<font color="blue">订单号或账单号为空（不保存）!!</font>';
                if(!empty($value)){
                    $skip ++;
                    $skipIds .= ','.$order_number;
                }
                continue;
            }

            // 验证订单是否存在
            $nowOrderInfo = DB::Find('ebay_order',$where ." AND ebay_id='$order_number2' ");
            if(empty($nowOrderInfo)){
                $nowOrderInfo = DB::Find('ebay_order_HistoryRcd',$where ." AND ebay_id='$order_number2' ");
                if(empty($nowOrderInfo)){
                    $errors[$order_number2] .= '<font color="red">订单号不存在/发货时间区间不对（不保存）!!</font>';
                    $skip ++;
                    $skipIds .= ','.$order_number;
                    continue;
                }
                $hisRcdOrderFlag = true;
            }
            if($nowOrderInfo['ebay_status'] != 2 AND $nowOrderInfo['ebay_status'] != 236){
                $errors[$order_number2] .= '<font color="red">非已发货状态(当前状态'.(OrderStatus::$EbayStatus[$nowOrderInfo['ebay_status']]).')</font>';
            }
            $nowOrderTracknumber = trim($nowOrderInfo['ebay_tracknumber']);
            if($nowOrderTracknumber != $logistics_number){
                $errors[$order_number2] .= '<font color="#990033">订单当前跟踪号('.$nowOrderTracknumber.')与账单号('.$logistics_number.')不匹配</font>';
            }

            // 判断订单编号(账单号)是否已经导入（已经导入 再次导入的账单为异常）
            $oldLogistics = DB::Find('logistics_fee_report'," order_number='$order_number2'");
            if($oldLogistics){
                $repeatIds .= ','.$order_number2;
                $old_order_number = $oldLogistics['order_number'];
                $old_logistics_number = $oldLogistics['logistics_number'];
                $errors[$order_number2] .= '<font color="#CC9966">订单编号重复(Old:'.$old_logistics_number.'Now:'.$logistics_number.')</font>';
            }

            $fee_time                               = trim($value[3]);// 账单时间
            if($fee_time){
                $fee_time = date('Y-m-d H:i:s',strtotime($fee_time));
                if($fee_time == '1970-01-01 00:00:00'){// 转换失败 用原来的值
                    $fee_time = trim($value[3]);
                }
            }
            $nowUploadData['fee_time']              = $fee_time;
            $nowUploadData['to_country']            = trim($value[4]);// 账单目的国家
            $nowUploadData['fee_weight']            = trim($value[5]);// 账单重量

            $nowUploadData['v2_amount']             = $nowOrderInfo['aa_postage'];// V2系统重量的运费
            $nowUploadData['bill_amount']           = $value[6];// 账单运费

            $nowUploadData['v2bill_diff']           = $nowUploadData['bill_amount'] - $nowUploadData['v2_amount'];
            $nowUploadData['v2bill_diff_rate']      = $nowUploadData['v2bill_diff']/$nowUploadData['bill_amount'];

            $nowUploadData['send_time']             = $nowOrderInfo['ShippedTime']>0?date('Y-m-d H:i:s',$nowOrderInfo['ShippedTime']):'';
            $nowUploadData['sys_to_country']        = $nowOrderInfo['ebay_countryname'];
            $nowUploadData['system_weight']         = $nowOrderInfo['orderweight2'];
            $nowUploadData['sys_logistics_name']    = $nowOrderInfo['system_shippingcarriername'];// 物流公司
            $nowUploadData['sys_logistics_code']    = $nowOrderInfo['ebay_carrier'];// 物流渠道
            $nowUploadData['order_type']            = $nowOrderInfo['ebay_ordertype'];// 平台
            $nowUploadData['order_account']         = $nowOrderInfo['ebay_account'];// 账号
            $nowUploadData['status']                = 10;
            $nowUploadData['updatedate']            = $updatedate;
            $nowUploadData['userid']                = $adduser;
            $nowUploadData['task_sn']               = $taskSn;

//        print_r($nowUploadData);
//        exit;

            if( $errors[$order_number2] ){
                $nowUploadData['error_reason'] = $errors[$order_number2];
            }

            // 禁用更新操作
            if($oldLogistics){
                $nowUploadData2['error_reason'] =  $oldLogistics['error_reason'].(empty($errors[$order_number2])?' ':$errors[$order_number2]);
                $id = $oldLogistics['id'];
                if(DB::Update('logistics_fee_report', $nowUploadData2," id='$id' ")){
                    $success ++;
                    $repeat ++;
                }else{
                    $errorIds .= ','.$order_number;
                    $failure ++;
                }
            }else{
                if(DB::Add('logistics_fee_report', $nowUploadData)){
                    $success ++;
                }else{
                    $errorIds .= ','.$order_number;
                    $failure ++;
                }
            }


            unset($nowData[$key]);
        }

        $offset += $lines;
    }


    $usedTime = ceil((time()-$startTime)/60);
    if($usedTime > 2 ){
        $usedTime = "用时{$usedTime}分";
    }else{
        $usedTime = "用时".(time()-$startTime)."秒";
    }
    $notes = "已完成:总行数{$totalCount},成功({$success}),失败({$failure}),跳过({$skip}),[重复({$repeat})],".
        $usedTime.',跳过:'.$skipIds.',错误或重复记录:'.$errorIds.'重复记录:'.$repeatIds;

    $condition['id'] = $taskId;
    $condition['status'] = '20';
    $condition['end_time'] = date('Y-m-d H:i:s');
    $condition['remark'] = $notes;
    SysAutoloadFiles::updateTask($condition);

}



$taskUsedTime        = ceil((time()-$taskStarTime)/60);
$uptSystemTask  = "update system_task set Runtime=".time().",UseTime=".$taskUsedTime.",taskstatus=0 where ID=93 ";
DB::QuerySQL($uptSystemTask);
