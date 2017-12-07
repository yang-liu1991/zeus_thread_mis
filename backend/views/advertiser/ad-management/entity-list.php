<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-07-18 17:07:01
 */


use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;
use yii\bootstrap\ActiveForm;
use common\models\Conversion;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerCssFile('@web/js/jq-zoom/jquery.zoom.css');
$this->registerJsFile('@web/js/jq-zoom/jquery.zoom.min.js');
$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/audit.js');

$this->title = '公司主体列表';
$this->params['breadcrumbs'][] = $this->title;

?>

<legend><?= Html::encode($this->title) ?></legend>

<!-- 以下为列表搜索框 -->
<div class="entity-search">
    <?php $form = ActiveForm::begin([
        'action' => ['entity-list', 'audit_status' => $searchModel->audit_status],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
		]
    ]); ?>

    <?= $form->field($searchModel, 'name_zh')->textInput(['placeholder' => '按中文名称搜索', 'style' => 'width:300px;']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<div class="user-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'pager'=>[
			'firstPageLabel'=>"First",
			'prevPageLabel'=>'Prev',
			'nextPageLabel'=>'Next',
			'lastPageLabel'=>'Last',
			'options' => ['class'=>'pager'],
		],
		'rowOptions' => ['style'=>'text-align:center;'],
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'text-align:center;font-size:13px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'entity_list_form'],
        'columns' => [
	    [
			'attribute' => 'ID',
			'value'		=> 'id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '注册公司英文名称',
			'format'	=> 'raw',
			'value'		=> function ($model, $key, $index, $column) {
				return Html::a($model->name_en, ['advertiser/entity-view', 'id' => $key]);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '注册公司中文名称',
			'value'		=> 'name_zh',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '业务类型',
			'value'		=> 'vertical',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '操作类型',
			'value'		=> function($data) {
				return Conversion::getActionType($data->created_at, $data->updated_at);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '审核状态',
			'value'		=> 'audit_status',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getAuditStatus($data->audit_status);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'template' => '{entity-update}',
			'buttons' => [
				'entity-update' => function($url, $model, $key) {
					return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 
						Yii::$app->urlManager->createUrl(['advertiser/entity-update', 'id' => $key]));
				},
			],
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;', 'width'=>100],
		],
		],
    ]); ?>
</div>
