<?php

/**
 * 应用操作的基础方法类
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */
class BaseAction
{
    /**
     * 获得当前登录的用户的ID
     * @return mixed
     */
    public function getActiveUserid(){
        $userid = $_SESSION['userid'];
        return $userid;
    }

    /**
     * 获取当前登录的用户的用户名
     * @return mixed
     */
    public function getActiveUsername(){
        $username = $_SESSION['username'];
        return $username;
    }

    /**
     * 获取当前进入的仓库ID或仓库名
     * @param string $type  ID表示查询仓库ID，其他为仓库名
     * @return mixed
     */
    public function getActiveStore($type = 'ID'){
        if($type == 'ID'){
            $active = $_SESSION['active_store_id'];
        }else{
            $active = $_SESSION['active_store_name'];
        }
        return $active;
    }

    /**
     * 获得GET方式提交的参数
     * @param $name
     * @return null
     */
    public function _get($name)
    {
        return isset($_GET[$name])?$_GET[$name]:null;
    }

    /**
     * 获得POST方式提交的参数
     * @param $name
     * @return null
     */
    public function _post($name){
        return isset($_POST[$name])?$_POST[$name]:null;
    }

    /**
     * 获得REQUEST方式提交的参数
     * @param $name
     * @return null
     */
    public function _request($name)
    {
        return isset($_REQUEST[$name])?$_REQUEST[$name]:null;
    }

    /**
     * 重定向
     * @param $redirect_url
     */
    public function redirect($redirect_url){
        header("location: pda.php?$redirect_url");
        exit;
    }

    /**
     * 验证用户是否登录
     * @return bool true|false 已登录/未登录
     */
    public function isLogin(){
        if(isset($_SESSION['username']) AND $_SESSION['username']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 绑定数据
     * @param $name
     * @param $value
     */
    public function boundParams($name,$value ){
        $this->$name = $value;
    }

    /**
     * 加载模板文件
     * @param $tmpName
     * @param string $ext
     */
    public function display($tmpName,$ext = '.php'){
        include_once  PDA_TPL.$tmpName.$ext;
    }




}