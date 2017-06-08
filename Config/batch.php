<?php
return array(
	// 是否过滤当前相同域名的url请求访问，为true时，filter_own无效
	'filter_own_domain'	=>	false,
	// 是否过滤通过接口的index.php的请求访问
	'filter_own'		=>	true,
	// 预定义的api接口
	'apis'	=>	array(
		'ip'	=>	array(
			'url'		=>	'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json',
			'method'	=>	'get',
			'condition'	=>	array(
				'value'		=>	'{$data}',
				'regular'	=>	'^(?!(?:-3)$)' // != -3
			)
		)
	),
	// 允许所有跨域访问，为true时AllowOrigins无效
	'allow_all_origin'	=>	true,
	// 允许跨域访问的域名，支持所有域名的子域名，不要带http://
	'allow_origins'	=>	array(
	)
);