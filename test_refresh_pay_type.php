<?php
set_time_limit(0);


$arr = [ 

'PO10279000',
'PFB10121232',
'PO10277159',
'FBA10170024',
'PO10275179',
'PO10274461',
'PO10274318',
'PO10274499',
'PFB10119281',
'PO10273354',
'PFB10119711',
'PFB10117601',
'PO10271638',
'PFB10118858',
'PFB10118868',
'PO10270506',
'PFB10117639',
'PFB10118529',
'PFB10118528',
'PFB10118558',
'PFB10118572',
'PFB10118564',
'PFB10118934',
'PFB10118962',
'FBA10167002',
'PO10270208',
'PFB10116014',
'FBA10165471',
'PFB10117080',
'PFB10116757',
'PFB10116881',
'FBA10164930',
'PFB10114240',
'PFB10114987',
'PO10268838',
'PFB10115427',
'PFB10115124',
'PFB10115529',
'PFB10112839',
'PFB10112844',
'PFB10113455',
'PO10267663',
'PFB10112444',
'PO10267300',
'PFB10113700',
'PFB10112466',
'PFB10114015',
'PFB10112403',
'PFB10112411',
'PO10265303',
'PO10265684',
'PO10265980',
'PO10265963',
'PO10264946',
'PO10266026',
'PO10266021',
'PO10265548',
'PO10265951',
'PO10264613',
'PO10264094',
'PFB10111176',
'PFB10112052',
'PO10262865',
'FBA10161212',
'PFB10110842',
'PFB10112129',
'PFB10110871',
'PFB10111893',
'PFB10112036',
'PFB10110520',
'PO10262689',
'FBA10160554',
'PFB10109324',
'PFB10109341',
'FBA10159985',
'PFB10109265',
'PFB10109311',
'PFB10109283',
'PFB10109284',
'PO10261877',
'PFB10108583',
'PO10261782',
'PFB10108715',
'PO10261463',
'PFB10108037',
'PFB10108096',
'PFB10108180',
'PFB10108208',
'PFB10108975',
'PFB10108207',
'PFB10108217',
'FBA10157983',
'FBA10157785',
'PFB10107197',
'PFB10107670',
'PFB10107804',
'PFB10107399',
'PFB10107431',
'PFB10107430',
'PFB10107427',
'PFB10107421',
'PFB10107448',
'FBA10156849',
'PFB10105456',
'PFB10105875',
'PFB10105849',
'PFB10105848',
'PFB10105813',
'PFB10105740',
'PFB10105632',
'PFB10103964',
'PO10258033',
'PFB10104734',
'PO10257404',
'PFB10104891',
'PFB10103992',
'PFB10104503',
'PFB10104982',
'FBA10154200',
'PO10256850',
'PFB10103366',
'PFB10103395',
'PFB10103752',
'PFB10103367',
'FBA10152373',
'PFB10102623',
'PFB10102650',
'PFB10102508',
'PFB10102530',
'PFB10102180',
'PFB10102159',
'FBA10153119',
'PFB10101075',
'PFB10101108',
'PFB10101101',
'PFB10101826',
'PFB10101034',
'FBA10151800',
'PO10255809',
'PO10255112',
'PFB10101039',
'FBA10151825',
'PFB10101778',
'PFB10101789',
'PFB10101786',
'PFB10101644',
'PFB10101647',
'PFB10100228',
'PFB10100279',
'PFB10100273',
'PFB10099627',
'PFB10100276',
'PFB10100300',
'PFB10100443',
'PFB10099944',
'PFB10100296',
'PFB10100290',
'PO10251166',
'PO10252082',
'PO10252292',
'PO10253267',
'PO10251445',
'PO10253268',
'PO10251900',
'PO10252145',
'PFB10098309',
'FBA10149462',
'PFB10098763',
'PFB10098865',
'PFB10098840',
'PFB10098863',
'PFB10098780',
'PO10250568',
'PFB10097320',
'PFB10098295',
'PFB10097354',
'PFB10097477',
'PFB10097682',
'PFB10097683',
'PFB10096910',
'PFB10096883',
'PFB10096892',
'PFB10096333',
'PFB10096207',
'PFB10096519',
'PFB10096461',
'PO10248047',
'PO10247634',
'PFB10095686',
'PFB10095408',
'PFB10095456',
'PFB10093709',
'PFB10094120',
'PFB10094373',
'PFB10092780',
'PFB10091009',
'PFB10091496',
'PFB10091495',
'PFB10090525',
'PFB10090608',
'PFB10090585',
'PFB10089490',
'PFB10089518',
'PFB10089492',
'PFB10088153',
'PFB10085801',
'PFB10087104',
'PFB10084516',
'PFB10076747',
'PFB10053366',
'PFB10049784',
'ABD9652837',
'ABD9650774',
'ABD9644519',
'ABD9644518',
    
];

$url = 'http://pms.yibainetwork.com:81/charge_against_api/init_purchase_order_pay_type_price?purchase_number=';

foreach($arr as $value){
	
	echo $url.$value;
	echo '<br/><br/>';
	file_get_contents($url.$value);
	echo 'OK';
	echo '<br/><br/>';
}


echo 'sss';exit;







