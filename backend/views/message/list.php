<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\user\User;
use backend\models\record\ThMessage;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '消息管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-index">
	<legend><?= ThMessage::getMessageTitle($model->type); ?></legend>
    <?= GridView::widget([
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
		'tableOptions' => ['class'=>'table table-striped table-bordered table-hover table-condensed', 'style'=>'font-size:13px;'],
        'columns' => [
	    [
			'attribute' => 'id',
			'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
			'attribute' => '发送者',
			'value'		=> function($data) {
				if(User::findIdentity($data->send_id))
					return User::findIdentity($data->send_id)->email;
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center']
	    ],
	    [
			'attribute' => '消息状态',
			'value'		=> function($data) {
				return $data->status ? '已读' : '未读';
			},
			'headerOptions' => ['style'=>'text-align:center'],
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
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'headerOptions' => ['style'=>'text-align:center'],
			'template' => '{view}',
			'buttons' => [
				'view' => function($url, $data, $key) {
					return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url);
				}
			] 
		],
        ],
    ]); ?>

</div>
