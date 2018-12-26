<?php
namespace Libs;

/**
 * CURL 获取远程文件 并保存到本地文件夹
 * User: Jolon
 * Date: 2018/8/21
 * Time: 10:35
 */
class RequestFile
{

    /**
     * 下载并保存文件
     * @param string $request_url
     * @param string $save_path
     * @param string $filename
     * @return array|bool
     */
    public static function curlDownFile($request_url, $save_path = '', $filename = '') {
        if ( empty($request_url) OR empty($save_path)) {
            return false;
        }
        //创建保存目录
        if (!file_exists($save_path) && !mkdir($save_path, 0777, true)) {
            return false;
        }
        if (trim($filename) == '') {
            $img_ext = strrchr($request_url, '.');
            $filename = time().rand(1000,9999). $img_ext;// 随机数生成，避免文件名相同
        }
        if(substr($save_path,-1) == '/'){
            $filePath = $save_path.$filename;
        }else{
            $filePath = $save_path."/".$filename;
        }

        // curl下载文件
        $timeout = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $fileContent = curl_exec($ch);
        curl_close($ch);

        // 保存文件到制定路径
        file_put_contents($filePath, $fileContent);


        $fileSize = filesize($filePath);
        unset($img, $url);
        return array('fileName' => $filename,'filePath' => $filePath,'fileSize' => $fileSize);
    }

}
