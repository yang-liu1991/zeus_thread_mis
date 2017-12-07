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
$this->registerJsFile('@web/js/request/company-amount.js');

$this->title = '广告主金额查看';
$this->params['breadcrumbs'][] = ['label' => '广告主金额管理', 'url' => '#'];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="th-amount-list">

    <legend><?= Html::encode($this->title) ?></legend>

	<?= $this->render('_company_amount_search', ['searchModel' => $searchModel, 'spendReportModel' => $spendReportModel])?>
	
	<?php Pjax::begin(['id' => 'company-amount-list']) ?>
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
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'table-layout:fixed;word-break:break-all;text-align:center;font-size:12px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'company-amount_list_form'],
        'columns' => [
		[
			'attribute' => 'Id',
			'value'		=> 'id',
			'headerOptions' => ['style'=>'text-align:center;width:120px;font-size:15px;'],
		],
		[
			'attribute' => '注册公司中文名称',
			'value'		=> 'name_zh',
			'headerOptions' => ['style'=>'text-align:center;width:140px;font-size:15px;'],
		], 
		[
			'attribute' => '付款名称',
			'value'		=> 'payname',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '结算方式',
			'format'	=> 'raw',
			'value'		=> function($data) {
				if(!empty($data->accountInfo))
					return ThAccountInfoSearch::getPaymentType($data->accountInfo[0]->pay_type);
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
        ],
		[
			'attribute' => '实际付款主体',
			'format'	=> 'raw',
			'value'		=> function($data) {
				 if(!empty($data->accountInfo))
					 return $data->accountInfo[0]['pay_name_real'];
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
        ],
		[
			'attribute' => '金额',
			'format'	=> 'raw',
			'value'		=> function($data) {
				$queryParams	= Yii::$app->request->queryParams;
				$queryParams['ThAccountInfoSearch']['entity_id'] = $data->id;
				$companySpendTotal		= AmountModel::getCompanyAmountTotal($queryParams);
				if(!empty($companySpendTotal))
					return AmountConversion::centToDollar($companySpendTotal);
				return '';
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
