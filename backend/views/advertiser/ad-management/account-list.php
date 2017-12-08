<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-07-18 17:07:01
 */


use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;
use yii\bootstrap\ActiveForm;
use common\models\Conversion;
use kartik\datetime\DateTimePicker;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThRemindRecordSearch;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerCssFile('@web/js/jquery-jsonview/jquery.jsonview.min.css');
$this->registerJsFile('@web/js/jquery-jsonview/jquery.jsonview.min.js');
$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/account-list.js');


$this->title = 'Facebook广告帐户申请列表';
$this->params['breadcrumbs'][] = $this->title;
?>

<legend><?= Html::encode($this->title) ?></legend>

	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) 
		{
			if(Yii::$app->session->hasFlash('account-apply-success'))
			{
				$message = '开户申请提交成功，等待审核！';
				echo '<div class="alert alert-success">' . $message . '</div>';
			}
		}
	?>

<!-- 以下为列表搜索框 -->
<div class="entity-search">
    <?php $form = ActiveForm::begin([
		'id' => 'account-list-search',
        'action' => ['advertiser/account-list'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
		]
    ]); ?>
	
	<?= $form->field($searchModel, 'begin_time')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择起始时间', 'style' => 'width:150px'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,
			'minView' => 2,
			'format' => 'yyyy-mm-dd'
		]   
	]); ?>  

	<?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择结束时间', 'style' => 'width:150px'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,   
			'minView' => 2,
			'format' => 'yyyy-mm-dd'
		]   
	]); ?> 
	
	<?= $form->field($searchModel, 'status')->dropDownList([0 => 'BLUEFOCUS', 1 => 'FACEBOOK', 3 => 'APPROVED', 4 => 'REQUESTED_CHANGE', 5 => 'DISAPPROVED', 6 => 'CANCELLED'], [
		'prompt'=>'请选择状态', 
		'style' => 'float:left;width:150px;'])->label('状态筛选') ?>

    <?= $form->field($searchModel, 'name_zh')->textInput(['placeholder' => '按广告主名称搜索', 'style' => 'width:300px;']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
		<?= Html::a('申请开户', ['/advertiser/account-apply'], ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

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
		'rowOptions' => ['style'=>'text-align:center'],
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'font-size:12px;', 'id' => 'account-list-form'],
        'columns' => [
		[
			'attribute' => 'ID',
			'value'		=> 'id',
			'headerOptions' => ['style'=>'text-align:center'],
		],
	    [
			'attribute' => '提交日期',
			'value'		=> function($data) {
				return date('Ymd', $data->updated_at);
			},
			'headerOptions' => ['style'=>'text-align:center'],
		],
		[
			'attribute' => '广告主名称',
			'value'		=> 'entityInfo.name_zh',
			'headerOptions' => ['style'=>'text-align:center'],
		],
		[
			'attribute' => '业务类型',
			'value'		=> 'entityInfo.vertical',
			'headerOptions' => ['style'=>'text-align:center'],
		],
		[
			'attribute' => '开户名称',
			'value'		=> 'fbaccount_name',
			'headerOptions' => ['style'=>'text-align:center'],
		],
		[
			'attribute' => 'Account ID',
			'value'		=> 'fbaccount_id',
			'headerOptions' => ['style'=>'text-align:center'],
		],
		[
			'attribute' => '时区',
			'value'		=> 'timezone_id',
			'headerOptions' => ['style'=>'text-align:center'],
		],
		[
			'attribute' => '状态',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getAdAccountStatus($data->status);
			},
			'headerOptions' => ['style'=>'text-align:center'],
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'template' => '{remind} {reason}',
			'buttons' => [
				'remind' => function($url, $model, $key) {
					if($model->status == ThAccountInfoSearch::getAccountStatus()['WAIT'] ||
						$model->status == ThAccountInfoSearch::getAccountStatus()['PENDING'] ||
						$model->status == ThAccountInfoSearch::getAccountStatus()['UNDER_REVIEW'])
					{
						$remind = Conversion::getRemind($model->status);
						$remindRecord = ThRemindRecordSearch::find()->where(['account_id' => $model->id])->one();
						if($remindRecord)
						{
							if($remindRecord->status == ThRemindRecordSearch::WAITING_BF || 
								$remindRecord->status == ThRemindRecordSearch::WAITING_FB)
							{
								return Html::button('', [
									'class'=>'glyphicon glyphicon-envelope', 
									'data-toggle' => 'tooltip', 
									'data-placement' => "top", 
									'title' => $remind, 
									'name' =>'remind-button', 
									'id' => 'refer-remind']);
							} else {
								return Html::button('', [
									'class'=>'glyphicon glyphicon-eye-open', 
									'data-toggle' => 'tooltip', 
									'data-placement' => "top", 
									'title' => sprintf('Have been %s, please wait!', strtolower($remind)), 
									'name' =>'view-button', 
									'id' => 'refer-view']);
							}
						}
					}
				},
				'reason' => function($url, $model, $key) {
					if($model->status == ThAccountInfoSearch::getAccountStatus()['DISAPPROVED'] || 
						$model->status == ThAccountInfoSearch::getAccountStatus()['REQUESTED_CHANGE'])
					{
						return Html::button('', [
							'class'=>'glyphicon glyphicon-eye-open', 
							'data-toggle' => 'tooltip', 
							'data-placement' => "top", 
							'title' => 'Reason', 
							'name' =>'reason-button', 
							'id' => 'refer-reason']);
					}	
				},
			],
			'headerOptions' => ['style'=>'text-align:center;', 'width'=>110],
		],
	    ],
    ]); ?>

</div>

