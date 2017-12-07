<?php

use yii\helpers\Html;
use common\models\AuthItem;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\auths\AuthItemSearcher */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="auth-item-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
	'options' => ['class' => 'form-inline well'],
	'fieldConfig' => [
	    'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
	]
    ]); ?>


    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Name']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
