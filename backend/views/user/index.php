<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users List';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

	<legend><?= Html::encode($this->title) ?></legend>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

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
		'tableOptions' => ['class'=>'table table-striped table-bordered table-hover table-condensed', 'style'=>'font-size:12px;'],
        'columns' => [
	    [
		'attribute' => 'id',
		'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
		'attribute' => 'email',
		'contentOptions' => ['style'=>'text-align:left']
	    ],
	    [
		'attribute' => 'realStatus',
		'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
		'attribute' => 'create_time',
		'headerOptions' => ['style'=>'text-align:center'],
		'content' => function($model){
		    return date('Y-m-d H:i', $model->create_time);
		}
	    ],
	    [
		'attribute' => 'update_time',
		'headerOptions' => ['style'=>'text-align:center'],
		'content' => function($model){
		    return date('Y-m-d H:i', $model->update_time);
		}
	    ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
