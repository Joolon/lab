<?php
include_once dirname(dirname(__FILE__)).'/Help/DB.class.php';

/**
 * 上传文件 加入 系统计划任务自动处理列表
 * Class SysAutoloadFiles
 */
class SysAutoloadFiles
{
    private static $tablename = 'system_autoload_files';

    // 操作类型
    private static $username = '';

    public static function init()
    {
        global $truename;
        self::$username = $truename;
    }


    public static function getTask($taskId){

        $taskInfo = DB::Find(self::$tablename," id='$taskId' ");

        return $taskInfo;
    }

    public static function getTaskList($condition){

        $where = '1';
        if(isset($condition['status']) AND $condition['status'] != 'ALL'){
            $where .= " AND status='{$condition['status']}' ";
        }
        if(isset($condition['task_type'])){
            $where .= " AND task_type='{$condition['task_type']}' ";
        }
        if(isset($condition['task_name'])){
            $where .= " AND task_name='{$condition['task_name']}' ";
        }

        $orderBy = ' ORDER BY addtime ASC';
        if(isset($condition['order_by'])){
            $orderBy = ' '.$condition['order_by'];
        }

        $where .= $orderBy;

        $list = DB::Select(self::$tablename,$where);

        return $list;

    }

    public static function addTask($condition){
        self::init();

        $taskSn = uniqid('LO');// 函数基于以微秒计的当前时间，生成一个唯一的 ID
        $taskSn = strtoupper($taskSn);

        $addTaskInfo = array();
        $addTaskInfo['task_sn'] = $taskSn;

        if(isset($condition['task_type'])){
            $addTaskInfo['task_type'] = $condition['task_type'];
        }
        if(isset($condition['task_name'])){
            $addTaskInfo['task_name'] = $condition['task_name'];
        }
        if(isset($condition['filepath'])){
            $addTaskInfo['filepath'] = $condition['filepath'];
        }
        if(isset($condition['optional_field'])){
            $addTaskInfo['optional_field'] = $condition['optional_field'];
        }
        if(isset($condition['remark'])){
            $addTaskInfo['remark'] = $condition['remark'];
        }

        $addTaskInfo['addtime']         = time();
        $addTaskInfo['adduser']         = self::$username;
        $addTaskInfo['status']          = 10;


        $res = DB::Add(self::$tablename,$addTaskInfo);
        return $res;
    }

    public static function updateTask($condition){

        $id = isset($condition['id'])?$condition['id']:0;
        if(empty($id)){
            return false;
        }
        $taskInfo = self::getTask($id);

        $update = array();
        if(isset($condition['status'])){
            $update['status'] = $condition['status'];
        }
        if(isset($condition['start_time'])){
            $update['start_time'] = $condition['start_time'];
        }
        if(isset($condition['end_time'])){
            $update['end_time'] = $condition['end_time'];
        }
        if(isset($condition['remark'])){
            $update['remark'] = empty($taskInfo['remark'])?$condition['remark']:($taskInfo['remark'].','.$condition['remark']);
        }
        if(empty($update)){
            return false;
        }

        $where = " id='$id' ";

        $res = DB::Update(self::$tablename,$update,$where);
        return $res;


    }

}