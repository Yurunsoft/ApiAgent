<?php
namespace Yurun\ApiAgent\Mode;

use Yurun\ApiAgent\ApiAgent;
use Yurun\Until\Event;
use Yurun\Until\HttpRequest;

class Cross extends Base
{
	/**
	 * 运行
	 */
	public function run()
	{
		header('Access-Control-Allow-Origin:*');
		$url = $this->getUrl();
		$method = $this->getMethod();
		$headers = $this->getHeaders();
		$requestBody = $this->getRequestBody();
		$isReturnCookie = !isset($_GET['return_cookie']) || 1 == $_GET['return_cookie'];
		$http = HttpRequest::newSession();
		if(isset($headers['Accept-Encoding']))
		{
			$http->option(CURLOPT_ENCODING, $headers['Accept-Encoding']);
		}
		$response = $http->headers($headers)
						 ->timeout(ApiAgent::$config['http_timeout'])
						 ->$method($url, $requestBody);
		header('Status: ' . $response->httpCode());
		// 处理是否返回cookie
		if(!$isReturnCookie && isset($response->headers['Set-Cookie']))
		{
			unset($response->headers['Set-Cookie']);
		}
		foreach($response->headers as $name => $header)
		{
			if(is_array($header))
			{
				foreach($header as $item)
				{
					header("{$name}:{$item}", false);
				}
			}
			else if(!in_array(strtolower($name), $this->config['request_header_filter']))
			{
				header("{$name}:{$header}", false);
			}
		}
		Event::trigger('CROSS_ECHO_CONTENT_BEFORE', array('handler'=>$this,'response'=>&$response));
		echo $response->body;
	}
	
	/**
	 * 获取请求url地址
	 * @return string 
	 */
	private function getUrl()
	{
		$url = isset($_GET[$this->config['param_url']]) ? $_GET[$this->config['param_url']] : null;
		Event::trigger('CROSS_GET_URL', array('handler'=>$this,'url'=>&$url));
		return $url;
	}

	/**
	 * 获取请求header头
	 * @return array 
	 */
	private function getHeaders()
	{
		$headers = getallheaders();
		Event::trigger('CROSS_GET_HEADERS', array('handler'=>$this,'headers'=>&$headers));
		foreach($this->config['response_headers_filter'] as $filter)
		{
			unset($headers[$filter]);
		}
		return $headers;
	}

	/**
	 * 获取请求内容
	 * @return array 
	 */
	private function getRequestBody()
	{
		$requestBody = file_get_contents('php://input');
		Event::trigger('CROSS_GET_REQUEST_BODY', array('handler'=>$this,'requestBody'=>&$requestBody));
		return $requestBody;
	}

	/**
	 * 获取请求方式
	 * @return string 
	 */
	private function getMethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		Event::trigger('CROSS_GET_METHOD', array('handler'=>$this,'method'=>&$method));
		return $method;
	}
}