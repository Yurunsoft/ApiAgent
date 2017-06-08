<?php
namespace Yurun\ApiAgent;

class Plugin
{
	public static $isInited = false;

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