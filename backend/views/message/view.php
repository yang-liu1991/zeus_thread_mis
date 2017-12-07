<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\user\User;
use backend\models\message\MessageModel;

/* @var $this yii\web\View */

$this->title = '阅读消息: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '消息列表', 'url' => ['index']];
?>
<div class="message-view">

	<legend><?= Html::encode($this->title) ?></legend>

    <?= DetailView::widget([
        'model' => $model,
		'options' => ['class'=>'table table-striped table-bordered table-condensed detail-view', 'style'=>'font-size:12px;'],
        'attributes' => [
            'id',
		[
			'attribute' => '发送者',
			'value'		=> !empty($model->send_id) ? User::findIdentity($model->send_id)->email : '',
		],
		[
			'attribute' => '消息内容',
			'format'	=> 'raw',
			'value'		=> MessageModel::formatMessage($model->type, $model->message),
		],
		[
			'attribute' => '消息状态',
			'value'		=> $model->status ? '已读' : '未读',
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

</div>
