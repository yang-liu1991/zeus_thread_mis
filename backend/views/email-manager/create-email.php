<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-02-21 17:52:09
 */
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\assets\AppAsset;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
AppAsset::register($this);

$this->title = '增加邮件模板';
$this->params['breadcrumbs'][] = ['label' => '邮件发送管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="create-email">
	<legend><?= Html::encode($this->title) ?></legend>
	<?= $this->render('_form', ['model' => $model]); ?>
	
	<div class="form-group" style="margin-left:80px;">
		<?= Html::Button('Create', ['class'=>'btn btn-primary','name' =>'create-button', 'id' => 'email-create-button']) ?>	
		<?= Html::Button('Preview', ['class'=>'btn btn-primary','name' =>'preview-button', 'id' => 'email-preview-button']) ?>	
	</div>
</div>
