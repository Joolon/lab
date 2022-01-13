<?php
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Oauth_user_model.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'API_JWT.php';


// Oauth 2.0 grant_type方式授权

// 验证 grant_type 合法性
$grant_type = $this->input->get_post('grant_type');

if(empty($grant_type)){
    echo json_encode(['error' => 'invalid_request','error_description' => 'Missing grant type']);
    exit;
}

if($grant_type != 'client_credentials'){
    echo json_encode(['error' => 'unsupported_grant_type','error_description' => 'Unsupported grant type: '.$grant_type]);
    exit;
}

// 验证用户密码信息
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])
    || empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
    echo json_encode(['error' => 'unauthorized','error_description' => 'Full authentication is required to access this resource']);
    exit;
}

//TODO 验证用户密码是否正确
if(!$this->Oauth_user_model->checkUser($_SERVER['PHP_AUTH_USER'],md5($_SERVER['PHP_AUTH_PW']))){
    echo json_encode(['error' => 'unauthorized','error_description' => 'User information authentication failed']);
    exit;
}


$jwt_token_data = $this->api_jwt->getToken($_SERVER['PHP_AUTH_USER'],md5($_SERVER['PHP_AUTH_PW']));

// 更新数据库
$this->Oauth_user_model->updateUser($_SERVER['PHP_AUTH_USER'],
    [
        'access_token' => $jwt_token_data['token']['access_token'],
        'token_iat' => $jwt_token_data['payload']['iat'],
        'token_nbf' => $jwt_token_data['payload']['nbf'],
        'token_exp' => $jwt_token_data['payload']['exp'],
        'update_time' => date('Y-m-d H:i:s'),
    ]);

// 返回token
echo json_encode($jwt_token_data['token']);
exit;