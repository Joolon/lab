<?php
namespace Libs;

class ChangeLog
{

    /**
     * 创建表格
     */
    public function createLogTable(){

        $sql = "
            CREATE TABLE IF NOT EXISTS `pur_change_log` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `record_number` varchar(50) DEFAULT NULL COMMENT '操作记录编号',
              `record_type` varchar(100) DEFAULT NULL COMMENT '操作记录类型',
              `content` varchar(255) DEFAULT NULL COMMENT '更新内容简述信息',
              `update_detail` text COMMENT '详细的变更内容',
              `operator` varchar(20) DEFAULT NULL COMMENT '操作人',
              `operate_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
              PRIMARY KEY (`id`),
              KEY `record_number` (`record_number`),
              KEY `record_type` (`record_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";

    }

    /**
     * 添加操作日志
     * @param array $data   要保存的数据
     * @return bool  true.成功,false.失败
     *
     * @example
     *      $data = array(
     *          id          => 目标记录编号（int|string）
     *          type        => 操作类型（关联模型）
     *          content     => 改变的内容（简略信息,支持搜索）
     *          update_data => 改变的内容（详细信息,文本类型）
     *          user        => 操作人（默认当前用户）
     *          time        => 操作时间（exp.2018-11-23 16:16:16  默认当前时间）
     *          is_show     => 标记日志类型（1.展示日志，2.非展示日志，默认 1）
     *      )
     */
    public static function insertOneLog($data){
        $model                  = new self();
        $model->record_number   = isset($data['id'])?$data['id']:'';
        $model->record_type     = isset($data['type'])?$data['type']:'';
        $model->content         = isset($data['content'])?$data['content']:'';
        $model->operator        = (isset($data['user']) AND $data['user'])?$data['user']:User::getActive()->username;
        $model->operate_time    = isset($data['time'])?$data['time']:date('Y-m-d H:i:s',time());
        $model->is_show         = isset($data['is_show'])?$data['is_show']:1;
        $model->update_detail   = isset($data['detail'])?serialize($data['detail']):'';// 详细信息转换

        $status = $model->save(false);
        return $status;
    }


    /**
     * @param array $query  查询条件
     * @return bool|array   array.结果集，false.查询条件缺失     *
     *
     * @example
     *      $query = array(
     *          id          => 目标记录编号（int|string）
     *          type        => 操作类型（关联模型）
     *          content     => 改变的内容（简略信息,支持搜索）
     *          user        => 操作人（默认当前用户）
     *          is_show     => 标记日志类型（1.展示日志，2.非展示日志，默认 1）
     *     )
     */
    public static function queryLogs($query){
        $real_query = array();
        isset($query['id'])         AND $real_query['id'] = $query['id'];
        isset($query['type'])       AND $real_query['record_type'] = $query['record_type'];
        isset($query['content'])    AND $real_query['content'] = $query['content'];
        isset($query['user'])       AND $real_query['operator'] = $query['user'];
        isset($query['is_show'])    AND $real_query['is_show'] = $query['is_show'];

        if(empty($real_query)) return false;// 查询条件缺失

        $results = (new yii\db\Query())->select("*")
            ->from("change_log")
            ->where($real_query)
            ->all();

        return $results ? $results:array();
    }

}