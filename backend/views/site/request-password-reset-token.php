<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-11-14 17:52:47
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\PasswordResetRequest */

$this->title = '重置密码';
$this->params['breadcrumbs'][] = $this->title;
?>
<legend><?= Html::encode($this->title) ?></legend>

<div class="site-request-password-reset">

    <p>Please fill out your email. A link to reset password will be sent there.</p>

    <div class="row">
        <div class="col-xs-6">
			<?php $form = ActiveForm::begin([
				'id' => 'request-password-reset-form',
				'options' => ['class' => 'form-horizontal'],
				'fieldConfig' => [
					'template' => "<div class='col-xs-3 col-sm-4 text-right'>{label}</div><div class='col-xs-9 col-sm-7'>{input}{error}</div>",
					'labelOptions' => ['class' => 'col-lg-l control-label'],
				]
			]); ?>
            
			<?= $form->field($model, 'email') ?>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('rbac-admin', '发送'), ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

