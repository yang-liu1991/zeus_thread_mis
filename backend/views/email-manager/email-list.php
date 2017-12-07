<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\web\JsExpression;
use yii\bootstrap\ActiveForm;
use backend\models\user\User;
use backend\models\record\ThEmailTemplate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerCssFile('@web/css/summernote/summernote.css');
$this->registerJsFile('@web/js/summernote/summernote.js');
$this->registerJsFile('@web/js/request/email-manager.js');


$this->title = '邮件发送列表';
$this->params['breadcrumbs'][] = ['label' => '邮件发送管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
	
	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) 
		{
			if(Yii::$app->session->hasFlash('email-create-success'))
			{
				$message = '邮件保存成功，等待发送！';
				echo '<div class="alert alert-success">' . $message . '</div>';
			} else if(Yii::$app->session->hasFlash('email-update-success')) {
				$message = '邮件更新成功，等待发送！';
				echo '<div class="alert alert-success">' . $message . '</div>';
			}
		}
	?>

<div class="email-list">

	<legend><?= Html::encode($this->title) ?></legend>
	<?= $this->render('_email_list_search', ['model' => $model]); ?>
	
	<?php Pjax::begin(['id' => 'email-list']) ?>
    <?= GridView::widget([
		'id' => 'email-list-form',
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
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'font-size:13px;'],
        'columns' => [
	    [
			'attribute' => 'id',
			'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
			'attribute' => '发送者',
			'value'		=> function($data) {
				if(User::findIdentity($data->sender))
					return User::findIdentity($data->sender)->email;
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center']
	    ],
		[
			'attribute' => '主题',
			'value'		=> 'subject',
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
				return ThEmailTemplate::getEmailStatus($data->status);
			},
			'headerOptions' => ['style'=>'text-align:center'],
	    ],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'headerOptions' => ['style'=>'text-align:center'],
			'template' => '{update} {view} {record}',
			'buttons' => [
				'update' => function($url, $data, $key) {
					return Html::button('', [
						'class'=>'glyphicon glyphicon-pencil', 
						'data-toggle' => 'tooltip', 
						'data-placement' => "top", 
						'title' => 'update', 
						'name' =>'update-button', 
						'id' => 'email-update'
					]);
				},
				'view' => function($url, $data, $key) {
					return Html::button('', [
						'class'=>'glyphicon glyphicon-eye-open', 
						'data-toggle' => 'tooltip', 
						'data-placement' => "top", 
						'title' => 'view', 
						'name' =>'view-button', 
						'id' => 'email-view'
					]);
				},
				'record' => function($url, $data, $key) {
					return Html::button('', [
						'class'=>'glyphicon glyphicon-list', 
						'data-toggle' => 'tooltip', 
						'data-placement' => "top", 
						'title' => 'record', 
						'name' =>'record-button', 
						'id' => 'email-record'
					]);
				},
			] 
		],
        ],
    ]); ?>
	<?php Pjax::end(); ?>
</div>
