<?php
/**
 * Oauth 认证用户信息模型
 * User: Jolon
 * Date: 2019/10/20 10:00
 */
class Oauth_user_model extends Purchase_model {

    protected $table_name   = 'oauth_user';// 授权用户数据表名称

    public function __construct(){
        parent::__construct();
    }

    /**
     * 检查用户是否存在并且是启用状态
     * @param $audience
     * @param $audience_key
     * @return array|bool
     */
    public function checkUser($audience,$audience_key){
        $have = $this->purchase_db->select('id,audience,audience_key,access_token')
            ->where('audience',$audience)
            ->where('audience_key',$audience_key)
            ->where('audience_status',1)
            ->get($this->table_name)
            ->row_array();
        return $have?$have:false;
    }

    /**
     * 获取一个授权用户信息
     * @param $audience
     * @return array|bool
     */
    public function getUserByAudience($audience){
        $have = $this->purchase_db->select('id,audience,audience_key,access_token')
            ->where('audience',$audience)
            ->get($this->table_name)
            ->row_array();
        return $have?$have:false;
    }

    /**
     * 更新授权用户信息
     * @param $audience
     * @param $update_data
     * @return bool
     */
    public function updateUser($audience,$update_data){
        $this->purchase_db->where('audience',$audience)->update($this->table_name,$update_data);
        return true;
    }

    /**
     * 检查access_token是否合法并判断是否限流
     * @param $access_token
     * @return array
     * @exp 限制访问 $return = [
     *          'code' => false,
     *          'message' => 'Expired token',
     *          'data' => '',
     *      ],
     * @exp 允许访问 $return = [
     *          'code' => true,
     *          'message' => '',
     *          'data' => '',
     *      ]
     */
    public function checkGrant($access_token){
        if(empty($access_token)){
            return $this->res_data(false,'Full authentication is required to access this resource');
        }

        $this->load->library('API_JWT');
        $this->load->library('rediss');
        $this->load->library('RollingTimeWindow');

        list($flag_result,$message) = $this->api_jwt->checkTokenUser($access_token);
        if($flag_result !== true){
            return $this->res_data(false,$message);
        }

        $jwt_iss    = $message['iss'];
        $audience   = $message['aud'];
        $key        = $jwt_iss.'_'.md5($audience);// 拼接计数缓存的KEY

        // 判断当前请求的 access_token 是否和数据库中的一致，不一致则为过期token
        $userOauth = $this->Oauth_user_model->getUserByAudience($audience);
        if(empty($userOauth) or $userOauth['access_token'] != $access_token){
            return $this->res_data(false,'Expired token');
        }

        // 解析 token,判断token是否过期
        list($jwt_token_result,$message) = $this->api_jwt->decodeToken($access_token,$userOauth['audience_key']);
        if($jwt_token_result === false){
            return $this->res_data(false,$message);
        }

        // 判断是否限流
        list($flagGrant,$message) = $this->rollingtimewindow->grant($this->rediss,$key);
        if($flagGrant === true){
            return $this->res_data(true);
        }else{
            return $this->res_data(false,$message);
        }
    }



}