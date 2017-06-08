# ApiAgent

## 简介

API接口代理，支持跨域接口代理和接口合并请求功能。

## 跨域接口代理

通过`index.php`文件访问，默认参数为`url`，注意需要`urlencode`编码。

例子：`http://localhost:2400/?url=http://www.baidu.com`

该接口原样返回状态码、返回头（包括cookie）、返回内容。

配置文件：`Config/cross.php`

## 接口合并请求

通过`batch.php`文件访问，请求规则是POST提交json格式内容。

配置文件：`Config/batch.php`

jQuery调用样例代码：

~~~js
// 设置跨域传递cookie，如果不需要可以去除
$.ajaxSetup({
	xhrFields: {
		withCredentials: true
	},
});
$.ajax({
	// 请求地址，改成你自己的
	url: 'http://localhost:2400/batch.php',
	method: 'post',
	data: JSON.stringify({
		aip: {
			url: 'ip', // 在apis中预定义的接口，传别名即可
			getData: {ip: '218.4.255.255'},
		},
		weather: {
			url: 'http://www.weather.com.cn/data/sk/101010100.html',
			condition: {
				value: '{$weather.weatherinfo}',
				regular: 'is not null', // 验证规则，可以是预定义规则，也可以是正则
			},
		},
		weather2: {
			url: 'http://www.weather.com.cn/data/sk/1010101001.html',
			condition: { // 返回结果.weatherinfo不为null
				value: '{$weather2.weatherinfo}',
				regular: 'is not null',
			},
		},
		baike: {
			url: 'http://baike.baidu.com/api/openapi/BaikeLemmaCardApi?scope=103&format=json&appid=379020&bk_length=600',
			// get参数
			getData: {
				bk_key: '{$aip.city}', // api接口中返回的数据.city
			},
			// post参数
			postData: {},
			// 数据类型
			dataType: 'form',
			condition: {
				value: '{$baike.errno}',
				regular: 'is not null',
			},
		},
	}),
	success: function(data)	{
		console.debug(data);
	}
});
~~~

## 预定义验证规则

| 代码 | 含义 |
| - | - |
| is null | null === $value |
| is not null | null !== $value |
| empty array | 0 === count($value[0]) |
| not empty array | count($value[0]) > 0 |
| true | true === $value |
| false | false === $value |

## dataType数据类型

| 名称 | 含义 |
| - | - |
| form | 表单参数格式(如：a=1&b=2)，默认 |
| json | 转为json格式提交 |