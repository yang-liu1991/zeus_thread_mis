<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-08-02 16:20:42
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>

<!-- 以下为列表搜索框 -->
<div class="spend-list-search">
    <?php $form = ActiveForm::begin([
        'action' => ['binding-list'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
		]
    ]); ?>

    <?= $form->field($searchModel, 'account_id')->textInput(['placeholder' => '按account id搜索', 'style' => 'width:300px;']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

