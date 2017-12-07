<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\models\record\ThAgencyBusinessSearch;

/* @var $this yii\web\View */
/* @var $model backend\models\record\ThAgencyBusinessSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="th-agency-business-search">
    <?php $form = ActiveForm::begin([
        'id' => 'agency-business-search',
        'action' => ['list'],
        'method' => 'get',
        'options' => ['class' => 'form-inline well', 'style' => 'align-text:right;'],
        'fieldConfig' => [
            'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
        ]
    ]); ?>

    <?= $form->field($model, 'company_id')->dropDownList(ThAgencyBusinessSearch::getCompanyName(), ['prompt' => '请选择Agency Name', 'style' => 'width:300px;']) ?>

    <?= $form->field($model, 'business_id')->textInput(['placeholder' => 'Business Id']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
