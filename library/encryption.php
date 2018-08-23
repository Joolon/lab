<?php

/**
 * Created by PhpStorm.
 * User: kdw
 * Date: 15-2-9
 * Time: 下午9:13
 * 加密工具类
 */
class Encryption
{

	private $encrypted;
	
	private $key;
	
	public function __construct($encrypted,$key)
    {
        //加载及加密验证信息
        $this->encrypted=$encrypted;
        $this->key=$key;
    }
	
    /*
     * des加密
     * @param $encrypted 被加密的内容
     * @param $key 密钥
     */
    public function encryption() {
        //待加密的字符串，用PKCS7填充
        $string = $this->paddingPKCS7(date('Ymd').$this->encrypted);
        //初始化向量来增加安全性
        $td = mcrypt_module_open( MCRYPT_3DES, '', MCRYPT_MODE_ECB, '' );
        $iv = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );
        mcrypt_generic_init( $td, $this->key, $iv );

        //开始加密
        $result = mcrypt_generic( $td, $string );
        return base64_encode(base64_encode($result));
    }

    /*
     * 采用ECB模式和PKCS7的补位
     */
    public function paddingPKCS7($input) {
        $srcdata = $input;
        $block_size = mcrypt_get_block_size('tripledes','ecb');
        $padding_char = $block_size - (strlen($input) % $block_size);
        $srcdata .= str_repeat(chr($padding_char),$padding_char);
        return $srcdata;
    }

    //AES加密
    public function encrypt()
    {

        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB);

        $input = $this->pkcs5_pad($this->encrypted,$size);

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'',MCRYPT_MODE_ECB,'');

        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);

        mcrypt_generic_init($td,$this->key,$iv);

        $data = mcrypt_generic($td,$input);

        mcrypt_generic_deinit($td);

        mcrypt_module_close($td);

        $data = base64_encode($data);

        return $data;
    }


    private function pkcs5_pad($text,$blocksize)
    {

        $pad = $blocksize-(strlen($text)%$blocksize);

        return $text.str_repeat(chr($pad),$pad);
    }

    //AES解密
    public function decrypt()
    {

        $decrypted = mcrypt_decrypt(

            MCRYPT_RIJNDAEL_128,

            $this->key,

            base64_decode($this->encrypted),

            MCRYPT_MODE_ECB

        );

        $dec_s = strlen($decrypted);

        $padding = ord($decrypted[$dec_s-1]);

        $decrypted = substr($decrypted,0,-$padding);

        return $decrypted;
    }
}