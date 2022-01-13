<?php
namespace  app\config;
use Yii;


class  Curd {
    

    /**
     * @数据库连接
     * @author  wuyang
     * @date: 2017 04 22
     *
     *
     */
    
    public function connection(){
        return  \Yii::$app->db;
    }
    
    //    INSERT INTO Persons (LastName, Address) VALUES ('Wilson', 'Champs-Elysees')
    
    
    /**
     * @向数据库中插入数据
     * @author  wuyang
     * @date: 2017 04 22
     *
     */
    
    public function Add($modelname, $arr){
        $model_name= $modelname::tableName();
        $key_str='('.implode(',', array_keys($arr)).')';
        $value_str=join(',', array_map(function($v) {return "'$v'";}, $arr));
        $value_str='('.$value_str.')';
        $sql="INSERT INTO ".$model_name.' '.$key_str.' VALUES '. $value_str;
        $result= $this->connection()->createCommand($sql)->execute();
        if($result){
            $last_id_sql = "select id from ".$model_name." order by id desc limit 1";
            $last_id     = $this->connection()->createCommand($last_id_sql)->queryOne();
            return $last_id['id'];
    
        }else{
            return false;
        }
    
    }
    
    /**
     * 插入方法例子
     * @ model 为要实例化的类。类的方法中有插入时要调用的方法
     * @ $categorymodel 为要插入数据的表，插入哪张表，就实例化那张表
     * @ $rs 如果插入成功，$rs返回值是插入的最后一条记录的ID值，如果插入失败，则返回0
     *
     public function Addexample(){
     $arr=[
     'parent_id'=>'66'
     ];
     $model= New Mailtemplate;
     $categorymodel= New MailTemplateCategory();
     $rs= $model->Add($categorymodel,$arr);
     var_dump($rs);
    
     */
    
    /**
     * @更改数据库中的数据
     * @author  wuyang
     * @date: 2017 04 22
     *
     */
    
    public function Updata($modelname, $arr, $where){
        $model_name= $modelname::tableName();
        $up_str='';
        foreach($arr as $key=>$value){
    
            $up_str.=$key.' = '."'".$value."'".', ';
    
        }
        $up_str=rtrim($up_str);
        $up_str=rtrim($up_str,',');
        $sql="UPDATE ".$model_name.' SET '.$up_str.' '.$where;
        $result= $this->connection()->createCommand($sql)->execute();
        return $result;
    }
    
    
    /**
     * @更改数据库中的数据例子
     * @author wuyang
     * @date 2017 04 22
     * @$uparr 是要更新的字段及要更新的值
     * @$model 是存放封装方法的类
     * @categorymodel 是实例化的类。要向其对应的数据表中更新数据
     * @where 是修改的条件
     * $rs 是修改后的返回值。 未修改任何数据，返回0， 修改成功后，返回修改成功条数
     public function actionTest(){
     $uparr=[
     'parent_id'=>'101',
     'category_name'=>'update test 101'
     ];
     $model= New Mailtemplate;
     $categorymodel= New MailTemplateCategory();
     $where="where id >25";
     $rs= $model->Updata($categorymodel,$uparr,$where);
     var_dump($rs);
     }
     *
     */
    
    /**
     * @查询数据库中的数据
     * @author  wuyang
     * @date: 2017 04 22
     *
     */
    
    public function Getdata($modelname, $target_data, $num='All', $where='', $order='', $limit='', $group=''){
        $model_name= $modelname::tableName();
        if($order){
            $order= ' order by '.$order;
        }
        if($limit){
            $limit=' limit '.$limit;
        }
        if($group){
            $group=' group by '.$group;
        }
    
    
        $sql="select ".$target_data.' FROM '.$model_name.' '.$where.''.$order.$limit.$group;
    
        $command =  \Yii::$app->db->createCommand($sql);
    
        if($num=='one'){
    
            $rs = $command->queryOne();
    
        }else{
    
            $rs = $command->queryAll();
        }
    
        return $rs;
    }
    
    /**
     * @查询数据库中的数据例子
     * @author wuyang
     * @date 2017 04 22
     * @$model 是存放封装方法的类
     * @categorymodel 是实例化的类。要向其对应的数据表中更新数据
     * @'id, category_name' 为第二个参数,是要获取的字段的名字
     * @'' 第三个参数为空。如果不传任何参数，则调用queryAll执行sql. 如果 传为'one',则调用queryOne 执行sql.如果传入的其他值，执行queryAll操作
     * @"where id <23" 为第四个参数。第四个参数是传入的where 的条件。
     * @'id desc'  为第五个参数。第五个参数是获取数值的排序
     * @'9'为第六个参数，指定limit 后面的数值
     * @''为第七个参数。为 group by 后面跟的参数 ， 一般不用
     * $rs 是修改后的返回值。 未修改任何数据，返回0， 修改成功后，返回修改成功条数
     public function actionTest(){
     $model= New Mailtemplate;
     $categorymodel= New MailTemplateCategory();
     $rs = $model->Getdata($categorymodel,'id, category_name','',"where id <23",'id desc','9','');
     var_dump($rs);
     exit;
    
     */
    
    
    /**
     * @func purpose 通用功能：用于跨库查询字段
     * @author wuyang
     * @date 2017 04 25
     *
     *
     */
    
    
    
    public function Getreplacedata($modelname, $target_data, $num='one', $where='', $order='', $limit='', $group=''){
    
        if($order){
            $order= ' order by '.$order;
        }
        if($limit){
            $limit=' limit '.$limit;
        }
        if($group){
            $group=' group by '.$group;
        }
        $sql="select ".$target_data.' FROM '.$modelname.' '.$where.''.$order.$limit.$group;
        //        echo $sql;
        //        exit;
        $command =  \Yii::$app->db->createCommand($sql);
        if($num=='one'){
            $rs = $command->queryOne();
        }else{
            $rs = $command->queryAll();
        }
        return $rs;
    }
    
    
    
}
