<?php

/**
 * Created by JoLon
 * AES128加解密类
 * User: zwl
 * Date: 2018/2/6
 * Time: 11:48
 */
class AES128Security
{
    private $_secret_key = null;// 密钥

    public function __construct($secret_key)
    {
        $this->_secret_key = md5($secret_key.md5($secret_key));
    }

    /**
     * 加密方法
     * @param string $str
     * @return string
     */
    public function encrypt($str)
    {
        //AES, 128 ECB模式加密数据
        $secret_key = $this->_secret_key;
        $secret_key = base64_decode($secret_key);
        $str = trim($str);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secret_key, $str, MCRYPT_MODE_ECB, $iv);
        return base64_encode($encrypt_str);
    }

    /**
     * 解密方法
     * @param string $str
     * @return string
     */
    public function decrypt($str)
    {
        //AES, 128 ECB模式加密数据
        $secret_key = $this->_secret_key;
        $str = base64_decode($str);
        $secret_key = base64_decode($secret_key);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secret_key, $str, MCRYPT_MODE_ECB, $iv);
        $encrypt_str = trim($encrypt_str);
        return $encrypt_str;
    }

}
