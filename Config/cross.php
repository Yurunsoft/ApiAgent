<?php
return array(
	// url地址的参数名
	'param_url'	=>	'url',
	// 过滤请求header
	'request_header_filter'	=>	array(
		'Connection',
		'Transfer-Encoding',
		'Content-Length',
		'Keep-Alive',
		'Host',
	),
	// 过滤返回header
	'response_headers_filter'	=>	array(
		'Connection',
		'Host',
	),
	// 是否过滤当前相同域名的url请求访问，为true时，filter_own无效
	'filter_own_domain'	=>	false,
	// 是否过滤通过接口的index.php的请求访问
	'filter_own'		=>	true,
);