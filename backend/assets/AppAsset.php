<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
	/* 加载全局样式 */
    public $css = [
		'css/main.css',
        'css/site.css',
		'css/pager.css'
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
	public $jsOptions = [
		'position' => \yii\web\View::POS_HEAD,
	];
}
