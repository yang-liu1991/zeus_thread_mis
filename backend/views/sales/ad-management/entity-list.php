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
use backend\models\record\ThEntityInfoSearch;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/audit.js');


$this->title = '公司主体审核';
$this->params['breadcrumbs'][] = $this->title;
?>

<legend><?= Html::encode($this->title) ?></legend>

<!-- 以下为列表搜索框 -->
<div class="entity-search">
    <?php $form = ActiveForm::begin([
        'action' => ['ae-list', 'audit_status' => $entityModel->audit_status],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
		]
    ]); ?>

    <?= $form->field($entityModel, 'name_zh')->textInput(['placeholder' => '按中文名称搜索', 'style' => 'width:300px;']) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
	echo Tabs::widget([
    'items' => [
        [
            'label' => '全部',
			'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['sales/entity-list', 'audit_status' => ThEntityInfoSearch::AUDIT_STATUS_ALL]),
			'active' => ($entityModel->audit_status == ThEntityInfoSearch::AUDIT_STATUS_ALL)
        ],
        [
            'label' => '待审核',
			'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['sales/entity-list', 'audit_status' => ThEntityInfoSearch::AUDIT_STATUS_WAIT]),
			'active' => ($entityModel->audit_status == ThEntityInfoSearch::AUDIT_STATUS_WAIT)
        ],
        [
            'label' => '已拒绝',
			'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['sales/entity-list', 'audit_status' => ThEntityInfoSearch::AUDIT_STATUS_FAILED]),
			'active' => ($entityModel->audit_status == ThEntityInfoSearch::AUDIT_STATUS_FAILED)
		],
    ],
]);
?>

<div class="user-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'rowOptions' => ['style'=>'text-align:center;'],
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'text-align:center;font-size:15px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'entity_list_form'],
        'columns' => [
	    [
			'attribute' => 'ID',
			'value'		=> 'entityInfo.id',
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '注册公司英文名称',
			'format'	=> 'raw',
			'value'		=> function ($data) {
				return Html::a($data->entityInfo->name_en, ['sales/detail', 'id' => $data['entity_id']]);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '注册公司中文名称',
			'value'		=> 'entityInfo.name_zh',
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '注册公司地址',
			'value'		=> 'entityInfo.address',
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '广告商产品URL',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getWebsite($data->entityInfo->website, 'all');
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '广告商产业类型',
			'value'		=> 'entityInfo.industry',
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '付款公司名称',
			'value'		=> 'entityInfo.payname',
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '操作类型',
			'value'		=> function($data) {
				return Conversion::getActionType($data->entityInfo->created_at, $data->entityInfo->updated_at);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'attribute' => '备注',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return Conversion::getNoteStatus($data->entityInfo->entity_note);
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'template' => '{comment}',
			'buttons' => [
				'comment' => function($data) {
					return Html::button('备注', ['class'=>'btn btn-success', 'name' =>'submit-button', 'id' => 'entity_note']);	
				},
			],
			'headerOptions' => ['style'=>'text-align:center;font-size:17px;'],
			'options'	=> ['style' => 'width:200px;'],
		],
		],
    ]); ?>
</div>

<!-- 以下为备注信息表单 -->
<div id="entity_message" style="display:none;margin:20px;auto;">
	<form id="entity_note_form" method="post" action="<?php Yii::$app->urlManager->createUrl(['entity/audit']); ?>">
		<input type="hidden" id="entity_id" name="entityId" value="<?php echo $searchModel->id ?>">
		<table>
			<tr>
				<td><span class="star">备注信息：</span></td>
				<td><textarea id="entity_text" name="entityText" class="form-contorl" style="width:450px;height:300px;"></textarea></td>
			</tr>
		</table>
	</form>
</div>
