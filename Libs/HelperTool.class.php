<?php
namespace Libs;

/**
 * CURL 获取远程文件 并保存到本地文件夹
 * User: Jolon
 * Date: 2018/8/21
 * Time: 10:35
 */
class HelperTool
{


    /**
     * 判断$_SERVER是否携带用户权限信息
     */
    public function httpCheck(){
        // HTTP 认证机制（只有在PHP作为Apache模块时才有效，CGI模式无效）
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            // 向浏览器发送认证请求(实名认证)，输入的用户名、密码和认证类型保存在$_SERVER和$HTTP_SERVER_VARS中
            header('WWW-Authenticate:Basic realm="My Realm"');// Basic B要大写，否则可能浏览器不兼容
            header('HTTP/1.0 401 Unauthorized');
            echo 'Text to send if user hits Cancel button';
            exit;
        } else {
            echo "<p>HELLO {$_SERVER['PHP_AUTH_USER']}.</p>";
            echo "<p>YOU Entered {$_SERVER['PHP_AUTH_PW']} AS YOU  PASSWORD.</p>";

        }
    }


    /**
     * 从远程页面中获取指定SKU的图片地址信息
     *
     * @param $skuCode
     * @return mixed|string
     */
    public function spliceImageLink($skuCode){
        session_write_close();// AJAX批量调用时 异步返回数据

        global $dbcon;

        $skuCode = strtoupper($skuCode);
        $goods_pic = '';
        $goods_imgs = '';

        // 先从 新产品开发资料中读取已经存在的图片地址
        $newgoods = "SELECT goods_sn,goods_pic,goods_imgs FROM ebay_newgoods WHERE goods_sn='$skuCode' LIMIT 1";
        $newgoods = $dbcon->query($newgoods);
        $newgoods = $dbcon->getResultArray($newgoods);
        $newgoods = isset($newgoods[0])?$newgoods[0]:'';
        if(!empty($newgoods['goods_pic'])){
            $hostUrl   = 'https://61.145.158.170:187';// 服务器地址
            if(strpos($newgoods['goods_pic'],'http') === false){
                $goods_pic = $hostUrl.$newgoods['goods_pic'];
            }else{
                $goods_pic = $newgoods['goods_pic'];
            }
            $goods_imgs = empty($newgoods['goods_imgs'])?'':$newgoods['goods_imgs'];
            if($goods_imgs){
                if(strpos($goods_imgs,'http') === false){
                    $goods_imgs = str_replace('/netimg/',$hostUrl.'/netimg/',$goods_imgs);
                }
            }else{
                // 从图片明细中读取所有图片地址
                $more_imgs = "SELECT picture_url2,picture_url3,picture_url4 FROM ebay_newgoodspic WHERE goods_sn='$skuCode' LIMIT 1 ";
                $more_imgs = $dbcon->query($more_imgs);
                $more_imgs = $dbcon->getResultArray($more_imgs);
                $more_imgs = $more_imgs[0];
                $more_imgs = empty($more_imgs['picture_url2'])?$more_imgs['picture_url2']:($more_imgs['picture_url3'].';'.$more_imgs['picture_url4']);
                if($more_imgs != ';'){
                    $dbcon->update("UPDATE ebay_newgoods SET goods_imgs='$more_imgs' WHERE goods_sn='$skuCode' LIMIT 1 ");
                    $goods_imgs = str_replace('/netimg/',$hostUrl.'/netimg/',$more_imgs);
                }
            }
            $dbcon->update("UPDATE ebay_goods SET goods_pic='$goods_pic',goods_imgs='$goods_imgs' WHERE goods_sn='$skuCode' LIMIT 1  ");
            return $goods_pic;
        }


        // 从美国服务器上读取图片地址
        //$baseUrl = 'https://www.eshopimage.com/netimg/';// 美国服务器地址
        $baseUrl = 'xxxx';// 不获取美国服务器图片
        $skuInfo = "SELECT upper(e_g.goods_sn) as goods_sn,FROM_UNIXTIME(e_g.addtim,'%Y%m') AS adddate FROM ebay_goods AS e_g 
        WHERE e_g.parent_sku=(SELECT parent_sku FROM ebay_goods WHERE goods_sn='$skuCode' ) OR e_g.goods_sn='$skuCode'
        GROUP BY e_g.goods_sn ORDER BY e_g.goods_sn ASC ";
        $skuInfo = $dbcon->query($skuInfo);
        $skuInfo = $dbcon->getResultArray($skuInfo);
        $skus = get_array_column($skuInfo,'goods_sn','goods_sn');
        natsort($skus);

        $adddate    = $skuInfo[0]['adddate'];
        if(count($skus) > 1){// 含有父SKU的 文件夹为 起始和终止SKU的组合
            $start      = current($skus);
            $end        = end($skus);
            $mainImg    = $start.'-'.$end;
            $dir        = $adddate.'/'.$start.'-'.$end.'/';
        }else{
            $start      = current($skus);
            $mainImg    = $start;
            $dir        = $adddate.'/'.$start.'/';
        }

        $dirpath        = $baseUrl.$dir;// 拼接SKU 图片所在文件夹
        $contents       = file_get_contents($dirpath);
        preg_match_all('/<a href="[^"]*"[^>]*>(.*)<\/a>/',$contents,$matchs);
        $files = $matchs[1];
//    print_r($files);exit;
        if($files){
            $picture_url = array();
            $picture_url3 = array();// 父SKU下所有主图
            $picture_url4 = array();// 父SKU下所有幅图
            foreach($files as $key => $name){
                if($key <=1 ) continue;

                $valPicUrlTmp = strtoupper($name);
                if(strrpos($valPicUrlTmp,'/') !== false){
                    $valPicUrlTmp = substr($valPicUrlTmp,strrpos($valPicUrlTmp,'/')+1);// 获取图片名称
                }

                // 去除图片名后缀
                if(strrpos($valPicUrlTmp,'.JPG'))
                    $valPicUrlTmp = substr($valPicUrlTmp,0,strrpos($valPicUrlTmp,'.JPG'));
                elseif(strrpos($valPicUrlTmp,'.PNG'))
                    $valPicUrlTmp = substr($valPicUrlTmp,0,strrpos($valPicUrlTmp,'.PNG'));
                elseif(strrpos($valPicUrlTmp,'.GIF'))
                    $valPicUrlTmp = substr($valPicUrlTmp,0,strrpos($valPicUrlTmp,'.GIF'));

                $nowSku = $valPicUrlTmp;
                $indexC = strpos($valPicUrlTmp,'-C');
                if($indexC !== false){// 主图
                    $picture_url3[] = $dirpath.$name;
                    $nowSku = substr($valPicUrlTmp,0,$indexC);
                }
                $indexD = strpos($valPicUrlTmp,'-D');
                if($indexD !== false){// 幅图
                    $picture_url4[] = $dirpath.$name;
                    $nowSku = substr($valPicUrlTmp,0,$indexD);
                }

                if($skuCode == $nowSku){// SKU对应的图片
                    $picture_url[$skuCode][] = $dirpath.$name;
                }
            }

            if(!empty($picture_url[$skuCode][0])){
                $goods_pic = $picture_url[$skuCode][0];
            }elseif(!empty($picture_url3[0])){
                $goods_pic = $picture_url3[0];
            }elseif(!empty($picture_url4[0])){
                $goods_pic = $picture_url4[0];
            }
            $goods_imgs = $picture_url[$skuCode] + $picture_url3 + $picture_url4;
            $goods_imgs = implode(';',$goods_imgs);
        }


//    print_r($goods_imgs);
//    print_r($goods_pic);
//    exit;
        // 从预售产品处获取资料图
        if(empty($goods_pic)){// 图片库中未找到图片则在预售产品里面寻找
            // WISH预售
            $sqlWish = "SELECT sku,mainimg,images from wishhotproducts where sku='$skuCode' LIMIT 1 ";
            $sqlWish = $dbcon->query($sqlWish);
            $sqlWish = $dbcon->getResultArray($sqlWish);
            if(isset($sqlWish[0]) AND $sqlWish[0]){
                if($sqlWish[0]['mainimg']){
                    $url = $sqlWish[0]['mainimg'];
                }else{
                    $images = $sqlWish[0]['images'];
                    $images = str_replace('，', ',', $images);
                    $images = explode(',', $images);
                    $imgarr = array();
                    foreach ($images as $img) {
                        if (strpos($img, '.jpg') == (strlen($img) - 4) && !strpos($img, '-large.jpg')) {
                            $imgarr[] = str_replace('.jpg', '', $img);
                        } else {
                            $imgarr[] = $img;
                        }
                    }
                    $url = $imgarr[0];
                }
            }
            else{
                // EBAY预售
                include_once "../dbconnecterp.php";
                $erpconn = new DBClasserp();
                $sqlEbay = "SELECT id,itemid,title,sku,variations_status,pic_details,shipping_details
                FROM `frode_download_listing`
                WHERE sku='$skuCode' LIMIT 1 ";
                $sqlEbay = $erpconn->query($sqlEbay);
                $sqlEbay = $erpconn->getResultArray($sqlEbay);
                if(isset($sqlEbay[0]) AND $sqlEbay[0]){
                    $pic_details    = $sqlEbay[0]['pic_details'];
                    $pic_details    = json_decode($pic_details, true);
                    $mainImg        = $pic_details['GalleryURL'];
                    $imgArr         = $pic_details['PictureURL'];
                    $url            = $mainImg?$mainImg:current($imgArr);// 获取主图或第一张附图
                }
            }
        }

        if($goods_pic){
            $dbcon = new DBClass();
            $update = "UPDATE ebay_goods SET goods_pic='$goods_pic' ";
            if($goods_imgs){
                $update .= ",goods_imgs='$goods_imgs' ";
            }
            $update .= " WHERE goods_sn='$skuCode' LIMIT 1 ";
            $dbcon->update($update);
        }

        return $goods_pic;
    }


}
