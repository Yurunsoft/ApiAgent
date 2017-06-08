<?php
namespace Yurun\ApiAgent\Mode;

use Yurun\Until\Event;
use Yurun\Until\HttpRequest;

class Cross extends Base
{
	public function run()
	{
		header('Access-Control-Allow-Origin:*');
		$url = $this->getUrl();
		$method = $this->getMethod();
		$headers = $this->getHeaders();
		$requestBody = $this->getRequestBody();
		$http = HttpRequest::newSession();
		$response = $http->headers($headers)
						 ->$method($url, $requestBody);
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
		Event::trigger('CROSS_ECHO_CONTENT_BEFORE', array('handler'=>$this,'response'=>$response));
		echo $response->body;
	}
	
	public function getUrl()
	{
		$url = isset($_GET[$this->config['param_url']]) ? $_GET[$this->config['param_url']] : null;
		Event::trigger('CROSS_GET_URL', array('handler'=>$this,'url'=>&$url));
		return $url;
	}

	public function getHeaders()
	{
		$headers = getallheaders();
		Event::trigger('CROSS_GET_HEADERS', array('handler'=>$this,'headers'=>&$headers));
		foreach($this->config['response_headers_filter'] as $filter)
		{
			unset($headers[$filter]);
		}
		return $headers;
	}

	public function getRequestBody()
	{
		$requestBody = file_get_contents('php://input');
		Event::trigger('CROSS_GET_REQUEST_BODY', array('handler'=>$this,'requestBody'=>&$requestBody));
		return $requestBody;
	}

	public function getMethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		Event::trigger('CROSS_GET_METHOD', array('handler'=>$this,'method'=>&$method));
		return $method;
	}
}