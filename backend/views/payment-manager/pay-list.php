<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\record\ThAccountInfoSearch;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\record\ThPaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/payment.js');

$this->title = '帐户付款信息管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="th-payment-list">

    <legend><?= Html::encode($this->title) ?></legend>

	<?= $this->render('_search', ['searchModel' => $searchModel])?>
	
	<?php Pjax::begin(['id' => 'paymentlist']) ?>
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
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'table-layout:fixed;word-break:break-all;text-align:center;font-size:12px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'payment_list_form'],
        'columns' => [
		[
			'attribute' => 'Account Id',
			'value'		=> 'fbaccount_id',
			'headerOptions' => ['style'=>'text-align:center;width:120px;font-size:15px;'],
		],
		[
			'attribute' => '公司英文名称',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Html::a($data->entityInfo["name_en"], ['account-executive/entity-view', 'id' => $data->entityInfo["id"]]);
			},
			'headerOptions' => ['style'=>'text-align:center;width:140px;font-size:15px;'],
		],
		[
			'attribute' => '注册公司中文名称',
			'value'		=> 'entityInfo.name_zh',
			'headerOptions' => ['style'=>'text-align:center;width:140px;font-size:15px;'],
		], 
		[
			'attribute' => '广告商产业类型',
			'value'		=> 'entityInfo.vertical',
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
			'attribute' => '备注',
			'value'		=> 'pay_comment',
			'headerOptions' => ['style'=>'text-align:center;width:200px;font-size:15px;'],
        ],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'template' => '{create} {history}',
			'buttons' => [
				'create' => function($url, $data, $key) {
					if($data['pay_name_real'])
					{
						return Html::button('编辑信息', ['name' =>'update-payment-button', 'class' => 'btn-info btn-xs', 'id' => 'update-payment-button']);	
					} else {
						return Html::button('添加信息', ['name' =>'create-payment-button', 'class' => 'btn-success btn-xs', 'id' => 'create-payment-button']);	
					}
				},
				'history' => function($url, $data, $key) {
					return Html::button('历史记录', ['name' =>'history-payment-button', 'class' => 'btn-warning btn-xs', 'id' => 'history-payment-button']);
				},
			],
			'headerOptions' => ['style'=>'text-align:center;width:140px;font-size:15px;'],
		]
	]
    ]); ?>
	<?php Pjax::end(); ?>
</div>

<!-- 添加与编辑付款信息表单-->
<div id="payment_message" style="display:none;margin:20px;auto;">
	<form id="edit_payment_form" method="post" action="<?php Yii::$app->urlManager->createUrl(['payment-manager/pay-edit']); ?>">
		<input type="hidden" id="paymentmodel-account_id" name="PaymentModel[account_id]" value="">
		 <div class="input-group" style="width:400px;">
			<span class="input-group-addon" style="width:110px;">实际付款主体</span>
            <input type="text" id="paymentmodel-pay_name_real" class="form-control" placeholder="请输入实际付款主体" name="PaymentModel[pay_name_real]">
        </div>
		<br />
		<div class="input-group" style="width:400px;">
			<span class="input-group-addon" style="width:110px;">结算方式</span>
			<select id="paymentmodel-pay_type" class="form-control" placeholder="请选择结算方式" name="PaymentModel[pay_type]">
				<option value="0" selected="selected">请选择结算方式</option>
				<option value="1">CPA-代投</option>
				<option value="2">Cost-代投</option>
				<option value="3">Cost-客户自投</option>
				<option value="4">CPA-外部代投</option>
			</select>
		</div>
		<br/>
		<div class="input-group" style="width:400px;">
			<span class="input-group-addon" style="width:110px;">备注</span>
			<textarea id="paymentmodel-pay_comment" name="PaymentModel[pay_comment]" placeholder="填写备注信息" class="form-control" rows="3"></textarea>
		</div>
	</form>
</div>

