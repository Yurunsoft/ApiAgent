<?php
namespace Yurun\ApiAgent;

use Yurun\Until\HttpRequest;

class ApiAgent
{
	const VERSION = '0.0.1';
	public static $config;
	public static function run($mode)
	{
		header('X-Powered-By:ApiAgent ' . self::VERSION, false);
		defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
		self::$config = include ROOT_PATH . 'Config/config.php';
		if(isset(self::$config['temp_dir'][0]))
		{
			HttpRequest::$tempDir = self::$config['temp_dir'];
		}
		if(self::$config['http_custom_location'])
		{
			HttpRequest::$customLocation = true;
		}
		Plugin::init(self::$config['plugins']);
		$className = 'Yurun\\ApiAgent\\Mode\\' . $mode;
		$obj = new $className;
		$obj->run();
	}
}