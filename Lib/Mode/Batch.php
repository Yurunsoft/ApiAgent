<?php
namespace Yurun\ApiAgent\Mode;

use Yurun\ApiAgent\ApiAgent;
use Yurun\Until\Event;
use Yurun\Until\HttpRequest;

class Batch extends Base
{
	/**
	 * 请求数据，规则数组
	 * @var array
	 */
	public $options;

	/**
	 * 返回结果数组
	 * @var array
	 */
	public $result;

	/**
	 * 保存接口返回数据的数组
	 * @var array
	 */
	public $dataResult;

	/**
	 * 运行
	 */
	public function run()
	{
		$this->checkOrigin();
		$this->options = json_decode(file_get_contents('php://input'), true);
		if(!is_array($this->options))
		{
			exit(json_encode(array(
				'success'	=>	false,
				'message'	=>	'参数不正确',
			)));
		}
		$this->result = array(
			'success'	=>	true,
			'data'		=>	array(),
			'error'		=>	array(),
			'header'	=>	array(),
		);
		$this->dataResult = array();
		foreach($this->options as $name => $option)
		{
			if(!isset($option['url']))
			{
				$this->result['success'] = false;
				$this->result['break'] = $name;
				$this->result['data'][$name] = "{$name}.url 不存在";
				break;
			}
			if(isset($this->config['apis'][$option['url']]))
			{
				$option = array_merge($this->config['apis'][$option['url']], $option);
				$option['url'] = $this->config['apis'][$option['url']]['url'];
			}
			if(!$this->parseOptionItem($name, $option))
			{
				$this->result['success'] = false;
				$this->result['break'] = $name;
				break;
			}
		}
		echo json_encode($this->result);
	}

	/**
	 * 检查处理跨域
	 */
	private function checkOrigin()
	{
		header('Access-Control-Allow-Credentials: true');
		if($this->config['allow_all_origin'])
		{
			if(isset($_SERVER['HTTP_ORIGIN']))
			{
				header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN']);
			}
		}
		else if(isset($_SERVER['HTTP_ORIGIN']))
		{
			foreach($this->config['allow_origins'] as $domain)
			{
				if($_SERVER['HTTP_ORIGIN'] === 'http://' . $domain || $_SERVER['HTTP_ORIGIN'] === 'https://' . $domain || substr($_SERVER['HTTP_ORIGIN'], -strlen($domain) - 1) === '.' . $domain)
				{
					header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
					break;
				}
			}
		}
		Event::trigger('BATCH_CHECK_ORIGIN', array('handler'=>$this));
	}

	/**
	 * 处理接口项
	 * @param string $name 
	 * @param array $option 
	 * @return bool 
	 */
	private function parseOptionItem($name, $option)
	{
		$method = isset($option['method']) ? strtolower($option['method']) : 'get';
		$dataType = isset($option['dataType']) ? $option['dataType'] : 'form';
		$url = $this->buildUrl($this->parseRule($option['url']), isset($option['getData']) ? $this->parseData('', $option['getData']) : array());
		$postData = isset($option['postData']) ? $this->parseData($dataType, $option['postData']) : array();
		Event::trigger('BATCH_BEFORE_SEND', array('handler'=>$this, 'method'=>$method, 'dataType'=>$dataType, 'url'=>$url, 'postData'=>$postData));
		$http = HttpRequest::newSession();
		if(empty($option['header']))
		{
			$headers = getallheaders();
			foreach($this->config['request_header_filter'] as $filter)
			{
				unset($headers[$filter]);
			}
		}
		else
		{
			$headers = $option['header'];
		}
		if(isset($headers['Accept-Encoding']))
		{
			$http->option(CURLOPT_ENCODING, $headers['Accept-Encoding']);
		}
		$result = $http->headers($headers)
					   ->timeout(ApiAgent::$config['http_timeout'])
					   ->$method($url, $postData);
		$this->dataResult[] = $result->body;
		$data = json_decode($result->body, true);
		if(is_array($data))
		{
			$this->result['data'][$name] = $data;
		}
		else
		{
			$this->result['data'][$name] = $result->body;
		}
		if($result->success)
		{
			if(!empty($result->cookies))
			{
				// cookie原样返回
				foreach($result->cookies as $cookieName => $item)
				{
					setcookie($cookieName, $item['value'], $_SERVER['REQUEST_TIME'] + $this->config['cookie_expire'], '/');
				}
			}
			$this->result['header'][$name] = $result->headers;
		}
		else
		{
			$this->result['error'] = array('key'=>$name, 'error'=>$result->error());
			return false;
		}
		return $this->checkResult($name, $option);
	}

	/**
	 * 处理接口调用结果
	 * @param string $name 
	 * @param array $option 
	 * @return bool 
	 */
	private function checkResult($name, $option)
	{
		if(!isset($option['condition']))
		{
			return true;
		}
		if(!isset($option['condition']['value']))
		{
			return true;
		}
		if(!is_array($this->result['data'][$name]))
		{
			return false;
		}
		return $this->checkByRegulars($option['condition']['value'], isset($option['condition']['regular']) ? $option['condition']['regular'] : '');
	}

	/**
	 * 根据正则或者预定义规则检查结果
	 * @param mixed $value 
	 * @param string $regular 
	 * @return bool 
	 */
	private function checkByRegulars($value, $regular)
	{
		switch($regular)
		{
			case 'is null':
				return null === $value;
			case 'is not null':
				return null !== $value;
			case 'empty array':
				return 0 === count($value[0]);
			case 'not empty array':
				return count($value[0]) > 0;
			case 'true':
				return true === $value;
			case 'false':
				return false === $value;
		}
		return is_scalar($value) && preg_match('/' . $regular . '/', $value) > 0;
	}

	/**
	 * 处理GET/POST数据
	 * @param string $dataType 
	 * @param array $data 
	 * @return array 
	 */
	private function parseData($dataType, $data)
	{
		foreach($data as $index => $item)
		{
			$data[$index] = $this->parseRule($item);
		}
		switch($dataType)
		{
			case 'form':
				return http_build_query($data);
			case 'json':
				return json_encode($data);
		}
		return $data;
	}

	/**
	 * 处理GET/POST数据里的变量（之前请求数据代入）
	 * @param string $rule 
	 * @return string 
	 */
	private function parseRule($rule)
	{
		$rules = $this->getRules($rule);
		foreach($rules[1] as $index => $item)
		{
			$rule = str_replace($rules[0][$index], $this->getValueByRule($item, $exists), $rule);
		}
		return $rule;
	}

	/**
	 * 获取GET/POST数据里的变量规则
	 * @param string $rule 
	 * @return array 
	 */
	private function getRules($rule)
	{
		preg_match_all('/{([^}]+)}/', $rule, $matches);
		return $matches;
	}

	/**
	 * 根据变量规则获取值
	 * @param string $rule 
	 * @param bool $exists 
	 * @return mixed 
	 */
	private function getValueByRule($rule, &$exists = true)
	{
		$list = explode('.', preg_replace('/\[([^\]]+)\]/', '.\1', $rule));
		if(isset($list[0][0]) && '$' === $list[0][0])
		{
			$name = substr($list[0], 1);
			unset($list[0]);
			if(isset($this->result['data'][$name]))
			{
				$data = $this->result['data'][$name];
			}
			else
			{
				return null;
			}
			foreach($list as $item)
			{
				if(!isset($data[$item]))
				{
					$exists = false;
					return;
				}
				$data = &$data[$item];
			}
			return $data;
		}
		else
		{
			return $rule;
		}
	}

	/**
	 * 构造url地址
	 * @param string $url 
	 * @param array $params 
	 * @return string 
	 */
	public function buildUrl($url, $params = array())
	{
		if(empty($params))
		{
			return $url;
		}
		if(false === strpos($url, '?'))
		{
			$url .= '?';
		}
		else
		{
			$url .= '&';
		}
		return $url . http_build_query($params);
	}
}