<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-23 17:46:42
 */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\assets\AppAsset;
use backend\models\account\BindingModel;
use backend\models\record\ThAgencyBindingSearch;
use common\struct\AccountChangeType;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
AppAsset::register($this);
$this->registerCssFile('@web/js/jquery-jsonview/jquery.jsonview.min.css');
$this->registerJsFile('@web/js/jquery-jsonview/jquery.jsonview.min.js');
/* upload file */
$this->registerJsFile('@web/js/jq-upload/jquery.ui.widget.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload.js');
$this->registerJsFile('@web/js/bootstrap-fileinput/js/fileinput.min.js');
$this->registerCssFile('@web/css/bootstrap-fileinput/css/fileinput.min.css');

$this->registerJsFile('@web/js/request/layer.js');
$this->registerCssFile('@web/css/request/main.css');
$this->registerCssFile('@web/css/account-manager/binding.css');
$this->registerJsFile('@web/js/account-manager/binding.js');

$this->title = '帐户绑定管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="binding-change">
	<legend><?= Html::encode($this->title) ?></legend>
	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) {
			if(Yii::$app->session->hasFlash('binding-change-error')) {
				$errorMessage = '| ';
				foreach($info as $error) {
					$errorInfo = implode("|", $error);
					$errorMessage .= $errorInfo.'|';
				}
				$message = '帐户绑定失败，原因：'.$errorMessage;
				echo '<div class="alert alert-danger">' . $message . '</div>';
			}
		}
	?>
	
	<?php
		echo Tabs::widget([
		'items' => [
		[
			'label' => '单次操作',
			'headerOptions'	=> ['style' => 'width:120px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['account-manager/binding-change', 'action' => AccountChangeType::ACTION_SINGLE]),
			'active' => ($model->action == AccountChangeType::ACTION_SINGLE)
		],
		[
			'label' => '批量上传',
			'headerOptions'	=> ['style' => 'width:120px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['account-manager/binding-change', 'action' => AccountChangeType::ACTION_MANY]),
			'active' => ($model->action == AccountChangeType::ACTION_MANY)
		],
		],
		]);
	?>

<div class="form-border">
	<input type="hidden" id="bindingmodel-action" value="<?= $model->action;?>" />
	<div class="binding-change-form">
		<?php $form = ActiveForm::begin([
			'id' => 'bindingmodel-single-form',
			'options' => ['class' => 'form-horizontal', 'style' => 'margin-top:30px;display:none;'],
			'enableClientValidation' => true,
			'enableAjaxValidation' => true,
			'validationUrl' => Url::toRoute(['validate-binding']),
			'fieldConfig' => [
				'template' => "<div class='col-xs-3 col-sm-2 text-right'>{label}</div><span class='col-xs-5 col-sm-4'>{input}{error}</span>",
				'labelOptions' => ['class' => 'col-lg-l control-label'],
			],	
		]); ?>
	
		<?= Html::button('获取Account信息', ['class'=>'btn btn-sm btn-primary', 'name' => 'get-account-info', 'id' => 'get-account-info']); ?>

		<?= $form->field($model, 'account_id')->textInput(['style' => 'width:300px;', 'placeholder' => '请输入需要调整的Account ID']) ?>
		<!-- 非PC的帐户，显示出BM的详细信息-->
		<div id="binding-change-info">	
			<?= $form->field($model, 'account_name')->textInput(['style' => 'width:300px;', 'readonly' => 'true']) ?>
			<div class="form-group field-bindingmodel-account_status">
				<div class="col-xs-3 col-sm-2 text-right">
					<label class="col-lg-l control-label" for="bindingmodel-account_status">Account Status</label>
				</div>
				<span class="col-xs-5 col-sm-4">
					<div id="button-adaccount-status"><span class="btn btn-xs btn-success">ACTIVE</span></div>
					<input type="hidden" name="BindingModel[account_status]" id="bindingmodel-account_status" value="<?= $model->account_status ?>">
				</span>
			</div>
			<div id="binding-detail">
				<?= $form->field($model, 'business_id')->textInput(['style' => 'width:300px;', 'placeholder' => '请输入需要操作的BM ID']); ?>
				<?= $form->field($model, 'action_type')->dropDownList(ThAgencyBindingSearch::getActionType(), ['style' => 'width:150px', 'prompt' => '请选择操作类型']); ?>
				<div id="binding-permitted_roles">
					<?= $form->field($model, 'permitted_roles')->dropDownList(ThAgencyBindingSearch::getPermittedRoles(), ['style' => 'width:150px', 'prompt' => '请选择角色分配']); ?>
				</div>
				<div style="margin-left:50px;">
					<?= Html::Button('提交', ['class'=>'btn btn-primary','name' =>'submit-button', 'id' => 'binding-submit-button']) ?>
					<?= Html::resetButton('重置', ['class'=>'btn','name' =>'reset-button', 'id' => 'binding-reset-button']) ?>	
				</div>
			</div>
		</div>
		<?php ActiveForm::end(); ?>

	<form id="bindingmodel-upload_file-form" class="form-horizontal" method="post" style="margin-top:30px;">
		<div class="form-group field-bindingmodel-upload_file required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="bindingmodel-upload_file">文件上传</label>
			</div>
			<span class="col-xs-5 col-sm-4" style="width:330px;">
				<input id="bindingmodel-upload_file" name="BindingModel[upload_file]" type="file" class="file" multiple data-show-upload="false" data-show-caption="true">
			</span>
		</div>
		<hr style="width:90%;">
	</form>
	<div style="margin-left:60px;display:none;" id="bindingmodel-upload_file-button">
		<button type="button" id="" class="btn btn-primary binding-submit-button" name="submit-button">提交</button>
		<button type="reset" id="" class="btn binding-reset-button" name="reset-button">重置</button>	
	</div>
	</div>
	</div>
</div>
