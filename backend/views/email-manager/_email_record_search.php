<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-02-23 17:26:01
 */
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\user\User;
use kartik\datetime\DateTimePicker;
use backend\models\record\ThEmailRecord;
?>

<!-- 以下为列表搜索框 -->
<div class="email-record-search">

	<?php
		Pjax::begin(['id' => 'email-record-search-pjax']);

		$form = ActiveForm::begin([
		'id' => 'email-record-search-form',
        'action' => ['email-record'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n<div style='width:250px;'>{input}</div>\n{hint}\n{endWrapper}",
		]
	]); ?>
	
	<?= $form->field($model, 'begin_time')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择起始时间'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,   
			'minView' => 2,
			'format' => 'yyyy-mm-dd'
		]   
	]); ?>  

	<?= $form->field($model, 'end_time')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择结束时间'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,   
			'minView' => 2,
			'format' => 'yyyy-mm-dd'	
		]   
	]); ?> 

	<?= $form->field($model, 'status')->dropDownList(array_flip(ThEmailRecord::getSendStatus()), [
		'prompt'=>'请选择状态', 
		'style' => 'width:250px;'])->label('状态筛选') ?>
	<?= Html::Button('Email List', ['class'=>'btn btn-success','name' =>'list-button', 'id' => 'email-list-button']) ?>	
	<?php ActiveForm::end(); ?>
	<?php Pjax::end(); ?>
</div>
