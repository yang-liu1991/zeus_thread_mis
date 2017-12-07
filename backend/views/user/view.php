<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\user\AddUserForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'View User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'View';
?>
<div class="user-view">

	<legend><?= Html::encode($this->title) ?></legend>

    <?= DetailView::widget([
        'model' => $model,
		'options' => ['class'=>'table table-striped table-bordered table-condensed detail-view', 'style'=>'font-size:12px;'],
        'attributes' => [
            'id',
		[
			'attribute' => '用户名',
			'value'		=> 'email'
		],
	    [
			'attribute' => '用户角色',
			'value' => join(', ', $model->rbacRole), 
	    ],
		[
			'attribute' => '状态',
			'value'		=> 'realStatus',
		],
		[
			'attribute' => '所属公司',
			'format'	=> 'raw',
			'value'		=> !empty($model->company_id) ? AddUserForm::getCompanyName()[$model->company_id] : '',
		],
	    [
			'attribute' => '创建时间',
			'value' => date('Y-m-d H:i', $model->create_time),
	    ],
	    [
			'attribute' => '更新时间',
			'value'		=> date('Y-m-d H:i', $model->update_time),
	    ],
        ],
    ]) ?>

</div>
