<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-07-27 13:37:48
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
$this->registerCssFile('@web/js/jq-zoom/jquery.zoom.css');
$this->registerJsFile('@web/js/jq-zoom/jquery.zoom.min.js');
$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/audit.js');


$this->title = '公司主体信息详情';
$this->params['breadcrumbs'][] = ['label' => '主体审核列表', 'url' => ['entity-list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ad-account-info-view">

    <legend><?= Html::encode($this->title) ?></legend>
	<input type="hidden" id="entity_id" value="<?= $model->id; ?>">
	<?= $this->render('_view', [
		'model' => $model]) 
	?>
</div>
