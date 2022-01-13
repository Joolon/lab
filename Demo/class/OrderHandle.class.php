<?php
/**
 * User: tian
 * Date: 2018/01/10
 * name: 订单处理
 */
class OrderHandle{
	/*
	$arr = array(
		array(1,23,4),
		array(5,6,7)
	)
	
	*@name 手动拆单,拆出一个或多个
	*@param 1 父单id  param2 子单id集 param3 类型（1为手动拆单业务，其余为自动拆单：分义乌仓与深圳仓）
	*@add date 2018-01-10 tian
	*/
	public static function splitOrder($ebay_id,$ebay_tid){
		global $dbcon,$truename;
		if(!isset($truename)){
			$truename='系统';
		}
		$returnMsg=array();
		$chaiDanSql = $dbcon->query('select a.ebay_ordersn,a.ebay_ordertype,a.ebay_carrier,a.orderweight,a.system_shippingcarriername,a.ebay_countryname from ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE a.ebay_id='.$ebay_id);
		$chaiDanSql	= $dbcon->getResultArray($chaiDanSql);
		
		//这些数据用于拆单后重新计算主单利润
		$ebay_countryname=$chaiDanSql[0]['ebay_countryname'];
		$system_shippingcarriername=$chaiDanSql[0]['system_shippingcarriername'];
		$ebay_carrier=$chaiDanSql[0]['ebay_carrier'];
		$orderweight=$chaiDanSql[0]['orderweight'];
		$ebay_ordersn=$chaiDanSql[0]['ebay_ordersn'];
		
		$sqldid='select a.*,b.goods_weight from ebay_orderdetail AS a LEFT JOIN ebay_goods AS b ON a.sku=b.goods_sn where a.ebay_id IN('.$ebay_tid.') and a.ebay_ordersn="'.$chaiDanSql[0]['ebay_ordersn'].'"';
		$sqldid=$dbcon->execute($sqldid);
		$sqldid	= $dbcon->getResultArray($sqldid);//需要拆出来的子单
		$cChaidanSql=count($chaiDanSql);
		$cSqldid=count($sqldid);
		if($cSqldid==$cChaidanSql){
			$returnMsg['state']='error';
			$returnMsg['msg']='不能选择所有子单';
			return $returnMsg;
		}
		if($cSqldid > 0){
			$oldOrdersn=$sqldid[0]['ebay_ordersn'];
			$tidItemPrice=0;//拆出子单的sku价格
			$tidItemAmount=0;//拆出子单的sku数目
			$newOrderShipfee=0;//新订单总运费
			$newOrderTotal=0;//新订单的总价格
			$newOrderWeight=0;//新订单总重量
			
			$sqlNewOrdersn=substr($ebay_tid,0,8);//取第一个子单的ebay_id链接ordersn
			$newOrdersn=$oldOrdersn.'-'.$sqlNewOrdersn;//新订单ordersn
			for($a=0;$a<$cSqldid;$a++){
				$newOrderTotal+=$sqldid[$a]['ebay_itemprice']*$sqldid[$a]['ebay_amount']+$sqldid[$a]['shipingfee'];
				$newOrderShipfee+=$sqldid[$a]['shipingfee'];
				$newOrderWeight+=$sqldid[$a]['ebay_amount']*$sqldid[$a]['goods_weight'];
			}
			//添加新主订单
			$addmorder="insert into ebay_order (
			ebay_user,ebay_status,ebay_ordersn,ebay_username,ebay_street,ebay_street1,ebay_city,ebay_state,ebay_countryname,
			ebay_postcode,ebay_phone,ebay_shipfee,ebay_usermail,ebay_userid,ebay_ptid,ebay_total,ebay_currency,ebay_paidtime,
			ebay_account,ebay_tracknumber,ebay_noteb,ebay_carrier,ebay_note,resendreason,refundreason,resendtime,refundtime,
			ebay_ordertype,packingtype,orderweight,ordershipfee,packinguser,ebay_warehouse,ebay_addtime,ebay_createdtime,ebay_markettime,ShippedTime,PayPalEmailAddress,recordnumber
			) select
			ebay_user,ebay_status,concat(ebay_ordersn,'-$sqlNewOrdersn'),ebay_username,ebay_street,ebay_street1,ebay_city,ebay_state,ebay_countryname,
			ebay_postcode,ebay_phone,ebay_shipfee,ebay_usermail,ebay_userid,ebay_ptid,ebay_total,ebay_currency,ebay_paidtime,
			ebay_account,concat(ebay_tracknumber,null),ebay_noteb,ebay_carrier,ebay_note,resendreason,refundreason,resendtime,refundtime,
			ebay_ordertype,packingtype,orderweight,ordershipfee,packinguser,ebay_warehouse,ebay_addtime,ebay_createdtime,ebay_markettime,ShippedTime,PayPalEmailAddress,recordnumber
			from ebay_order where ebay_id='$ebay_id'";
			$dbcon->execute($addmorder);
			//将拆除的子订单关联到新主单
			$uptdorder="update ebay_orderdetail set ebay_ordersn='$newOrdersn' where ebay_id IN($ebay_tid)";
			$dbcon->execute($uptdorder);
			//更新新订单的总价与运费,总重量
			$uptmordernew="update ebay_order set ebay_status=1,orderweight=$newOrderWeight,ebay_total=$newOrderTotal,ebay_shipfee=$newOrderShipfee where ebay_ordersn='$newOrdersn'";
			
			$dbcon->execute($uptmordernew);
			//更新旧主单的总价与运费
			$oldOrderweight=$orderweight-$newOrderWeight;//重算旧单重量
			$uptmorderold="update ebay_order set orderweight={$oldOrderweight},ebay_total=ebay_total-$newOrderTotal,ebay_shipfee=ebay_shipfee-$newOrderShipfee where ebay_ordersn='$oldOrdersn'";
			$dbcon->execute($uptmorderold);
			//新订单增加日志
			$sqlrd="select ebay_id from ebay_order where ebay_ordersn='$newOrdersn'";
			$sqlrd=$dbcon->execute($sqlrd);
			$sqlrd	= $dbcon->getResultArray($sqlrd);
			$newebay_id=$sqlrd[0]['ebay_id'];
			if($newebay_id){
				$returnMsg['state']='success';
				$returnMsg['old_id']=$ebay_id;
				$returnMsg['new_id']=$newebay_id;
				$returnMsg['msg']='拆单成功';
				//重新计算主单利润
				$fee=self::getShipFee($ebay_carrier,$oldOrderweight,$ebay_countryname,$system_shippingcarriername);
				$returnMsg['fee']=$fee;
				$profit=self::getProfit($ebay_id,$fee);
				$returnMsg['profit']=$profit;
				$upProfit="UPDATE  ebay_order SET ordershipfee={$fee},orderweight={$oldOrderweight},aa_postage={$fee},aa_profit={$profit} WHERE ebay_id={$ebay_id}";
				$dbcon->execute($upProfit);
				$notes="订单:【{$ebay_id}】发生拆单操作,订单重量更新为：【{$oldOrderweight}】,订单利润重新计算为：【{$profit}】";
				$dbcon->execute('insert into ebay_orderslog (operationuser,operationtime,notes,ebay_id,types) values("'.$truename.'",'.time().',"'.mysql_real_escape_string($notes).'",'.$ebay_id.',18)');
			}else{
				$returnMsg['state']='error';
				$returnMsg['msg']='拆单失败';
			}
			$notes= '此订单是从订单['.$ebay_id.']拆出来 操作人是:'.$truename;
			$dbcon->execute('insert into ebay_orderslog (operationuser,operationtime,notes,ebay_id) values("'.$truename.'",'.time().',"'.mysql_real_escape_string($notes).'",'.$newebay_id.')');
			//旧订单增加日志
			$notes= '子订单['.$ebay_tid.']被拆出到订单:['.$newebay_id.'] 操作人是:'.$truename;
			$dbcon->execute('insert into ebay_orderslog (operationuser,operationtime,notes,ebay_id) values("'.$truename.'",'.time().',"'.mysql_real_escape_string($notes).'",'.$ebay_id.')');
			return $returnMsg;
			
		}
		
	}
	//计算订单利润 这个方法一般用在 getShipFee下面
	public static function getProfit($ebayid,$postage){
		global $dbcon,$user,$truename;
		$vv				= 'select ebay_order.ebay_currency,ebay_order.ebay_total,ebay_order.ebay_ordersn,ebay_order.ebay_carrier,ebay_order.ebay_countryname,ebay_order.paypalfees,ebay_order.ebay_ordertype,ebay_account.default_currency,ebay_order.ebay_shipfee from ebay_order left join ebay_account on ebay_account.ebay_account=ebay_order.ebay_account where ebay_order.ebay_id ='.$ebayid.' group by ebay_order.ebay_id';
		$vv				= $dbcon->execute($vv);
		$vv				= $dbcon->getResultArray($vv);
		$ebay_total 		= $vv[0]['ebay_total'];
		$ebay_currency		= strtoupper(trim($vv[0]['ebay_currency']));
		$ebay_ordersn		= $vv[0]['ebay_ordersn'];
		$ebay_carrier		= $vv[0]['ebay_carrier'];
		$ebay_countryname 	= $vv[0]['ebay_countryname'];
		$paypalfees		 	= $vv[0]['paypalfees'];
		$ebay_ordertype		= $vv[0]['ebay_ordertype'];
		$default_currency	= strtoupper(trim($vv[0]['default_currency']));
        $ebay_shipfee = $vv[0]['ebay_shipfee'];
		$rates = 1;
		$defaultRates = 1;
		$nn				= 'select * from ebay_currency where currency in ("'.$ebay_currency.'","'.$default_currency.'") and user="'.$user.'"';
		$nn				= $dbcon->execute($nn);
		$nn				= $dbcon->getResultArray($nn);
		foreach($nn as $y){
			$y['currency'] = strtoupper(trim($y['currency']));
			if($type=='' || $type=='online'){
				if($y['currency']==$ebay_currency) {
					$rates = $y['onlinerates'];
				}
				if($y['currency']==$default_currency){
					$defaultRates = $y['onlinerates'];
				}
			}elseif($type=='offline'){
					if($y['currency']==$ebay_currency) $rates = $y['rates'];
					if($y['currency']==$default_currency) $defaultRates = $y['rates'];
			}
		}
		$ebay_total 	= $ebay_total * $rates;
		$paypalfees0	= $paypalfees * $rates;	//byzhuwf20160714 主表里的PAYPAL费用为空 paypalfees0实际为0
			

		$goods_price  = 0 ;
		$goods_weight	= 0;
		$flag_goods_price=0;	//产品成本未设置标记
			
			/* 计算产品的成本 */
		$ebayfeesFrode	= 0;
		$vv				= "select sku,ebay_amount,FinalValueFee,FeeOrCreditAmount from ebay_orderdetail where ebay_ordersn ='$ebay_ordersn'";
		$vv				= $dbcon->execute($vv);
		$vv				= $dbcon->getResultArray($vv);
		for($i=0;$i<count($vv);$i++){
			$ebay_amount			= $vv[$i]['ebay_amount'];
			$goods_sn				= $vv[$i]['sku'];
			$ebayfeesFrode += $vv[$i]['FinalValueFee'];			// add by Frode(2014.8.25)
			$paypalfeesFrode += $vv[$i]['FeeOrCreditAmount'];			// add by zhuwf(2016.6.14)
		    $sql					= "select ebay_packingmaterial,goods_cost,goods_weight  from ebay_goods where goods_sn='$goods_sn' ";
			$sql				    = $dbcon->execute($sql);
		    $sql				    = $dbcon->getResultArray($sql);

			if(count($sql)  == 0){								
				$rr			= "select goods_sncombine from ebay_productscombine where goods_sn='$goods_sn'";
				$rr			= $dbcon->execute($rr);
				$rr 	 	= $dbcon->getResultArray($rr);	
				if(count($rr) == 0){
					$goods_sncombine	= $rr[0]['goods_sncombine'];
					$goods_sncombine    = explode(',',$goods_sncombine);	
					for($e=0;$e<count($goods_sncombine);$e++){
							$pline			= explode('*',$goods_sncombine[$e]);
							$goods_sn		= $pline[0];
							$goddscount     = $pline[1] * $ebay_amount;
							$sql					= "select ebay_packingmaterial,goods_cost,goods_weight  from ebay_goods where goods_sn='$goods_sn' ";
							$sql				= $dbcon->execute($sql);
							$sql				= $dbcon->getResultArray($sql);											
								
							$ebay_packingmaterial	= $sql[0]['ebay_packingmaterial'];
							$kk			= " select * from ebay_packingmaterial where model ='$ebay_packingmaterial' ";
							$kk			= $dbcon->execute($kk);
							$kk 	 	= $dbcon->getResultArray($kk);
							//$wweight	= $kk[0]['weight'];
							$wprice		= $kk[0]['price'];			
							if($wprice==0) $wprice=0.55;	//没有设置ＳＫＵ包材时默认Ａ５成本
							
							$goods_price		+= $sql[0]['goods_cost'] * $goddscount + $wprice * $goddscount;
							if($sql[0]['goods_cost']==0) $flag_goods_price=1;		//如果有产品成本为0，设置标记，将不计算利润						
					}
				}						
			}else{
					$ebay_packingmaterial	= $sql[0]['ebay_packingmaterial'];
					$kk			= " select * from ebay_packingmaterial where model ='$ebay_packingmaterial' ";
					$kk			= $dbcon->execute($kk);
					$kk 	 	= $dbcon->getResultArray($kk);
					$wweight	= $kk[0]['weight'];
					$wprice		= $kk[0]['price'];
					if($wprice==0) {$wprice=0.55;}	//没有设置ＳＫＵ包材时默认Ａ５成本
					
					$goods_price		+= $sql[0]['goods_cost'] * $ebay_amount + $wprice * $ebay_amount;
					$goods_weight		+=  $sql[0]['goods_weight'] * $ebay_amount + $wweight * $ebay_amount;
			}
		}
		$ebayfees	= $ebayfeesFrode * $rates;	//addbyzhuwf20160714
		$paypalfees	= $paypalfeesFrode * $rates + $paypalfees0;	//addbyzhuwf20160714
						
		if($ebay_ordertype=='ALI-EXPRESS'){	//ALI订单需要扣除5%费用，还需要扣除联盟佣金3%-5%
            $ebay_total=$ebay_total*0.92;
			$profit		= $ebay_total - $ebayfees - $paypalfees - $goods_price - $postage;
		}else if($ebay_ordertype=='WISH'){	//    添加备注:wish的平台费用15%其实就是 $ebayfees(产品的15%)  $paypalfees(运费的15%) 还需要扣除PP费2%
            $ebay_total=$ebay_total*0.98;
			$profit		= $ebay_total - $ebayfees - $paypalfees - $goods_price - $postage;
		}else if($ebay_ordertype=='LAZADA'){ //LAZADA订单需要扣除12%费用
            $ebay_total=$ebay_total*0.88;
		    $profit		= $ebay_total - $ebayfees - $paypalfees - $goods_price - $postage;
		}else if($ebay_ordertype=='AMAZON'){	//AMAZON订单需要扣除15%费用，不足$1USD按$1USD收
			if($ebay_total*0.15 < $rates){
				if($ebay_currency == 'GBP') {
					$rates = 0.4*$rates;
				}
					
				if($ebay_currency == 'EUR') {
					$rates = 0.5*$rates;
				}
				$ebay_total     = $ebay_total - $rates;
				$profit		= $ebay_total - $ebayfees - $paypalfees - $goods_price - $postage;
			}else{
                $ebay_total     = $ebay_total*0.85;
				$profit		= $ebay_total - $ebayfees - $paypalfees - $goods_price - $postage;
			}
		}else if($ebay_ordertype=='1688'){
            $ebay_total = $ebay_total+$ebay_shipfee;
            $profit		= $ebay_total - $ebayfees - $paypalfees - $goods_price - $postage;
        }else{
			$profit		= $ebay_total - $ebayfees - $paypalfees - $goods_price - $postage;
		}
		if($flag_goods_price==1 || $postage==0){
			$profit=0;
		}//如果有产品成本为0，有标记，或运费为0，将不计算利润
                    
        $file_url=$_SERVER['PHP_SELF'];
        $time=time();
        $sSql="INSERT save_kuiben(ebay_id,ebay_total,ebay_fees,paypal_fees,package_cost,shipping_fees,file_url,aa_profit,save_time) VALUES($ebayid,$ebay_total,$ebayfees,$paypalfees,$goods_price,$postage,'$file_url',$profit,$time)";
        $dbcon->query($sSql);   	
		return number_format($profit,2,'.','');
	}
	//计算订单运费
	public static function getShipFee($ebay_carrier,$kg,$ebay_countryname,$carrier_id){
		global $dbcon;
	
		$kg				= $kg * 1000;
	
		$hh				= "select id from ebay_carrier where name ='$ebay_carrier' ";
		$hh				= $dbcon->execute($hh);
		$hh				= $dbcon->getResultArray($hh);
		$shippingid				= $hh[0]['id'];
	
		//modbyzhuwf20160226
		//$ss				= " select type from ebay_systemshipfee where shippingid ='$shippingid' ";
		//$ss				= " select type from ebay_systemshipfee where shippingid ='$shippingid' and $kg BETWEEN aweightstart AND aweightend";
		$ss				= " select type from ebay_systemshipfee where shippingid ='$shippingid' and $kg BETWEEN aweightstart AND aweightend and ((acountrys like '%$ebay_countryname%' or acountrys like '%,any,%' ) or (bcountrys like '%$ebay_countryname%' or bcountrys like '%,any,%'))";
		$ss				= $dbcon->execute($ss);
		$ss				= $dbcon->getResultArray($ss);
	
		$type			= $ss[0]['type'];
	
		$shipfee		= 0;
		if($type 		== 0){
			$vv				= "select ashipfee,ahandlefee from ebay_systemshipfee where $kg between aweightstart and aweightend and (acountrys like '%$ebay_countryname%' or acountrys like '%,any,%' )  and shippingid ='$shippingid'";
			//echo $vv;
			$vv				= $dbcon->execute($vv);
			$vv				= $dbcon->getResultArray($vv);
			$shipfee		= $vv[0]['ashipfee'] + $vv[0]['ahandlefee'];
	
		}else{
			$vv				= "select * from ebay_systemshipfee where  (bcountrys like '%$ebay_countryname%' or bcountrys like '%,any,%') and shippingid ='$shippingid' AND $kg between aweightstart and aweightend ";
			//echo $vv;
			$vv				= $dbcon->execute($vv);
			$vv				= $dbcon->getResultArray($vv);
				
			//print_r($vv);
				
			$bfirstweight				= $vv[0]['bfirstweight'];//首重
			$bfirstweightamount			= $vv[0]['bfirstweightamount'];//首重费用
			$bnextweight				= $vv[0]['bnextweight'];//续重
			$bnextweightamount			= $vv[0]['bnextweightamount'];//续重费用
			$bhandlefee					= $vv[0]['bhandlefee'];//处理费
			$bdiscount					= $vv[0]['bdiscount']?$vv[0]['bdiscount']:1;//折扣
				
			$dis_sql = "SELECT id,discount,registed_discount
						FROM `system_shippingqudao`
						WHERE ebay_carrier = '".$ebay_carrier."'
						AND shippingcarrierid = '".$carrier_id."'
						";
			$dis_query = $dbcon->execute($dis_sql);
			$dis_val = $dbcon->getResultArray($dis_query);
            $bdiscount2 = $dis_val['0']['discount']?$dis_val['0']['discount']:1;
			if($bdiscount<=0) $bdiscount = 1;
			//挂号费用 物流渠道折扣
			if($dis_val['0']['registed_discount'] == 1) {
				$bhandlefee = $bhandlefee*$bdiscount2;
			}
			//echo 'KG='.$kg.' First weigth='.$bfirstweight;
			if($kg <= ($bfirstweight)){
				//$shipfee	= $bfirstweightamount + $bhandlefee;
				$shipfee	= $bfirstweightamount;
				//$shipfee	= $bfirstweight*$bfirstweightamount*$bdiscount;
			}else{
				$shipfee	+= ceil((($kg-$bfirstweight)/$bnextweight))*$bnextweightamount ;
				//$shipfee	 = $shipfee + $bfirstweightamount + $bhandlefee;
				$shipfee	 = $shipfee + $bfirstweightamount;
			}
			//echo $shipfee.'/'.$kg.'/'.$bfirstweight.'/'.$bnextweight.'/'.$bnextweightamount.'/'.$bdiscount.'<br>';
//			$shipfee				= $shipfee * $bdiscount;
			$shipfee = $shipfee*$bdiscount + $bhandlefee*1;// 运费用发货方式
		}
		return round($shipfee,2);
	}
	
	/*
	@name 拆分单个子订单
	@param1  主订单id
	@param2  拆分数量的子订单id
	@param3  需要拆出新子单的数量
	*/
	public static function splitQty($ebay_id,$ebay_sid,$qty){
		global $dbcon,$truename;
		$return['new_res']='<b style="color:red;font-size:14px;">添加新子单失败</b>';
		$return['old_res']='<b style="color:red;font-size:14px;">更新旧子单数据失败</b>';
		$sql='SELECT * FROM ebay_orderdetail WHERE ebay_id='.$ebay_sid;
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		$ordersn		=	$sql[0]['ebay_ordersn'];
		$sku			=	$sql[0]['sku'];
		$itemprice		=	$sql[0]['ebay_itemprice'];
		$itemtitle		=	$sql[0]['ebay_itemtitle'];
		$shipfee		=	$sql[0]['shipingfee'];//子单总运费
		$amount			=	$sql[0]['ebay_amount'];//子单总数量
		$cjfee			=	$sql[0]['FeeOrCreditAmount'];//总成交费
		$ppfee			=	$sql[0]['FinalValueFee'];//总pp费用
		unset($sql);
		//计算单个产品的各项费用
		$one_shipfee	=	$shipfee / $amount;
		$one_cjfee		=	$cjfee / $amount;
		$one_ppfee		=	$ppfee / $amount;
		//添加拆出的新子单
		$new_shipfee	=	$one_shipfee * $qty;
		$new_cjfee		=	$one_cjfee * $qty;
		$new_ppfee		=	$one_ppfee * $qty;
		$new_order		=	'INSERT ebay_orderdetail(ebay_ordersn,sku,ebay_itemprice,ebay_amount,shipingfee,FeeOrCreditAmount,FinalValueFee) VALUES("'.$ordersn.'","'.$sku.'",'.$itemprice.','.$qty.','.$new_shipfee.','.$new_cjfee.','.$new_ppfee.')';
		//更新旧子单的数据
		$old_qty=$amount - $qty;
		$old_shipfee	=	$one_shipfee * $old_qty;
		$old_cjfee		=	$one_cjfee * $old_qty;
		$old_ppfee		=	$one_ppfee * $old_qty;
		$old_order		=	'UPDATE ebay_orderdetail SET ebay_amount='.$old_qty.',shipingfee='.$old_shipfee.',FeeOrCreditAmount='.$old_cjfee.',FinalValueFee='.$old_ppfee.' WHERE ebay_id='.$ebay_sid;
		$dbcon->execute($new_order);
		$sql=$dbcon->query("SELECT ebay_id FROM ebay_orderdetail WHERE ebay_ordersn='{$ordersn}' AND sku='{$sku}' ORDER BY ebay_id DESC ");
		$sql=$dbcon->getResultArray($sql);
		if(count($sql)>1){//添加新子单成功
			$return['new_res']='<b style="color:green;font-size:14px;">添加新子单成功</b>';
			$new_ebayid=$sql[0]['ebay_id'];
			$notes = "子订单【{$ebay_sid}】的产品数量被拆出【{$qty}】个,新的子订单号为【{$new_ebayid}】";
			$new_log=$dbcon->execute('insert into ebay_orderslog (operationuser,operationtime,notes,ebay_id,types) values("'.$truename.'",'.time().',"'.mysql_real_escape_string($notes).'",'.$ebay_id.',18)');
		}
		if($dbcon->execute($old_order)){
			$return['old_res']='<b style="color:green;font-size:14px;">更新旧子单数据成功</b>';
			$notes = "子订单【{$ebay_sid}】产品原数量【{$amount}】个,被拆出【{$qty}】个,剩余【{$old_qty}】";
			$old_log=$dbcon->execute('insert into ebay_orderslog (operationuser,operationtime,notes,ebay_id,types) values("'.$truename.'",'.time().',"'.mysql_real_escape_string($notes).'",'.$ebay_id.',18)');
		}
		return $return;
	}
}