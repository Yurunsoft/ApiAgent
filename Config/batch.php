<?php
return array(
	// 是否过滤当前相同域名的url请求访问，为true时，filter_own无效
	'filter_own_domain'	=>	false,
	// 是否过滤通过接口的index.php的请求访问
	'filter_own'		=>	true,
	// 预定义的api接口
	'apis'	=>	array(
		/*
		'接口别名'	=>	array(
			'url'		=>	'接口地址',
			'method'	=>	'get', // 请求方式
			'condition'	=>	array(
				'value'		=>	'{$之前的接口别名}.xxx', // 
				'regular'	=>	'' // 正则
			)
		),
		*/
	),
	// 允许所有跨域访问，为true时AllowOrigins无效
	'allow_all_origin'	=>	true,
	// 允许跨域访问的域名，支持所有域名的子域名，不要带http://
	'allow_origins'	=>	array(
	),
	// 接口返回的cookie有效期，单位：秒
	'cookie_expire'	=>	86400,
	// 过滤请求header
	'request_header_filter'	=>	array(
		'Connection',
		'Transfer-Encoding',
		'Content-Length',
		'Keep-Alive',
		'Host',
	),
);