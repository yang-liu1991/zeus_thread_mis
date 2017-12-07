<?php
/**
 * for d3 redesign.
 */
namespace app\assets;
use yii\web\AssetBundle;

class D3Asset extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $js = [
		'js/plugin/jquery.cookie.js',
//		'js/site/site.js',
		'js/common/common.js',
	];
	public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
	public $cssOptions = ['position' => \yii\web\View::POS_HEAD];
	public $depends = [
		'yii\web\JqueryAsset',
	];
}
