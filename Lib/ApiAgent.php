<?php
namespace Yurun\ApiAgent;

use Yurun\Until\HttpRequest;

/**
 * api接口代理调度类
 */
class ApiAgent
{
	/**
	 * 版本号
	 * @var string
	 */
	const VERSION = '0.0.1';

	/**
	 * 总配置
	 * @var array
	 */
	public static $config;

	/**
	 * 执行接口代理
	 * @param string $mode 
	 */
	public static function run($mode)
	{
		header('X-Powered-By:ApiAgent ' . self::VERSION, false);
		// 项目根目录
		defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
		// 加载总配置
		self::$config = include ROOT_PATH . 'Config/config.php';
		// yurunhttp的临时目录设置
		if(isset(self::$config['temp_dir'][0]))
		{
			HttpRequest::$tempDir = self::$config['temp_dir'];
		}
		// yurunhttp的是否开启自定义重定向功能
		if(self::$config['http_custom_location'])
		{
			HttpRequest::$customLocation = true;
		}
		// 插件初始化
		Plugin::init(self::$config['plugins']);
		// 根据模式执行相应的类
		$className = 'Yurun\\ApiAgent\\Mode\\' . $mode;
		$obj = new $className;
		$obj->run();
	}
}