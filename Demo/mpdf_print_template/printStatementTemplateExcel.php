<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<?php
    $data = json_decode(file_get_contents('php://input'), true);
    if(empty($data)){
        echo '数据缺失';exit;
    }
    $items_list   = $data['items_list'];
    $summary_list = $data['summary_list'];
    unset($data['items_list'],$data['summary_list']);
    $statement_data = $data;

	function format_third_point_price($price){
	    // 去除小数点末尾的0(可以去除 浮点数或字符串类型的浮点数)
        if(is_float($price) or preg_match("/^[\d]+\.[\d]+$/",$price)){
            return floatval($price);
        }else{
            return $price;
        }
    }
    /**
     * 切割 中文字符，每行按指定字符数排列
     * @param $str
     * @return array|string
     */
    function mb_str_split($str){
        return $str;
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        $arr = array_chunk($arr,8);
        $str = [];
        foreach($arr as $value){
            $str[] = implode('',$value);
        }
        $str = implode("<br/>",$str);
        return $str;
    }

    /**
     * 仓库名称替换为 城市简称
     * @author Jolon
     * @param $warehouse_name
     * @return mixed|string
     */
    function get_city_warehouse_name($warehouse_name){
        preg_match('/(虎门|慈溪|塘厦)/',$warehouse_name,$matches);
        if(isset($matches[0]) and isset($matches[1])){
            return $matches[1];
        }else{
            return $warehouse_name;
        }
    }

    function make_semiangle($str){
        $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4','５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E','Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O','Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T','Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y','Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd','ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i','ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n','ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y', 'ｚ' => 'z','(' => '(', ')' => ')', '〔' => '[', '〕' => ']', '【' => '[','】' => ']', '〖' => '[', '〗' => ']', '“' => '"', '”' => '"','‘［' => '[', '］' => ']', '｛' => '{', '｝' => '}', '《' => '<','》' => '>','％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-','：' => ':', '。' => '.', '、' => ',', '，' => '.', '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',  '｀' => '`', '‘' => '`', '｜' => '|', '〃' => '"','　' => ' ',''=> '',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'',''=>'','	'=>'');
        return strtr($str, $arr);
    }
?>
<body>
<div>
    <div style="position:relative;">
        <div style="font-weight: bold;text-align:center;font-size: 4mm;"><?php echo $statement_data['purchase_name_cn'] ?></div>
        <div style="font-weight: bold;font-size: 6mm;">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;
            采购对账单
            <span style="font-size: 3.5mm;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;
                <?php echo $statement_data['statement_number'] ?></span>
        </div>
    </div>
    <div style="justify-content: center;text-align:center;width: 220mm;">
        <table border="1" style="text-align: center;border-spacing: 1px;border-collapse: collapse;font-size: 3.5mm;width: 220mm;">
            <tr class="ui-base-tr">
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="3">供应商：<?php echo $statement_data['supplier_name'] ?></td>
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="6">供应商地址：<?php echo $statement_data['supplier_address'] ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="3">收款名称：<?php echo $statement_data['sup_account_name']; ?></td>
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="6">结算方式：<?php echo $statement_data['settlement_method_cn']; ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="3">联系方式：<?php echo $statement_data['sup_phone_number']; ?></td>
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="6">支付方式：<?php echo $statement_data['pay_type_cn'] ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="3">开户行名称：<?php echo $statement_data['sup_bank'] ?></td>
                <td style="line-height: 15px;padding-left: 3px;" align="left" colspan="6">对账联系人：<?php echo $statement_data['create_user_name'] ?>&nbsp;<?php echo $statement_data['create_user_phone'] ?></td>
            </tr>
            <tr class="ui-base-tr">
                <td style="line-height: 15px;padding-left: 3px;border-bottom:0px solid #fff;" align="left" colspan="3">收款账号：<?php echo $statement_data['sup_account'] ?></td>
                <td style="line-height: 15px;padding-left: 3px;border-bottom:0px solid #fff;" align="left"  colspan="6">对账日期：<?php echo substr($statement_data['create_time'],0,10); ?></td>
            </tr>
        </table>
        <table border="1" style="text-align: center;border-spacing: 1px;border-collapse: collapse;font-size: 3.5mm;">
            <tr class="ui-base-tr">
                <td style="width: 25mm;">采购单号</td>
                <td style="width: 25mm;">sku</td>
                <td style="width: 78mm;">产品名称</td>
                <td style="width: 12mm;">采购<br />仓库</td>
                <td style="width: 12mm;">下单<br />数量</td>
                <td style="width: 12mm;">入库<br />数量</td>
                <td style="width: 16mm;">单价</td>
                <td style="width: 17mm;">入库<br />金额</td>
                <td style="width: 23mm;padding-right: 3px;">入库日期</td>
            </tr>
            <?php foreach ($items_list as  $key => $value) { ?>
                <tr>
                    <td><?php echo $value['purchase_number']?></td>
                    <td style="padding-left: 5px;padding-right: 5px;"><?php echo "=\"".strval($value['sku'])."\""?></td>
                    <td align="left" style="margin-left: 2px;padding-right: 3px;line-height: 1;">
                        <?php echo  make_semiangle(mb_str_split($value['product_name']))?>
                    </td>
                    <td><?php echo get_city_warehouse_name($value['warehouse_name'])?></td>
                    <td><?php echo $value['real_confirm_amount']?></td>
                    <td><?php echo $value['instock_qty']?></td>
                    <td><?php echo format_third_point_price($value['purchase_unit_price'])?></td>
                    <td><?php echo format_third_point_price($value['instock_price'])?></td>
                    <td><?php echo str_replace('-','/',substr($value['instock_date'],0,10))?></td>
                </tr>
            <?php  } ?>
            <tr >
                <td></td>
                <td></td>
                <td></td>
                <td>总计：</td>
                <td></td>
                <td style="color:red"><?php echo array_sum(array_column($items_list,'instock_qty')) ?></td>
                <td></td>
                <td style="color:red" colspan="2"><?php echo format_third_point_price(array_sum(array_column($items_list,'instock_price'))) ?></td>
            </tr>
        </table>
    </div>
    <div style="width: 220mm; padding: 20px;font-size: 3.5mm;" border="1">
        <p align="left" style="font-size: 3.5mm;" ><b>对账汇总</b></p>
        <table border="1" style="text-align: center;border-spacing: 1px;border-collapse: collapse;  font-size: 3.5mm;">
            <tr class="ui-base-tr">
                <td style="width: 30mm;">po下单金额</td>
                <td style="width: 30mm;">po入库金额</td>
                <td style="width: 30mm;">po已付商品额</td>
                <td style="width: 30mm;">异常入库金额</td>
                <?php if($statement_data['is_drawback'] == 0){ ?>
                    <td style="width: 30mm;">可请款商品额</td>
                    <td style="width: 30mm;">优惠额</td>
                    <td style="width: 30mm;">可请款总额</td>
                <?php }else { ?>
                    <td style="width: 30mm;">已抵扣商品额</td>
                    <td style="width: 30mm;">可请款总额</td>
                <?php } ?>
            </tr>
            <tr>
                <td><?php echo format_third_point_price(array_sum(array_column($summary_list,'real_confirm_amount_price'))) ?></td>
                <td><?php echo format_third_point_price(array_sum(array_column($summary_list,'total_instock_price'))) ?></td>
                <td><?php echo  format_third_point_price(array_sum(array_column($summary_list,'paid_product_money'))) ?></td>
                <td><?php echo format_third_point_price(array_sum(array_column($summary_list,'loss_product_money'))) ?></td>
                <?php if($statement_data['is_drawback'] == 0){ ?>
                    <td class="ui-quarter-td"><?php echo format_third_point_price(array_sum(array_column($summary_list,'instock_price_after_charge_against'))
                                                                                  + array_sum(array_column($summary_list,'loss_product_money'))) ?></td>
                    <td class="ui-quarter-td"><?php echo format_third_point_price(array_sum(array_column($summary_list,'order_discount'))) ?></td>
                    <td class="ui-quarter-td"><?php echo format_third_point_price( array_sum(array_column($summary_list,'instock_price_after_charge_against'))
                                                                                   + array_sum(array_column($summary_list,'loss_product_money'))
                                                                                   - array_sum(array_column($summary_list,'order_discount'))) ?></td>
                <?php }else { ?>
                    <td class="ui-quarter-td"><?php echo format_third_point_price(array_sum(array_column($summary_list,'ca_amount'))) ?></td>
                    <td class="ui-quarter-td"><?php echo format_third_point_price( array_sum(array_column($summary_list,'instock_price_after_charge_against'))
                                                                                   + array_sum(array_column($summary_list,'loss_product_money'))) ?></td>
                <?php } ?>
            </tr>
        </table>
        <div style="width: 190mm; text-align:left;font-size:3.5mm" >
            <?php if($statement_data['is_drawback'] == 0){ ?>
                <div class="mb5"><b>注意：</b> po已付商品额：包含了抵扣商品额<?php echo format_third_point_price(array_sum(array_column($summary_list,'ca_amount'))) ?>元。</div>
                <div class="mb5"> po入库金额：迄今该po所有的入库总额</div>
                <div class="mb5"> 异常入库金额：未能在入库明细中体现的，易佰入库的金额</div>
                <div class="mb5"> 可请款商品额：=(异常入库金额+入库金额)-po已付商品额</div>
                <div class="mb5"> 可请款金额：=可请款商品额-优惠金额</div>
                <!-- <div class="mb5">公司对账的基本原则是：(已付商品额+本次申请的商品额)不能超过入库金额，超出了部分才可以申请请款 </div> -->
            <?php }else { ?>
                <div class="mb5"><b>注意：</b> po已付商品额：包含了抵扣商品额</div>
                <div class="mb5">po入库金额：迄今该po所有的入库总额</div>
                <div class="mb5">异常入库金额：未能在入库明细中体现的，易佰入库的金额</div>
                <div class="mb5">可请款商品额：公司对账的基本原则是：(已付商品额+可请款金额)不能超过po入库金额</div>
                <div class="mb5">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp可请款金额=(异常入库金额+入库金额)-po已付商品额</div>
            <?php } ?>
        </div>
    </div>
    <div  style="width: 190mm; text-align: left;font-size: 3.5mm"border="1">
        <p class="ui-table-font-size-5"><b>对账要求</b>
        <div class="mb5">1.贵司在收到我司对账单时,须在2个工作日内进行签字确认盖章回传。</div>
        <div class="mb5">2.对账单签署处须用印,用印须为公章或财务专用章,不接受电子章、业务专用章、发票章。</div>
        <div class="mb5">3.页数≥2的对账单须加盖骑缝章，除了签字栏盖章，须再加盖骑缝章。</div>
        <div class="mb5">4.需要开票的供应商,请在付款前将发票寄到我司,否则将逾期付款,发票邮寄时拒绝到付,邮寄地址:深圳市龙岗区坂田街道里浦街7号TOD科技中心易佰大厦2楼采购部 199 2535 7932 党小婷收。</div>
        <div class="mb5">5.以上请大家积极配合并遵照执行,如未能按述安排操作,我司内审核流程将无法顺利通过,由此导致的无法正常付款/延迟等不利结果将由贵司自行承担。</div>
        </p>
    </div>
    <div style="text-align: right;padding-top:10mm;font-size: 4mm">
        <div class="mr20" style="margin-bottom: 5mm;"><span>供应商名称：</span><span><?php echo $statement_data['supplier_name']?></span></div>
        <div class="mr20" style="margin-bottom: 5mm;"><span>签字盖章：</span><span style="color: white;visibility: hidden" ><?php echo $statement_data['supplier_name']?></span></div>
        <div class="mr20" style="margin-bottom: 5mm;"><span>日期：</span><span style="color:white;visibility: hidden"><?php echo $statement_data['supplier_name']?></span></div>
    </div>
</div>
</body>
</html>
