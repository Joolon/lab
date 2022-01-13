<?php

// 认证是否需要限流
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Oauth_user_model.php';

// 需要修改 .htaccess 文件增加一行： SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0
$access_token = $this->input->get_post('access_token');
$this->load->model('user/Oauth_user_model');

$flagGrant = (new Oauth_user_model())->checkGrant($access_token);

print_r($flagGrant);
exit;