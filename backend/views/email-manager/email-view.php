<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\user\User;
use backend\models\record\ThEmailTemplate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerCssFile('@web/css/summernote/summernote.css');
$this->registerJsFile('@web/js/summernote/summernote.js');
$this->registerJsFile('@web/js/request/email-manager.js');

$this->title = '邮件发送详情';
$this->params['breadcrumbs'][] = ['label' => '邮件发送管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-view">

	<legend><?= Html::encode($this->title) ?></legend>
	
	<input type="hidden" id="mail-view-id" value="<?= $model->id; ?>">
	 <?= DetailView::widget([
        'model' => $model,
		'options' => ['class'=>'table table-condensed detail-view', 'style'=>'table-layout:fixed;word-break:break-all;font-size:13px;font-family: "Microsoft YaHei" ! important;'],
		'template' => '<tr><th style="width:120px;">{label}</th><td>{value}</td></tr>',
		'attributes' => [
		[
			'attribute' => 'id',
			'value'		=> $model->id,
			'valueColOptions'	=> ['id' => 'aaa'],
		],
		[
			'attribute' => '发送者',
			'value'		=> !empty($model->sender) ? User::findIdentity($model->sender)->email : '',
		],
		[
			'attribute' => '接收者',
			'value'		=> unserialize($model->receiver),
		],
		[
			'attribute' => '邮件主题',
			'format'	=> 'raw',
			'value'		=> $model->subject,
		],
		[
			'attribute' => '邮件内容',
			'format'	=> 'raw',
			'value'		=> unserialize($model->content),
		],
		[
			'attribute' => '消息状态',
			'format'	=> 'raw',
			'value'		=> ThEmailTemplate::getEmailStatus($model->status),
		],
	    [
			'attribute' => '创建时间',
			'value'		=> date('Y-m-d H:i', $model->created_at),
	    ],
	    [
			'attribute' => '更新时间',
			'value'		=> date('Y-m-d H:i', $model->updated_at),
	    ],
        ],
    ]) ?>
	
	<div class="form-group" style="">
		<?= Html::Button('Send Submit', ['class'=>'btn btn-primary','name' =>'send-button', 'id' => 'email-send-button']) ?>	
		<?= Html::Button('Email List', ['class'=>'btn btn-success','name' =>'list-button', 'id' => 'email-list-button']) ?>	
	</div>

</div>
