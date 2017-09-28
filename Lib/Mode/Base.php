<?php
namespace Yurun\ApiAgent\Mode;

/**
 * 模式基类
 */
abstract class Base
{
	/**
	 * 模式配置
	 * @param $modeConfig 模式配置，为空则读取配置文件
	 * @var array
	 */
	public $config;

	public function __construct($modeConfig = null)
	{
		// 开启智能压缩
		ob_start('ob_gzhandler');
		$className = get_called_class();
		if ($pos = strrpos($className, '\\'))
		{
			$className = substr($className, $pos + 1);
		}
		else
		{
			$className =  $pos;
		}
		// 加载模式配置
		if(null === $modeConfig)
		{
			$this->config = include API_AGENT_ROOT_PATH . 'Config/' . strtolower($className) . '.php';
		}
		else
		{
			$this->config = $modeConfig;
		}
	}

	/**
	 * 运行
	 */
	public abstract function run();
}