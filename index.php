<?php
if(!class_exists('\Composer\Autoload\ClassLoader', false))
{
	require __DIR__ . '/vendor/autoload.php';
}
\Yurun\ApiAgent\ApiAgent::run('Cross');