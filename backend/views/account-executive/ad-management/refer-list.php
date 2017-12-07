<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-07-18 17:07:01
 */


use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;
use yii\bootstrap\ActiveForm;
use common\models\Conversion;
use kartik\popover\PopoverX;
use backend\models\user\User;
use backend\models\record\ThAccountInfoSearch;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerCssFile('@web/js/jq-zoom/jquery.zoom.css');
$this->registerJsFile('@web/js/jq-zoom/jquery.zoom.min.js');
$this->registerCssFile('@web/js/jquery-jsonview/jquery.jsonview.min.css');
$this->registerJsFile('@web/js/jquery-jsonview/jquery.jsonview.min.js');

$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/account-executive.js');


$this->title = '广告帐户申请';
$this->params['breadcrumbs'][] = $this->title;
?>

<legend><?= Html::encode($this->title) ?></legend>
<!-- 以下为列表搜索框 -->
<div class="entity-search">
    <?php $form = ActiveForm::begin([
        'action' => ['refer-list'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
		]
    ]); ?>

    <?= $form->field($searchModel, 'fbaccount_id')->textInput(['placeholder' => '按account id搜索', 'style' => 'width:300px;']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php

	echo Tabs::widget([
    'items' => [
        [
			'label' => '帐户申请',
			'headerOptions'	=> ['style' => 'width:120px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['account-executive/refer-list', 'type' => $searchModel::DIRECT_CREDIT]),
			'active' => ($searchModel->type == $searchModel::DIRECT_CREDIT)
        ],
		[
            'label' => 'PC帐户申请',
			'headerOptions'	=> ['style' => 'width:120px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['account-executive/refer-list', 'type' => $searchModel::PARTITIONED_CREDIT]),
			'active' => ($searchModel->type == $searchModel::PARTITIONED_CREDIT)
        ],
	],
]);
?>

<div class="user-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'pager'=>[
			'firstPageLabel'=>"First",
			'prevPageLabel'=>'Prev',
			'nextPageLabel'=>'Next',
			'lastPageLabel'=>'Last',
			'options' => ['class'=>'pager'],
		],
		'rowOptions' => ['style'=>'text-align:center;'],
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'table-layout:fixed;word-break:break-all;text-align:center;font-size:12px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'refer_list_form'],
        'columns' => [
	    [
			'attribute' => 'ID',
			'value'		=> 'id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '开户时间',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return date('Ymd', $data['updated_at']);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '公司英文名称',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Html::a($data->entityInfo['name_en'], ['account-executive/entity-view', 'id' => $data->entityInfo['id']]);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '业务类型',
			'value'		=> 'entityInfo.vertical',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '时区',
			'value'		=> 'timezone_id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '开户名称',
			'value'		=> 'fbaccount_name',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '创建人',
			'value'		=> function($data)
			{
				if(User::findIdentity($data['user_id']))
					return User::findIdentity($data['user_id'])->email;
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '代理名称',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getCompany($data['company_id']);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => 'Request ID',
			'format'	=> 'raw',
			'value'		=> 'request_id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => 'Account ID',
			'format'	=> 'raw',
			'value'		=> 'fbaccount_id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '状态',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getAccountStatus($data['status']);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'template' => '{commit} {delete} {reason}',
			'buttons' => [
				'commit' => function($url, $data, $key) {
					if($data['status'] == ThAccountInfoSearch::getAccountStatus()['WAIT'] || $data['status'] == ThAccountInfoSearch::getAccountStatus()['REQUESTED_CHANGE'])
						return Html::button('', ['class'=>'glyphicon glyphicon-ok', 'name' =>'submit-button', 'id' => 'refer-submit']);	
				},
				'delete' => function($url, $data, $key) {
					if($data['status'] == ThAccountInfoSearch::getAccountStatus()['PENDING'] || $data['status'] == ThAccountInfoSearch::getAccountStatus()['REQUESTED_CHANGE'])
						return Html::button('', ['class'=>'glyphicon glyphicon-remove', 'name' =>'delete-button', 'id' => 'refer-delete']);
				},
				'reason' => function($url, $data, $key) {
					if($data['status'] == ThAccountInfoSearch::getAccountStatus()['DISAPPROVED'] || $data['status'] == ThAccountInfoSearch::getAccountStatus()['REQUESTED_CHANGE'])
						return Html::button('', ['class'=>'glyphicon glyphicon-eye-open', 'name' =>'reason-button', 'id' => 'refer-reason']);
				},
			],
			'headerOptions' => ['style'=>'text-align:center;width:50px;font-size:15px;'],
		],
		],
    ]); ?>
</div>

<!-- 以下为隐藏部分，提交开户时，供选择agency -->
<div id="planning_agency_business_id" style="display:none;margin:20px;auto;">
	<form id="" method="post" action="">
		<span style="float:left;">开户BM信息：</span>
		<span style="float:left;margin:0 5px 5px 5px;">
		<select id="requestmodel-planning_agency_business_id" class="form-control" name="RequestModel[planning_agency_business_id]" style="width:300px;">
			<?php 
				$option = '<option value="">请选择Businiess Manager</option>';
				foreach($businessInfo as $business)
				{
					$option .= '<option value='.$business->business_id.'>'. $business->business_name .'</option>';
				}
				echo $option;
			?>
		</select>
		</span>
		<table style="margin-top:5px;">
			<tr>
				<td><span class="star">开户备注信息：</span></td>
				<td><textarea id="requestmodel-additional_comment" name="RequestModel[additional_comment]" class="form-contorl" style="width:300px;height:100px;border:1px solid;"></textarea></td>
			</tr>
		</table>
	</form>
</div>


