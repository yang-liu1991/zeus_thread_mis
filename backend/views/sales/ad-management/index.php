<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-07-18 17:07:01
 */


use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '主体信息变更';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
		'rowOptions' => ['style'=>'text-align:center'],
		'tableOptions' => ['class'=>'table table-striped table-bordered table-hover table-condensed', 'style'=>'font-size:12px;'],
        'columns' => [
	    [
			'attribute' => '注册公司英文名称',
			'value'		=> 'name_en',
			'headerOptions' => ['style'=>'text-align:center'],
	    ],
		[
			'attribute' => '注册公司中文名称',
			'value'		=> 'name_zh',
			'headerOptions' => ['style'=>'text-align:center'],
	    ],
	    [
			'attribute' => 'created_at',
			'headerOptions' => ['style'=>'text-align:center'],
			'content' => function($model){
			    return date('Y-m-d H:i', $model->created_at);
			}
	    ],
	    [
			'attribute' => 'updated_at',
			'headerOptions' => ['style'=>'text-align:center'],
			'content' => function($model){
			    return date('Y-m-d H:i', $model->updated_at);
			}
	    ],
        ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
