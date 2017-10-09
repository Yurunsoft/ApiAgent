<?php
namespace Yurun\ApiAgent;

/**
 * 插件接口
 */
interface IPlugin
{
	/**
	 * 初始化插件
	 * @return void
	 */
	public function init();
}