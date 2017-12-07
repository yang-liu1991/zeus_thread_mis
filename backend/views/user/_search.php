<?php

use yii\helpers\Html;
use backend\models\user\User;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\user\UserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
	'options' => ['class' => 'form-inline well'],
	'fieldConfig' => [
	    'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
	]
    ]); ?>


    <?= $form->field($model, 'status')->dropDownList([User::STATUS_ACTIVE => 'Active', User::STATUS_DELETED => 'Deleted'], ['prompt' => '-- Status --', 'style' => 'width:150px;']) ?>
    <?= $form->field($model, 'email')->textInput(['placeholder' => 'Username']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
