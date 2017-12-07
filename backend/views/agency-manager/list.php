<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\assets\AppAsset;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\record\ThAgencyBusinessSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

AppAsset::register($this);
$this->registerJsFile('@web/js/agency-manager/agency-list.js');

$this->title = 'Agency Businesses List';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
foreach (Yii::$app->session->getAllFlashes() as $key => $info)
{
    if(Yii::$app->session->hasFlash('agency-delete-success'))
    {
        $message = 'Agency Businesses 删除成功!';
        echo '<div class="alert alert-success">' . $message . '</div>';
    }
}
?>

<div class="th-agency-business-list">

    <legend><?= Html::encode($this->title) ?></legend>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'pager'=>[
            'firstPageLabel'=>"First",
            'prevPageLabel'=>'Prev',
            'nextPageLabel'=>'Next',
            'lastPageLabel'=>'Last',
            'options' => ['class'=>'pager'],
        ],
        'rowOptions' => ['style'=>'text-align:center'],
        'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'text-align:center;font-size:13px;font-family: "Microsoft YaHei" ! important;', 'id' => 'agency-business-list'],
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute'     => 'BM Id',
                'value'         => 'business_id',
                'headerOptions' => ['style' => 'text-align:center;font-size:15px;']
            ],
            [
                'attribute'     => 'BM Name',
                'value'         => 'business_name',
                'headerOptions' => ['style' => 'text-align:center;font-size:15px;']
            ],
            [
                'attribute'     => 'Referral Email',
                'value'         => 'referral',
                'headerOptions' => ['style' => 'text-align:center;font-size:15px;']
            ],
            [
                'attribute' => 'created_at',
                'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
                'content' => function($model){
                    return date('Y-m-d H:i', $model->created_at);
                }
            ],
            [
                'attribute' => 'updated_at',
                'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
                'content' => function($model){
                    return date('Y-m-d H:i', $model->updated_at);
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
