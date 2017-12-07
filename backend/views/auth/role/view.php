<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */

$this->title = 'View Role: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Role', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = 'View';
?>
<div class="auth-item-view">
	<legend><?= Html::encode($this->title) ?></legend>
    <?= DetailView::widget([
        'model' => $model,
	'options' => ['class'=>'table table-striped table-bordered table-condensed detail-view', 'style'=>'font-size:12px;'],
        'attributes' => [
            'name',
            'description:ntext',
	    /*
            'rule_name',
            'data:ntext',
	     */
	    [
		'attribute' => 'permissions',
		'value' => join(', ', $model->permissions), 
	    ],
	    [
		'attribute' => 'created_at',
		'value' => date('Y-m-d H:i', $model->created_at),
	    ],
	    [
		'attribute' => 'updated_at',
		'value' => date('Y-m-d H:i', $model->updated_at),
	    ],
        ],
    ]) ?>

</div>
