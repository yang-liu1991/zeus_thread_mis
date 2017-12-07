<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-07-18 17:07:01
 */


use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;
use common\models\Conversion;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerCssFile('@web/js/jq-zoom/jquery.zoom.css');
$this->registerJsFile('@web/js/jq-zoom/jquery.zoom.min.js');
$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/audit.js');


$this->title = '帐号信息异常查看';
$this->params['breadcrumbs'][] = $this->title;
?>

<legend><?= Html::encode($this->title) ?></legend>

<?= 
	$this->render('_search', [
		'searchModel'	=> $searchModel
	]);
?>
<?php
	echo Tabs::widget([
    'items' => [
        [
            'label' => '异常帐户',
			'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['admin-manager/abnormal-list', 'status' => $searchModel::getAccountStatus()['ABNORMAL']]),
			'active' => ($searchModel->status == $searchModel::getAccountStatus()['ABNORMAL'])
        ],
		[
            'label' => '已封停',
			'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['admin-manager/abnormal-list', 'status' => $searchModel::getAccountStatus()['FORCEOUT']]),
			'active' => ($searchModel->status == $searchModel::getAccountStatus()['FORCEOUT'])
		],
    ],
]);
?>

<div class="user-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'pager'=>[
			'firstPageLabel'=>"First",
			'prevPageLabel'=>'Prev',
			'nextPageLabel'=>'Next',
			'lastPageLabel'=>'Last',
			'options' => ['class'=>'pager'],
		],
		'rowOptions' => ['style'=>'text-align:center;'],
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'table-layout:fixed;word-break:break-all;text-align:center;font-size:13px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'entity_list_form'],
        'columns' => [
	    [
			'attribute' => 'Account ID',
			'value'		=> 'fbaccount_id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '注册公司英文名称',
			'format'	=> 'raw',
			'value'		=> function ($data) {
				return Html::a($data->entityInfo->name_en, ['admin-manager/detail', 'id' => $data->entityInfo->id]);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '产品URL',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getPromotableUrls($data->entityInfo->promotable_urls, 'normal');
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '异常产品URL',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getPromotableUrls($data->entityInfo->promotable_urls, 'abnormal');
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '异常时间',
			'value'		=> function($data) {
				return date('Ymd H:i:s', $data->updated_at);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute'	=> '状态',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getAccountStatus($data->status);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		]
		],
    ]); ?>
</div>

