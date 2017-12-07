<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\models\record\ThAgencyBusinessSearch;

/* @var $this yii\web\View */
/* @var $model backend\models\record\ThAgencyBusiness */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="th-agency-business-form">
    <?php $form = ActiveForm::begin([
        'id' => 'agency_business_form',
        'options' => ['class' => 'form-horizontal'],
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "<div class='col-xs-3 col-sm-2 text-right'>{label}</div><div class='col-xs-9 col-sm-7'>{input}{error}</div>",
            'labelOptions' => ['class' => 'col-lg-l control-label'],
        ],
    ]); ?>

    <?php if($model->scenario == 'create'):?>
        <p style="padding-left:4%; color:#999;">Please fill out the following fields:</p><br/>
        <?= $form->field($model, 'business_id')->textInput(['style' => 'width:400px;', 'placeholder' => '请输入Business Id']) ?>
        <?= $form->field($model, 'business_name')->textInput(['style' => 'width:400px;', 'placeholder' => '请输入Business Name']) ?>
        <?= $form->field($model, 'referral')->textInput(['style' => 'width:400px;', 'placeholder' => '请输入推荐人邮箱地址']) ?>
        <?= $form->field($model, 'access_token')->textArea(['style' => 'width:400px;', 'rows' => 4, 'placeholder' => '请输入Agency授权AccessToken']) ?>
    <?php elseif($model->scenario == 'update'): ?>
        <?= $form->field($model, 'business_id')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'business_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'referral')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'access_token')->textArea(['rows' => 4]) ?>
    <?php endif; ?>

    <div class="form-group" style="padding:15px 0 0 18.6%;border-top: 1px solid #e5e5e5;">
        <?= Html::submitButton('保存', ['class'=>'btn btn-primary','name' =>'submit-button', 'id' => 'submit-button']) ?>
        <?= Html::resetButton('重置', ['class'=>'btn','name' =>'reset-button', 'id' => 'reset-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
