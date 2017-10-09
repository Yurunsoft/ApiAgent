<?php
namespace Yurun\ApiAgent;

class Plugin
{
	/**
	 * 是否初始化过
	 * @var boolean
	 */
	public static $isInited = false;

	/**
	 * 初始化插件列表
	 * @param array $plugins
	 * @return void
	 */
	public static function init($plugins)
	{
		if(!self::$isInited)
		{
			foreach($plugins as $plugin)
			{
				$className = 'Yurun\\ApiAgent\\Plugin\\' . $plugin;
				$obj = new $className;
				$obj->init();
			}
			self::$isInited = true;
		}
	}
}