<?php
namespace Yurun\ApiAgent\Mode;

abstract class Base
{
	public $config;
	public function __construct()
	{
		$className = get_called_class();
		if ($pos = strrpos($className, '\\'))
		{
			$className = substr($className, $pos + 1);
		}
		else
		{
			$className =  $pos;
		}
		$this->config = include ROOT_PATH . 'Config/' . strtolower($className) . '.php';
	}
	public abstract function run();
}