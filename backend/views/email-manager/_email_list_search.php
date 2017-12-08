<?php
/**
 * Author: young_liu@vip.sina.com
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
use backend\models\record\ThEmailTemplate;
?>

<!-- 以下为列表搜索框 -->
<div class="email-list-search">

	<?php
		Pjax::begin(['id' => 'email-list-search-pjax']);

		$form = ActiveForm::begin([
		'id' => 'email-list-search-form',
        'action' => ['email-list'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n<div style='width:250px;'>{input}</div>\n{hint}\n{endWrapper}",
		]
	]); ?>
	<?= $form->field($model, 'subject')->hiddenInput(); ?>	
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
	
	<?= $form->field($model, 'search')->widget(Select2::classname(), [
		'options' => ['placeholder' => '按邮件主题搜索'],
		'pluginOptions' => [
			'allowClear' => true,
			'tags' => true,
			'minimumInputLength' => 1,
			'language' => [
				'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
			],
			'ajax' => [
				'url' => Url::to(['get-email-subject']),
				'dataType' => 'json',
				'data'	=> new JsExpression('function(params) { return {subject: params.term}}')
			],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(city) { return city.text; }'),
			'templateSelection' => new JsExpression('function (city) { return city.text; }'),
			], 
		]);	 ?>
	<?php ActiveForm::end(); ?>
	<?php Pjax::end(); ?>
</div>
