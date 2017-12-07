<?php

use yii\helpers\Html;
use backend\models\user\User;
use yii\bootstrap\ActiveForm;
use backend\models\user\AddUserForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
$this->registerJsFile('@web/js/plugin/jquery.md5.js?v=' . D3_VERSION);
$this->registerJsFile('@web/js/site/signup.js');
?>

<div class="row">
    <?php $form = ActiveForm::begin([
	'id' => 'user_form',
	'layout' => 'horizontal',
	'fieldConfig' => [  
	    'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-5\">{error}</div>\n<div style=\"margin-left:200px;\" class=\"col-lg-5\">{hint}</div>",  
	    'labelOptions' => ['class' => 'col-lg-2 control-label'],  
	], 
    ]); ?>
	<p style="padding-left:4%; color:#999;">Please fill out the following fields:</p>
	<br/>
	<?php if($model->scenario == 'add'):?>
		<?= $form->field($model, 'email')->textInput() ?>
		<?= $form->field($model, 'password')->passwordInput() ?>
		<?= $form->field($model, 'repassword')->passwordInput() ?>
		<?= $form->field($model, 'company_id')->dropDownList(AddUserForm::getCompanyName(), ['prompt' => '请选择代理公司']) ?>

	<?php elseif($model->scenario == 'mod'):?>

		<?= $form->field($model, 'email')->textInput(['readonly'=>'readonly']) ?>
		<?= $form->field($model, 'password')->passwordInput()->hint('If you don\'t need to modify the password, please do not fill in.', ['class' => 'help-block']) ?>
		<?= $form->field($model, 'repassword')->passwordInput()->hint('If you don\'t need to modify the password, please do not fill in.', ['class' => 'help-block']) ?>
	<?php endif;?>

	<?= $form->field($model, 'rbacRole')->dropDownList($allRbacRole, ['style' => 'width:370px;', 'multiple' => true, 'size' => 8])->hint('Press and hold ctrl or command to choose.', ['class' => 'help-block']) ?>
	<?= $form->field($model, 'status')->dropDownList([User::STATUS_ACTIVE => 'Active', User::STATUS_DELETED => 'Deleted'], ['style' => 'width:150px;']) ?>

	<div class="form-group" style="padding:15px 0 0 18.6%; border-top: 1px solid #e5e5e5;">
	<?php if($model->scenario == 'add'): ?>
        <?= Html::button(Yii::t('rbac-admin', '提交'), ['class' => 'btn btn-primary', 'name' => 'button-create-user', 'id' => 'submit-create-user']) ?>
	<?php elseif($model->scenario == 'mod'):?>
        <?= Html::button(Yii::t('rbac-admin', '更新'), ['class' => 'btn btn-primary', 'name' => 'button-update-user', 'id' => 'submit-update-user']) ?>
	<?php endif;?>
	</div>
    <?php ActiveForm::end(); ?>
</div>
