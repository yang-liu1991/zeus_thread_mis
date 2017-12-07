<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-08-02 16:20:42
 */
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\datetime\DateTimePicker;
use backend\models\record\ThEntityInfo;
?>

<!-- 以下为列表搜索框 -->
<div class="company-amount-search">

	<?php
		$form = ActiveForm::begin([
		'id' => 'company-amount-search-form',
        'action' => ['company-amount-list'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n<div style='width:250px;'>{input}</div>\n{hint}\n{endWrapper}",
		]
	]); ?>
	
	<?= $form->field($searchModel, 'name_zh')->hiddenInput(); ?>
	<?= $form->field($spendReportModel, 'date_start')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择起始时间', 'style' => 'width:150px'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,
			'minView' => 2,
			'format' => 'yyyy-mm-dd'
		]   
	]); ?>  
	<?= $form->field($spendReportModel, 'date_stop')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择结束时间', 'style' => 'width:150px'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,   
			'minView' => 2,
			'format' => 'yyyy-mm-dd'
		]   
	]); ?> 	

	<?= $form->field($searchModel, 'search')->widget(Select2::classname(), [
		'options' => ['placeholder' => '按广告主名称搜索'],
		'pluginOptions' => [
			'allowClear' => true,
			'tags' => true,
			'minimumInputLength' => 1,
			'language' => [
				'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
			],
			'ajax' => [
				'url' => Url::to(['get-company-name']),
				'dataType' => 'json',
				'data'	=> new JsExpression('function(params) { return {name_zh: params.term}}')
			],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(city) { return city.text; }'),
			'templateSelection' => new JsExpression('function (city) { return city.text; }'),
			], 
		]);	 ?>

		<?= Html::Button('Export', ['class' => 'btn btn-primary', 'id' => 'amount-export-button']); ?>
	<?php ActiveForm::end(); ?>
</div>

