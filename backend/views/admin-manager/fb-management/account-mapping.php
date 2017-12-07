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
use backend\models\record\ThAccountInfoSearch;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerJsFile('@web/js/request/request.js');


$this->title = '公司帐户对应';
$this->params['breadcrumbs'][] = $this->title;
?>

<legend><?= Html::encode($this->title) ?></legend>
<!-- 以下为列表搜索框 -->
<div class="entity-search">
    <?php $form = ActiveForm::begin([
		'id'	=> 'account-mapping',
        'action' => ['account-mapping'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
		]
    ]); ?>

	<?= $form->field($searchModel, 'company_id')->dropDownList(ThAccountInfoSearch::getCompanyName(), ['prompt'=>'请选择代理', 'style' => 'float:left;width:250px;'])->label('代理筛选') ?>
    <?= $form->field($searchModel, 'name_zh')->textInput(['placeholder' => '请输入中文名称查询', 'style' => 'width:300px;'])?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


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
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'text-align:center;font-size:15px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'entity_list_form'],
        'columns' => [
	   	[
			'attribute' => '注册公司英文名称',
			'format'	=> 'raw',
			'value'		=> 'entityInfo.name_en',
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '注册公司中文名称',
			'value'		=> 'entityInfo.name_zh',
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute'	=> '代理',
			'value'		=> function($data) {
				return Conversion::getCompany($data->company_id);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '产品URL',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getPromotableUrls($data->entityInfo['promotable_urls'], 'all');
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => 'Account ID',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return ThAccountInfoSearch::getAccountidStr($data->entityInfo['id']);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		],
    ]); ?>
</div>

