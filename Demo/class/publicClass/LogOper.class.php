<?php 
/*
*@name 日志操作类
*@add time 2018-03-22
*@add user tian
*/
class LogOper{
	/*
	*@name 记录计划任务操作时间
	*@param id 计划任务id
	*@param1 时间类型
	*@add time 2018-03-22 tian
	*/
	public static function saveOperTime($id,$time_type='start'){
		global $dbcon,$truename;
		$sql='';
		$time=time();
		$startpath = $_SERVER['PHP_SELF'];
		if($time_type=='start'){
			$sql.="UPDATE task_plan SET begintime={$time},startpath='{$startpath}' WHERE id={$id}";
		}else{
			$sql.="UPDATE task_plan SET endtime={$time},startpath='{$startpath}' WHERE id={$id}";
		}
		DB::QuerySQL($sql);
	}
	/*
	*@name 删除sql_log1表数据
	*@param 时间节点
	*@add time 2018-03-22 tian
	*/
	public static function delLogTableData($time=''){
		global $dbcon;
		if(!$time){
			$time=time()-86400*3;
		}
		$dsql = 'DELETE FROM sql_log1 WHERE UNIX_TIMESTAMP(add_time)<' . $time;
        DB::QuerySQL($dsql);
	}
	/*
	*@name 添加订单操作日志（可添加多条）
	*@param 订单id
	*@param1 备注
	*@param2 类型 （见orderslog_type表）
	*@add time 2018-03-22 tian
	*/
	public static function addOrderLogs($ebay_id,$notes,$type=0){
		global $truename,$dbcon,$mctime;
		if(!isset($truename)) $truename = ''; 
		if(!isset($mctime)) $mctime = time();
		$asql = 'insert into ebay_orderslog (operationuser,operationtime,notes,ebay_id,types) values("'.$truename.'",'.$mctime.',"'.mysql_real_escape_string($notes).'",'.$ebay_id.','.$type.')';
		DB::QuerySQL($asql);
	}
	/*
	*@name 添加订单操作日志（同类型更新）
	*@param 订单id
	*@param1 备注
	*@param2 类型 （见orderslog_type表）
	*@add time 2018-03-22 tian
	*/
	public static function updateOrderLogs($ebay_id,$notes,$type=0){
		global $truename,$dbcon,$mctime;
		$time = time();
		$isIn = DB::QuerySQL('SELECT * FROM ebay_orderslog WHERE ebay_id='.$ebay_id.' AND types='.$type);
		if($isIn){
			$dbcon->execute('UPDATE ebay_orderslog SET operationtime = '.$time.' ,notes="'.$notes.'" WHERE ebay_id='.$ebay_id.' AND types='.$type);
		}else{
			self::addOrderLogs($ebay_id,$notes,$type);
		}
	}
	
}