<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <?php $form = ActiveForm::begin([
	'layout' => 'horizontal',
	'fieldConfig' => [  
	    'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-5\">{error}</div>\n<div style=\"margin-left:200px;\" class=\"col-lg-5\">{hint}</div>",  
	    'labelOptions' => ['class' => 'col-lg-2 control-label'],  
	], 
    ]); ?>
    <p style="padding-left:4%; color:#999;">Please fill out the following fields:</p>
    <br/>
    <?php if($model->isNewRecord): ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?php else: ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'readonly' => 'readonly']) ?>
    <?php endif; ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'permissions')->dropDownList($model->allPermissions, ['style' => 'width:370px;', 'multiple' => true, 'size' => 8])->hint('Press and hold ctrl or command to choose.', ['class' => 'help-block']) ?>

    <?= $form->field($model, 'type')->hiddenInput()->label('') ?>

    <div class="form-group" style="padding:15px 0 0 18.6%; border-top: 1px solid #e5e5e5;">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
