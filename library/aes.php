<?php

/**
 * AES128加解密类
 * User：Jolon
 */
class Aes
{

    public $_secrect_key; // 密钥

    public function __construct()
    {
        $this->_secrect_key = 'MYgGnQE2+DAS973vd1DFHg==';
    }

    /**
     * 加密方法
     * @param string $str
     * @return string
     */
    public function encrypt($str)
    {
        // AES, 128 ECB模式加密数据
        $screct_key = $this->_secrect_key;
        $screct_key = base64_decode($screct_key);
        $str = trim($str);
        $str = $this->addPKCS7Padding($str);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_ECB, $iv);
        return base64_encode($encrypt_str);
    }

    /**
     * 解密方法
     * @param string $str
     * @return string
     */
    public function decrypt($str)
    {
        // AES, 128 ECB模式加密数据
        $screct_key = $this->_secrect_key;
        $str = base64_decode($str);
        $screct_key = base64_decode($screct_key);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_ECB, $iv);
        $encrypt_str = trim($encrypt_str);
        $encrypt_str = $this->stripPKSC7Padding($encrypt_str);
        return $encrypt_str;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    public function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block = mcrypt_get_block_size('rijndael-128', 'ecb');
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }

    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    public function stripPKSC7Padding($source)
    {
        $source = trim($source);
        $char = substr($source, -1);
        $num = ord($char);
        if ($num == 62) return $source;
        $source = substr($source, 0, -$num);
        return $source;
    }
}