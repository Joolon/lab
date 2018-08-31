<?php

/**
 * 用户首页展示、登录操作类
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */

class IndexAction extends BaseAction
{
    /**
     * 展示登录页面
     */
    public function login()
    {
        if(!$this->isLogin()){
            $this->display('login');
        }else{
            $this->redirect('c=Index&m=index');
            exit;
        }
    }

    /**
     * 验证用户信息开始登录
     */
    public function loginCheck(){
        $username = $this->_post('username');
        $password = $this->_post('password');
        $scanCode = $this->_post('scan_code');
        $loginType = $this->_post('loginType');

        if($loginType == '1'){// 密码方式登录
            if(empty($username) OR empty($password)){
                $ret = array('code' => '0X0001','msg' => '用户名或密码缺失');
                echo json_encode($ret);
                exit;
            }
        }
        else if($loginType == '2'){// 扫码方式登录
            if(empty($scanCode)){
                $ret = array('code' => '0X0001','msg' => '请先扫码');
                echo json_encode($ret);
                exit;
            }else{
                $scanCode = explode('|',$scanCode);
                $userid = $scanCode[0];
                $password = $scanCode[1];
            }
        }

        $password_hash = md5($password);
        if(isset($userid) AND $userid){
            $where = " id='$userid' and (password = '$password' OR password_hash='$password_hash' ) ";
        }else{
            $where = " (username='$username' OR tel='$username') and (password = '$password' OR password_hash='$password_hash' ) ";
        }


        $res = DB::Find('ebay_user',$where);
//        print_r($res);exit;
        if($res){
            $_SESSION['userid'] = $res['id'];
            $_SESSION['username'] = $res['username'];
            $ret =  array('code' => '0X0000','msg' => '恭喜,登录成功!');
        }else{
            $ret =  array('code' => '0X0002','msg' => '抱歉,登录失败,请使用正确的账户或密码登录!');
        }
//        print_r($ret);exit;
        echo json_encode($ret);
        exit;

    }

    /**
     * 首页（仓库等参数初始化界面）
     */
    public function index(){
        if(!$this->isLogin()){
            $this->redirect('c=Index&m=login');
            exit;
        }
        if($this->_get('store_id')){
            $username = $this->getActiveUsername();

            $store_id = $this->_get('store_id');
            $rber_me = $this->_get('rber_me');// 记住我的选择？

            if($rber_me){
                $updateUser = array('last_store_id' => $store_id);
            }else{
                $updateUser = array('last_store_id' => 0);
            }

            DB::Update('ebay_user',$updateUser,"username='$username'");

            $warehouseList = DB::Select('ebay_store',"ebay_user='otw'");
            $warehouseList = get_array_column($warehouseList,'store_name','id');
            $_SESSION['active_store_id'] = $store_id;// 缓存当前操作的仓库
            $_SESSION['active_store_name'] = $warehouseList[$store_id];

            $this->redirect('c=Index&m=main');
        }else {
            $warehouseList = DB::Select('ebay_store', "ebay_user='otw'");
            $warehouseList = get_array_column($warehouseList, 'store_name', 'id');

            $username = $this->getActiveUsername();
            $userInfo = DB::Find('ebay_user', "username='$username'", 'last_store_id');
            $last_store_id = $userInfo['last_store_id'];

            $this->warehouseList = $warehouseList;
            $this->last_store_id = $last_store_id;
        }


        $this->display('selectwarehouse');
    }

    /**
     * 退出系统登录
     */
    public function logout(){
        unset($_SESSION['userid'],$_SESSION['username'],$_SESSION['active_store_id']);
        $this->redirect('c=Index&m=index');
    }

    /**
     * 主菜单展示界面
     */
    public function main(){
        $this->display('main');
    }


}