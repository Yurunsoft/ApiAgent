<?php
return array(
	// 插件列表
	'plugins'			=>	array(
		'Cross\\RequestFilter',
		'Batch\\RequestFilter',
	),
	// 临时目录地址，为空则取系统默认
	'temp_dir'	=>	'',
	// 使用自定义实现的重定向，性能较差。如果不是环境不支持自动重定向，请勿设为true
	'http_custom_location'	=>	false,
);