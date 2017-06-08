<?php
namespace Yurun\ApiAgent\Plugin\Cross;

use Yurun\ApiAgent\IPlugin;
use Yurun\Until\Event;

class RequestFilter implements IPlugin
{
	public function init()
	{
		Event::on('CROSS_GET_URL', array($this, 'parseGetUrl'));
	}

	public function parseGetUrl($params)
	{
		$uri = parse_url($params['url']);
		if(!isset($uri['scheme'], $uri['host']))
		{
			exit(json_encode(array(
				'success'	=>	false,
				'message'	=>	'URL格式不正确',
			)));
		}
		if($params['handler']->config['filter_own_domain'])
		{
			// 过滤当前域名
			if($uri['host'] . (isset($uri['port']) ? (':' . $uri['port']) : '') === $_SERVER['HTTP_HOST'])
			{
				exit(json_encode(array(
					'success'	=>	false,
					'message'	=>	'禁止请求当前域名',
				)));
			}
		}
		else if($params['handler']->config['filter_own'])
		{
			// 过滤当前访问文件
			$path = $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
			$path2 = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
			$requestUrl = $uri['host'] . (isset($uri['port']) ? (':' . $uri['port']) : '') . (isset($uri['path']) ? $uri['path'] : '');
			if($path === $requestUrl || $path2 === $requestUrl)
			{
				exit(json_encode(array(
					'success'	=>	false,
					'message'	=>	'禁止请求当前地址',
				)));
			}
		}
	}
}