<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\auths\AuthItemSearcher */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Permission';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-index">
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
		'attribute' => 'name',
		'enableSorting' => false,
		'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
		'attribute' => 'description',
		'enableSorting' => false,
		'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
		'attribute' => 'created_at',
		'enableSorting' => false,
		'headerOptions' => ['style'=>'text-align:center'],
		'content' => function($model){
		    return date('Y-m-d H:i', $model->created_at);
		}
	    ],
	    [
		'attribute' => 'updated_at',
		'enableSorting' => false,
		'headerOptions' => ['style'=>'text-align:center'],
		'content' => function($model){
		    return date('Y-m-d H:i', $model->updated_at);
		}
	    ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
