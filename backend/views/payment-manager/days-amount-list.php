<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;
use yii\widgets\Pjax;
use common\models\AmountConversion;
use backend\models\payment\AmountModel;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThSpendReportSearch;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\record\ThPaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/days-amount.js');

$this->title = '广告主分天数据查看';
$this->params['breadcrumbs'][] = ['label' => '广告主金额管理', 'url' => '#'];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="th-amount-list">

    <legend><?= Html::encode($this->title) ?></legend>

	<?= $this->render('_days_amount_search', ['searchModel' => $searchModel, 'spendReportModel' => $spendReportModel])?>
	
	<?php Pjax::begin(['id' => 'days-amount-list']) ?>
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
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'table-layout:fixed;word-break:break-all;text-align:center;font-size:12px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'days-amount_list_form'],
        'columns' => [
		[
			'attribute' => 'Account Id',
			'value'		=> 'fbaccount_id',
			'headerOptions' => ['style'=>'text-align:center;width:120px;font-size:15px;'],
		],
		[
			'attribute' => '注册公司中文名称',
			'value'		=> 'entityInfo.name_zh',
			'headerOptions' => ['style'=>'text-align:center;width:140px;font-size:15px;'],
		], 
		[
			'attribute' => '付款名称',
			'value'		=> 'entityInfo.payname',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '结算方式',
			'format'	=> 'raw',
			'value'		=> function($data) {
				if(!empty($data['pay_type']))
					return ThAccountInfoSearch::getPaymentType($data['pay_type']);
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
        ],
		[
			'attribute' => '实际付款主体',
			'format'	=> 'raw',
			'value'		=> function($data) {
				if(!empty($data['pay_name_real']))
					return $data['pay_name_real'];
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
        ],
		[
			'attribute' => '金额',
			'format'	=> 'raw',
			'value'		=> function($data) {
				$queryParams	= Yii::$app->request->queryParams;
				$queryParams['ThSpendReportSearch']['account_id'] = $data['fbaccount_id'];
				$accountSpendTotal		= AmountModel::getSpendTotalData($queryParams);
				return AmountConversion::centToDollar($accountSpendTotal);
			},
			'headerOptions' => ['style'=>'text-align:center;width:200px;font-size:15px;'],
        ],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'template' => '{amount-detail}',
			'buttons' => [
				'amount-detail' => function($url, $data, $key) {
					return Html::button('查看帐户明细', ['name' =>'amount-detail-button', 'class' => 'btn-success btn-xs', 'id' => 'amount-detail-button']);	
				},
			],
			'headerOptions' => ['style'=>'text-align:center;width:140px;font-size:15px;'],
		]
	]
    ]); ?>
	<?php Pjax::end(); ?>
</div>
<div id="">
	<input type="hidden" id="paymentmodel-account_id" name="PaymentModel[account_id]" value="">
</div>
