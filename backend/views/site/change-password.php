<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-14 17:34:00
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


$this->registerJsFile('@web/js/plugin/jquery.md5.js?v=' . D3_VERSION);
$this->registerJsFile('@web/js/site/password.js');


/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\ChangePassword */
$this->title = Yii::t('rbac-admin', '更新密码');
$this->params['breadcrumbs'][] = $this->title;
?>

<legend><?= Html::encode($this->title) ?></legend>

<div class="site-signup">

    <p>Please fill out the following fields to change password:</p>

	<?php $form = ActiveForm::begin([
		'id' => 'form-change',
		'options' => ['class' => 'form-horizontal'],
		'fieldConfig' => [
			'template' => "<div class='col-xs-2 col-sm-3 text-right'>{label}</div><div class='col-xs-9 col-sm-7'>{input}{error}</div>",
			'labelOptions' => ['class' => 'col-lg-l control-label'],
		]
	]); ?>
    
	<?= $form->field($model, 'oldPassword')->passwordInput(['style' => 'width:300px;']) ?>
    <?= $form->field($model, 'newPassword')->passwordInput(['style' => 'width:300px;']) ?>
    <?= $form->field($model, 'retypePassword')->passwordInput(['style' => 'width:300px;']) ?>
    
	<div class="form-group">
        <?= Html::button(Yii::t('rbac-admin', '更新'), ['class' => 'btn btn-primary', 'name' => 'change-button', 'id' => 'change-password']) ?>
    </div>
    
	<?php ActiveForm::end(); ?>
</div>

