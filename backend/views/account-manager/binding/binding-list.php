<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-25 16:05:13
 */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;
use backend\models\user\User;
use common\struct\AccountChangeStatus;
use backend\models\record\ThAgencyBindingSearch;

$this->registerCssFile('@web/js/jquery-jsonview/jquery.jsonview.min.css');
$this->registerJsFile('@web/js/jquery-jsonview/jquery.jsonview.min.js');
/* upload file */
$this->registerJsFile('@web/js/jq-upload/jquery.ui.widget.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload.js');
$this->registerJsFile('@web/js/bootstrap-fileinput/js/fileinput.min.js');
$this->registerCssFile('@web/css/bootstrap-fileinput/css/fileinput.min.css');

$this->registerJsFile('@web/js/request/layer.js');
$this->registerCssFile('@web/css/account-manager/binding.css');
$this->registerJsFile('@web/js/account-manager/binding.js');


$this->title = '帐户绑定列表';
$this->params['breadcrumbs'][] = $this->title;
?>

<legend><?= Html::encode($this->title) ?></legend>

	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) {
			if(Yii::$app->session->hasFlash('binding-change-success'))
			{
				$message = '帐户操作成功！';
				echo '<div class="alert alert-success">' . $message . '</div>';
			}
		}
	?>

<?= 
	$this->render('_search', [
		'searchModel'	=> $searchModel
	]);
?>

<div class="form-inline well">
	<?= Html::Button('全选', ['class' => 'btn btn-info btn-xs', 'name' => 'button-of-all', 'id' => 'button-of-all']);?>
	<?= Html::Button('全不选', ['style'=> 'margin:0 0 0 10px;', 'class' => 'btn btn-danger btn-xs', 'name' => 'button-of-none', 'id' => 'button-of-none']);?>
	<?= Html::Button('提交', ['style'=> 'margin:0 0 0 10px;', 'class' => 'btn btn-primary btn-xs', 'name' => 'button-of-submit', 'id' => 'button-of-submit']);?>
	<div style="text-align:center;" id="submit-loading"></div>
</div>

<div class="binding-list">
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
		'tableOptions' => ['class'=>'table table-striped table-hover table-condensed', 'style'=>'table-layout:fixed;word-break:break-all;text-align:center;font-size:13px;font-family: "Microsoft YaHei" ! important; ', 'id'=>'binding_list_form'],
        'columns' => [
        [
			'attribute' => '全选',
			'format'	=> 'raw',
			'value'		=> function($data) {
				if($data->status == AccountChangeStatus::ACCOUNT_CHANGE_WAITING)
					return sprintf('<input type="checkbox" name="checkbox" value="%d"/>', $data->id);
				return sprintf('<input type="hidden" value="%d"/>', $data->id);
			},
			'headerOptions' => ['style'=>'width:50px;text-align:center;font-size:15px;'],
		],
		[
			'attribute' => 'ID',
			'value'		=> 'id',
			'headerOptions' => ['style'=>'width:50px;text-align:center;font-size:15px;'],
		],
	    [
			'attribute' => 'Account ID',
			'value'		=> 'account_id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => 'Account Name',
			'value'		=> 'account_name',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => 'BM ID',
			'value'		=> 'business_id',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => 'BM Name',
			'value'		=> 'business_name',
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => 'Roles',
			'format'	=> 'raw',
			'value'		=> function($data) {
				if($data->permitted_roles) return $data->permitted_roles;
				return '';
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '操作类型',
			'format'	=> 'raw',
			'value'		=> function($data) { 
				if(array_key_exists($data->action_type, ThAgencyBindingSearch::getActionType()))
					return ThAgencyBindingSearch::getActionType()[$data->action_type]; 
				return 'Unknow';
			},
			'headerOptions' => ['style'=>'text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '提交人',
			'value'		=> function($data) {
				if(User::findIdentity($data->user_id))
					return User::findIdentity($data->user_id)->email;
				return '';
			},
			'headerOptions' => ['style'=>'width:180px;text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '提交时间',
			'value'		=> function($data) {
				return date('Ymd', $data->created_at); 
			},
			'headerOptions' => ['style'=>'width:70px;text-align:center;font-size:15px;'],
		],
		[
			'attribute' => '状态',
			'format'	=> 'raw',
			'value'		=> function($data) {
				return AccountChangeStatus::getAccountChangeStatus($data->status);
			},
			'headerOptions' => ['style'=>'width:60px;text-align:center;font-size:15px;'],
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => '操作',
			'template' => '{submit} {reject} {reason}',
			'buttons' => [
				'reason' => function($url, $data, $key) {
					if($data['status'] == AccountChangeStatus::ACCOUNT_CHANGE_FAILED)
						return Html::button('', ['class'=>'glyphicon glyphicon-eye-open', 'name' =>'reason-button', 'id' => 'binding-reason']);
				},
				'submit' => function($url, $data, $key) {
					if(Yii::$app->user->can('admin_group') && $data['status'] == AccountChangeStatus::ACCOUNT_CHANGE_WAITING)
						return Html::button('', ['class'=>'glyphicon glyphicon-ok', 'name' =>'submit-button', 'id' => 'binding-submit']);
				},
				'reject' => function($url, $data, $key) {
					if(Yii::$app->user->can('admin_group') && $data['status'] == AccountChangeStatus::ACCOUNT_CHANGE_WAITING)
						return Html::button('', ['class'=>'glyphicon glyphicon-remove', 'name' =>'reject-button', 'id' => 'binding-reject']);
				},
			],
			'headerOptions' => ['style'=>'text-align:center;width:80px;font-size:15px;'],
		]
	],
]);
?>

<div id="binding_reject_reason" style="display:none;margin:20px;auto;">
	<form id="" method="post" action="">
		<input type="hidden" id="bindingmodel-account_id" name="BindingModel[account_id]" value="">
		<div class="input-group" style="width:450px;">
			<span class="input-group-addon" style="width:50px;">驳回原因</span>
			<textarea id="bindingmodel-reason" name="BindingModel[reason]" placeholder="请填写驳回原因!" class="form-control" rows="4"></textarea>
		</div>
	</form>
</div>


