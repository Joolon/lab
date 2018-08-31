<?php
/**
 * Created by JoLon.
 * User: Administrator
 * Date: 2017/11/21
 * Time: 11:11
 */
include 'taskbase.php';
require_once BASE_PATH."include/config.php";
require_once BASE_PATH."include/functions.php";


//计算EUB使用情况 只在星期一(monday)的早上6点执行
$startime = time();
$uptSystemTask = "update system_task set taskstatus=1,taskstarttime=" . time() . ",RunTime=0 where ID=17 ";
$dbcon->execute($uptSystemTask);
subOperation("eubrate","计算评价时间与发货时间差开始时间：[".date("Y-m-d H:i:s")."]");

//计算上次EUB使用情况(过去倒数3-4周的周日到周六)
$aa=date('Y-m-d',time());
if(date('D')=='Sun'){
    $aastart=	date('Y-m-d',strtotime("$aa -21 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -8 days"));
}else if(date('D')=='Mon'){
    $aastart=	date('Y-m-d',strtotime("$aa -22 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -9 days"));
}else if(date('D')=='Tue'){
    $aastart=	date('Y-m-d',strtotime("$aa -23 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -10 days"));
}else if(date('D')=='Wed'){
    $aastart=	date('Y-m-d',strtotime("$aa -24 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -11 days"));
}else if(date('D')=='Thu'){
    $aastart=	date('Y-m-d',strtotime("$aa -25 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -12 days"));
}else if(date('D')=='Fri'){
    $aastart=	date('Y-m-d',strtotime("$aa -26 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -13 days"));
}else if(date('D')=='Sat'){
    $aastart=	date('Y-m-d',strtotime("$aa -27 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -14 days"));
}
if(date('D')=='Sun'){
    $aastart=	date('Y-m-d',strtotime("$aa -28 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -15 days"));
}else if(date('D')=='Mon'){
    $aastart=	date('Y-m-d',strtotime("$aa -29 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -16 days"));
}else if(date('D')=='Tue'){
    $aastart=	date('Y-m-d',strtotime("$aa -30 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -17 days"));
}else if(date('D')=='Wed'){
    $aastart=	date('Y-m-d',strtotime("$aa -31 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -18 days"));
}else if(date('D')=='Thu'){
    $aastart=	date('Y-m-d',strtotime("$aa -32 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -19 days"));
}else if(date('D')=='Fri'){
    $aastart=	date('Y-m-d',strtotime("$aa -33 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -20 days"));
}else if(date('D')=='Sat'){
    $aastart=	date('Y-m-d',strtotime("$aa -34 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -21 days"));
}

$sql = "select * from ebay_account where ebay_user='$user' and not isnull(ebay_token) order by ebay_account  ";
$sql = $dbcon->execute($sql);
$sql = $dbcon->getResultArray($sql);
echo "总行数：" . count($sql) . "<br>";
for ($i = 0; $i < count($sql); $i++) {
    $ebay_account = $sql[$i]['ebay_account'];
    $usstatus = $sql[$i]['usstatus'];
    if (date('d', $aaend) <= 20) $usstatus = $sql[$i]['oldusstatus'];    //测评周期最后一天小于20号是取上月评级
    $ttt = "select ebay_id,ebay_ordersn,ebay_carrier,ebay_tracknumber from ebay_order where ebay_countryname = 'United States' and ebay_ordertype='EBAY订单' and ebay_combine!='1' and (ebay_paidtime>'" . strtotime($aastart . " 00:00:00") . "' && ebay_paidtime<'" . strtotime($aaend . " 23:59:59") . "') and ebay_account='$ebay_account'";
    if ($truename == 'otw') echo $ttt;
    $ttt = $dbcon->execute($ttt);
    $ttt = $dbcon->getResultArray($ttt);
    $totalrs = count($ttt);
    //echo $totalrs;
    $big5 = 0;
    $big5ng = 0;
    $small5 = 0;
    $small5ng = 0;
    $ngcount = 0;
    $ngebayid = "";
    $ngebayid2 = "";
    $big5ebayid = "";
    $small5ebayid = "";
    for ($t = 0; $t < $totalrs; $t++) {    //所有美国订单
        $ebay_id            = $ttt[$t]['ebay_id'];
        $ebay_carrier       = $ttt[$t]['ebay_carrier'];
        $ebay_tracknumber   = $ttt[$t]['ebay_tracknumber'];
        $ebayordersn        = $ttt[$t]['ebay_ordersn'];
        $rrr = "select ebay_itemprice,shipingfee/ebay_amount as shipcost from ebay_orderdetail where ebay_ordersn='$ebayordersn'";
        $rrr = $dbcon->execute($rrr);
        $rrr = $dbcon->getResultArray($rrr);
        $rscount = count($rrr);
        $err = "NG";
        for ($r = 0; $r < $rscount; $r++) {    //明细判断5USD处理
            $itemprice = $rrr[$r]['ebay_itemprice'] + $rrr[$r]['shipcost'];
            if ($itemprice >= 5) {    //单价大于等于5
                if (($ebay_carrier == 'EUB' || $ebay_carrier == '云途EUB线上') && stripos($ebay_tracknumber, 'K') > 0) {    //除设置EUB方式，加上是否成功申请跟踪号
                    $big5++;        //使用EUB
                    $big5ebayid .= '[' . $ebay_id . ']';
                    break;
                } else {
                    $err = "NG";
                    $ngebayid .= $ebay_id . ',';
                    $big5ng++;
                    break;
                }
            } else {        //单价小于5
                if (($ebay_carrier == 'EUB' || $ebay_carrier == '云途EUB线上') && stripos($ebay_tracknumber, 'K') > 0) {    //除设置EUB方式，加上是否成功申请跟踪号
                    $small5++;
                    $small5ebayid .= '[' . $ebay_id . ']';
                    break;
                } else {
                    $err = "OK";
                }
            }
        }
        if ($err == "OK") {
            $ngebayid2 .= $ebay_id . ',';
            $small5ng++;
        }
    }
    //计算使用率
    if ($big5ng == 0) {
        $eubuserate1 = 100;
    } else {
        $eubuserate1 = round($big5 / ($big5 + $big5ng) * 100, 2);    //5USD以上使用率
    }
    $eubuserate2 = round($small5 / ($small5 + $small5ng) * 100, 2);    //5USD以下使用率
    $eubuserate3 = round(($small5 + $big5) / ($small5 + $small5ng + $big5 + $big5ng) * 100, 2);    //综合使用率
    //更新EUB使用数据
    if ($usstatus == 'BelowStandard') {    //美国低于标准使用综合使用率
        $uptsql = "update ebay_account set lastusorderqty=" . ($small5 + $small5ng + $big5 + $big5ng) . ",lasteuborderqty=" . ($small5 + $big5) . ",lasteubperiod='" . date('m-d', strtotime($aastart)) . "-->" . date('m-d', strtotime($aaend)) . "' where ebay_account='$ebay_account'";
    } else {    //美国高于标准使用5USD以上使用率
        $uptsql = "update ebay_account set lastusorderqty=" . ($big5 + $big5ng) . ",lasteuborderqty=" . ($big5) . ",lasteubperiod='" . date('m-d', strtotime($aastart)) . "-->" . date('m-d', strtotime($aaend)) . "' where ebay_account='$ebay_account'";
    }
    if ($truename == 'otw') echo '<br>' . $uptsql;
    $dbcon->execute($uptsql);
}


//计算当前EUB使用情况(过去倒数2-3周的周日到周六)
$aa=date('Y-m-d',time());
if(date('D')=='Sun'){
    $aastart=	date('Y-m-d',strtotime("$aa -21 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -8 days"));
}else if(date('D')=='Mon'){
    $aastart=	date('Y-m-d',strtotime("$aa -22 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -9 days"));
}else if(date('D')=='Tue'){
    $aastart=	date('Y-m-d',strtotime("$aa -23 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -10 days"));
}else if(date('D')=='Wed'){
    $aastart=	date('Y-m-d',strtotime("$aa -24 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -11 days"));
}else if(date('D')=='Thu'){
    $aastart=	date('Y-m-d',strtotime("$aa -25 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -12 days"));
}else if(date('D')=='Fri'){
    $aastart=	date('Y-m-d',strtotime("$aa -26 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -13 days"));
}else if(date('D')=='Sat'){
    $aastart=	date('Y-m-d',strtotime("$aa -27 days"));
    $aaend=		date('Y-m-d',strtotime("$aa -14 days"));
}
$sql = "select * from ebay_account where ebay_user='$user' and not isnull(ebay_token) order by ebay_account  ";
$sql = $dbcon->execute($sql);
$sql = $dbcon->getResultArray($sql);
echo "总行数：" . count($sql) . "<br>";
for ($i = 0; $i < count($sql); $i++) {
    $ebay_account   = $sql[$i]['ebay_account'];
    $usstatus       = $sql[$i]['usstatus'];
    if (date('d', $aaend) <= 20) $usstatus = $sql[$i]['oldusstatus'];    //测评周期最后一天小于20号是取上月评级
    $ttt = "select ebay_id,ebay_ordersn,ebay_carrier,ebay_tracknumber from ebay_order where ebay_countryname = 'United States' and ebay_ordertype='EBAY订单' and ebay_combine!='1' and (ebay_paidtime>'" . strtotime($aastart . " 00:00:00") . "' && ebay_paidtime<'" . strtotime($aaend . " 23:59:59") . "') and ebay_account='$ebay_account'";
    if ($truename == 'otw') echo $ttt;
    $ttt = $dbcon->execute($ttt);
    $ttt = $dbcon->getResultArray($ttt);
    $totalrs = count($ttt);
    //echo $totalrs;
    $big5 = 0;
    $big5ng = 0;
    $small5 = 0;
    $small5ng = 0;
    $ngcount = 0;
    $ngebayid = "";
    $ngebayid2 = "";
    $big5ebayid = "";
    $small5ebayid = "";
    for ($t = 0; $t < $totalrs; $t++) {    //所有美国订单
        $ebay_id            = $ttt[$t]['ebay_id'];
        $ebay_carrier       = $ttt[$t]['ebay_carrier'];
        $ebay_tracknumber   = $ttt[$t]['ebay_tracknumber'];
        $ebayordersn        = $ttt[$t]['ebay_ordersn'];
        $rrr = "select ebay_itemprice,shipingfee/ebay_amount as shipcost from ebay_orderdetail where ebay_ordersn='$ebayordersn'";
        $rrr = $dbcon->execute($rrr);
        $rrr = $dbcon->getResultArray($rrr);
        $rscount    = count($rrr);
        $err        = "NG";
        for ($r = 0; $r < $rscount; $r++) {    //明细判断5USD处理
            $itemprice = $rrr[$r]['ebay_itemprice'] + $rrr[$r]['shipcost'];
            if ($itemprice >= 5) {    //单价大于等于5
                if (($ebay_carrier == 'EUB' || $ebay_carrier == '云途EUB线上') && stripos($ebay_tracknumber, 'K') > 0) {    //除设置EUB方式，加上是否成功申请跟踪号
                    $big5++;        //使用EUB
                    $big5ebayid .= '[' . $ebay_id . ']';
                    break;
                } else {
                    $err = "NG";
                    $ngebayid .= $ebay_id . ',';
                    $big5ng++;
                    break;
                }
            } else {        //单价小于5
                if (($ebay_carrier == 'EUB' || $ebay_carrier == '云途EUB线上') && stripos($ebay_tracknumber, 'K') > 0) {    //除设置EUB方式，加上是否成功申请跟踪号
                    $small5++;
                    $small5ebayid .= '[' . $ebay_id . ']';
                    break;
                } else {
                    $err = "OK";
                }
            }
        }
        if ($err == "OK") {
            $ngebayid2 .= $ebay_id . ',';
            $small5ng++;
        }
    }
    //计算使用率
    if ($big5ng == 0) {
        $eubuserate1 = 100;
    } else {
        $eubuserate1 = round($big5 / ($big5 + $big5ng) * 100, 2);    //5USD以上使用率
    }
    $eubuserate2 = round($small5 / ($small5 + $small5ng) * 100, 2);    //5USD以下使用率
    $eubuserate3 = round(($small5 + $big5) / ($small5 + $small5ng + $big5 + $big5ng) * 100, 2);    //综合使用率
    //更新EUB使用数据
    if ($usstatus == 'BelowStandard') {    //美国低于标准使用综合使用率
        $uptsql = "update ebay_account set usorderqty=" . ($small5 + $small5ng + $big5 + $big5ng) . ",euborderqty=" . ($small5 + $big5) . ",eubperiod='" . date('m-d', strtotime($aastart)) . "-->" . date('m-d', strtotime($aaend)) . "' where ebay_account='$ebay_account'";
    } else {    //美国高于标准使用5USD以上使用率
        $uptsql = "update ebay_account set usorderqty=" . ($big5 + $big5ng) . ",euborderqty=" . ($big5) . ",eubperiod='" . date('m-d', strtotime($aastart)) . "-->" . date('m-d', strtotime($aaend)) . "' where ebay_account='$ebay_account'";
    }
    if ($truename == 'otw') echo '<br>' . $uptsql;
    $dbcon->execute($uptsql);
}

$usetime = (time() - $startime) / 60;
$uptSystemTask = "update system_task set Runtime=" . time() . ",UseTime=" . $usetime . ",taskstatus=0 where ID=17 ";
$dbcon->execute($uptSystemTask);
$dbcon->close();