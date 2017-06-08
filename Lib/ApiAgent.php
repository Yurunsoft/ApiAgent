<?php
namespace Yurun\ApiAgent;

class ApiAgent
{
	public static $config;
	public static function run($mode)
	{
		defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
		self::$config = include ROOT_PATH . 'Config/config.php';
		Plugin::init(self::$config['plugins']);
		$className = 'Yurun\\ApiAgent\\Mode\\' . $mode;
		$obj = new $className;
		$obj->run();
	}
}