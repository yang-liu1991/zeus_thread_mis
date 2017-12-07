<?php

/* 主要定义了wendor路径 */
require(__DIR__ . '/../../common/config/common.php');

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');
defined('D3_VERSION') or define('D3_VERSION', '0.0.1');

require(VENDOR . '/autoload.php');
require(VENDOR . '/yiisoft/yii2/Yii.php');
require(VENDOR . '/qiniu/php-sdk/autoload.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');

if(file_exists(__DIR__ . '/../../common/config/main-local.php'))
{
	$config = yii\helpers\ArrayHelper::merge(
		require(__DIR__ . '/../../common/config/main.php'),
		require(__DIR__ . '/../../common/config/main-local.php'),
		require(__DIR__ . '/../config/main.php'),
		require(__DIR__ . '/../config/main-local.php')
	);
} else {
	$config = yii\helpers\ArrayHelper::merge(
		require(__DIR__ . '/../../common/config/main.php'),
		require(__DIR__ . '/../config/main.php')
	);
}

$application = new yii\web\Application($config);
$application->run();
