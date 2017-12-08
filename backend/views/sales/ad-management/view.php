<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-07-27 13:37:48
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */

$this->title = '公司主体信息详情';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ad-account-info-view">

    <legend><?= Html::encode($this->title) ?></legend>
	<?= $this->render('_view', [
		'model' => $model]) 
	?>
</div>
