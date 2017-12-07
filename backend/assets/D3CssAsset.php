<?php
/**
 * for d3 redesign.
 */
namespace app\assets;
use yii\web\AssetBundle;

class D3CssAsset extends AssetBundle {
	public $css = [
		//'redesign/css/reset.css',
		//'redesign/css/base-row.css',
		//'redesign/css/common.css',
	];
	public $depends = [
		'yii\web\JqueryAsset',
	];
}
