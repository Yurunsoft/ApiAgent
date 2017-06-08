<?php
namespace Yurun\ApiAgent\Mode;

use Yurun\Until\Event;
use Yurun\Until\HttpRequest;

class Batch extends Base
{
	public $options, $apis = array(), $result, $dataResult;

	/**
	 * 发送请求
	 * @return array 
	 */
	public function run()
	{
		$this->checkOrigin();
		$this->options = json_decode(file_get_contents('php://input'), true);
		$this->result = array(
			'success'	=>	true,
			'data'		=>	array(
			),
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
			if(isset($this->apis[$option['url']]))
			{
				$option = array_merge($this->apis[$option['url']], $option);
				$option['url'] = $this->apis[$option['url']]['url'];
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

	private function checkOrigin()
	{
		if($this->config['allow_all_origin'])
		{
			header('Access-Control-Allow-Origin:*');
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

	private function parseOptionItem($name, $option)
	{
		$method = isset($option['method']) ? strtolower($option['method']) : 'get';
		$dataType = isset($option['dataType']) ? $option['dataType'] : 'form';
		$url = $this->buildUrl($this->parseRule($option['url']), isset($option['getData']) ? $this->parseData($option['getData']) : array());
		$postData = isset($option['postData']) ? $this->parseData($dataType, $option['postData']) : array();
		Event::trigger('BATCH_BEFORE_SEND', array('handler'=>$this, 'method'=>$method, 'dataType'=>$dataType, 'url'=>$url, 'postData'=>$postData));
		$http = HttpRequest::newSession();
		$result = $http->$method($url, $postData);
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
		return $this->checkResult($name, $option);
	}

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

	private function parseRule($rule)
	{
		$rules = $this->getRules($rule);
		foreach($rules[1] as $index => $item)
		{
			$rule = str_replace($rules[0][$index], $this->getValueByRule($item, $exists), $rule);
		}
		return $rule;
	}

	private function getRules($rule)
	{
		preg_match_all('/{([^}]+)}/', $rule, $matches);
		return $matches;
	}

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