存储过程update_wish_week_sales：
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_wish_week_sales`()
begin
	#转移要计算的数据到临时表
	INSERT INTO  `wish_temp_ids`(`item`)
	SELECT item FROM wish_merchant_listing_bigdata
	WHERE  act_time='0000-00-00 00:00:00' LIMIT 50000;

	INSERT INTO `wish_merchant_listing_temp`(`id`,`item` ,`sales`, `rating_num`, `fetch_time`,`act_time`)
	SELECT a.`id` , a.`item` ,  a.`feed_tile_text` , a.`rating_num` , a.`fetch_time` , a.`act_time`
	FROM  `wish_merchant_listing_bigdata` a,  `wish_temp_ids` ids
	WHERE a.`item` = ids.`item`;

	TRUNCATE TABLE  `wish_temp_ids`;

	#处理只爬取了两次以上的产品
	INSERT INTO  `wish_temp_ids`(`item`)
	SELECT item FROM wish_merchant_listing_temp
	GROUP BY item having count(id)>1;

	INSERT INTO `wish_report_tmp` (
		`from_id`,`item`,`url`,`seller`, `shopname`, `rating`,`rating_num`,`title`,
		`sales`, `original_price`,`original_shipping`,`generation_time`,`display_picture`,
		`localized_price_localized_value`,`localized_shipping_localized_value` ,
		`merchant_tags`,`fetch_time`,`update_time`,`week_sales`, `week_rating`, `fetchcount` )
	SELECT
		bigdata.`from_id`,a.`item`,bigdata.`url`,bigdata.`seller`, bigdata.`shopname`,bigdata.`rating`,a.`rating_num`,bigdata.`title`,a.`sales`,
		bigdata.`original_price` ,bigdata.`original_shipping` ,	bigdata.`generation_time`,bigdata.`small_picture`,
		bigdata.`localized_price_localized_value` ,bigdata.`localized_shipping_localized_value` ,bigdata.`merchant_tags`,a.`fetch_time`, now(),
		(SELECT (a.sales-c.sales)*604800 FROM  wish_merchant_listing_temp c WHERE  c.id<>a.id  LIMIT 1) AS week_sales,
		(SELECT (a.rating_num-c.rating_num)*604800  FROM wish_merchant_listing_temp c WHERE c.id<>a.id  LIMIT 1 ) AS `week_rating`,
		( SELECT count(id) FROM wish_merchant_listing_temp c WHERE c.item=a.item GROUP BY item )  AS `fetchcount`
	FROM wish_merchant_listing_temp a,wish_merchant_listing_bigdata bigdata ,wish_temp_ids ids
	WHERE a.id=bigdata.id;

	UPDATE wish_report_tmp  SET
	`money_sales`=(`original_price`+`original_shipping`)*`sales`,
	`week_money`=(`original_price`+`original_shipping`)*`week_sales`;

	UPDATE wish_merchant_listing_bigdata bigdata, wish_temp_ids ids
	SET bigdata.act_time=now()
	WHERE bigdata.item =ids.item  and bigdata.act_time='0000-00-00 00:00:00';

	TRUNCATE TABLE  `wish_temp_ids`;
	TRUNCATE TABLE  `wish_merchant_listing_temp`;
end$$
DELIMITER ;