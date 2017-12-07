<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use backend\models\user\User;
use backend\models\record\ThEmailRecord;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerCssFile('@web/css/summernote/summernote.css');
$this->registerJsFile('@web/js/summernote/summernote.js');
$this->registerJsFile('@web/js/request/email-manager.js');

$this->title = '邮件发送记录';
$this->params['breadcrumbs'][] = ['label' => '邮件发送管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-record">

	<legend><?= Html::encode($this->title) ?></legend>

	<input type="hidden" id="mail-record-id" value="<?= Yii::$app->request->get('id'); ?>">
	<?= $this->render('_email_record_search', ['model' => $model, 'id' => Yii::$app->request->get('id')]); ?>
	<?php Pjax::begin(['id' => 'email-record']) ?>
	<?= GridView::widget([
		'id' => 'email-record-form',
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
		'pager'=>[
			'firstPageLabel'=>"First",
			'prevPageLabel'=>'Prev',
			'nextPageLabel'=>'Next',
			'lastPageLabel'=>'Last',
			'options' => ['class'=>'pager'],
		],
		'rowOptions' => ['style'=>'text-align:center'],
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'table-layout:fixed;word-break:break-all;text-align:center;font-size:13px;font-family: "Microsoft YaHei" ! important; '],
        'columns' => [
	    [
			'attribute' => 'id',
			'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
			'attribute' => '接收者',
			'value'		=> 'receiver',
			'headerOptions' => ['style'=>'text-align:center']
	    ],
		[
			'attribute' => '主题',
			'value'		=> 'emailTemplate.subject',
			'headerOptions' => ['style'=>'text-align:center']
	    ],
	    [
			'attribute' => '创建时间',
			'headerOptions' => ['style'=>'text-align:center'],
			'content' => function($model){
				return date('Y-m-d H:i', $model->created_at);
			}
	    ],
	    [
			'attribute' => '更新时间',
			'headerOptions' => ['style'=>'text-align:center'],
			'content' => function($model){
				return date('Y-m-d H:i', $model->updated_at);
			}
	    ],
		[
			'attribute' => '状态',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return ThEmailRecord::getRecordStatus($data->status);
			},
			'headerOptions' => ['style'=>'text-align:center'],
	    ],
		[
			'attribute' => '失败原因',
			'format'	=> 'raw',
			'value'		=> 'reason',
			'headerOptions' => ['style'=>'width:200px;text-align:center'],
	    ]
		],
    ]); ?>
	<?php Pjax::end(); ?>
</div>
