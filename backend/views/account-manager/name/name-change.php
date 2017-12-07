<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-11-23 17:46:42
 */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\assets\AppAsset;
use backend\models\account\ChangeNameModel;
use common\struct\AccountChangeType;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
AppAsset::register($this);

/* json view */
$this->registerCssFile('@web/js/jquery-jsonview/jquery.jsonview.min.css');
$this->registerJsFile('@web/js/jquery-jsonview/jquery.jsonview.min.js');
/* upload file */
$this->registerJsFile('@web/js/jq-upload/jquery.ui.widget.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload.js');
$this->registerJsFile('@web/js/bootstrap-fileinput/js/fileinput.min.js');
$this->registerCssFile('@web/css/bootstrap-fileinput/css/fileinput.min.css');

$this->registerJsFile('@web/js/request/layer.js');
$this->registerCssFile('@web/css/request/main.css');
$this->registerCssFile('@web/css/account-manager/name.css');
$this->registerJsFile('@web/js/account-manager/name.js');


$this->title = '帐户名称管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="name-change">
	<legend><?= Html::encode($this->title) ?></legend>
	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) {
			if(Yii::$app->session->hasFlash('name-change-error')) {
				$errorMessage = '| ';
				foreach($info as $error) {
					$errorInfo = implode("|", $error);
					$errorMessage .= $errorInfo.'|';
				}
				$message = '帐户名称更新提交失败，原因：'.$errorMessage;
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
			'url' => Yii::$app->urlManager->createUrl(['account-manager/name-change', 'action' => AccountChangeType::ACTION_SINGLE]),
			'active' => ($model->action == AccountChangeType::ACTION_SINGLE)
		],
		[
			'label' => '批量上传',
			'headerOptions'	=> ['style' => 'width:120px;text-align:center;'],
			'options' => ['style' => 'margin:0 auto;'],
			'url' => Yii::$app->urlManager->createUrl(['account-manager/name-change', 'action' => AccountChangeType::ACTION_MANY]),
			'active' => ($model->action == AccountChangeType::ACTION_MANY)
		],
		],
		]);
	?>


<div class="form-border">
	<input type="hidden" id="changenamemodel-action" value="<?= $model->action;?>" />
	<div class="accountname-change-form">
		<?php $form = ActiveForm::begin([
			'id' => 'changenamemodel-single-form',
			'options' => ['class' => 'form-horizontal', 'style' => 'margin-top:30px;display:none;'],
			'enableClientValidation' => true,
			'enableAjaxValidation' => true,
			'validationUrl' => Url::toRoute(['validate-name']),
			'fieldConfig' => [
				'template' => "<div class='col-xs-3 col-sm-2 text-right'>{label}</div><span class='col-xs-5 col-sm-4'>{input}{error}</span>",
				'labelOptions' => ['class' => 'col-lg-l control-label'],
			],	
		]); ?>
	
		<?= Html::button('获取Account信息', ['class'=>'btn btn-sm btn-primary', 'name' => 'get-account-info', 'id' => 'get-account-info']); ?>

		<?= $form->field($model, 'account_id')->textInput(['style' => 'width:300px;', 'placeholder' => '请输入需要操作的Account ID']) ?>
		<!-- 如果BM为PC帐户，则返回的是错误信息 -->
		<div id="account-name-errormessage">
			<?= $form->field($model, 'error_message')->textInput(['style' => 'width:300px;', 'readonly' => 'true']) ?>
		</div>
		<!-- 非PC的帐户，显示出BM的详细信息-->
		<div id="name-change-info">	
			<?= $form->field($model, 'account_name')->textInput(['style' => 'width:300px;', 'readonly' => 'true']) ?>
			<div class="form-group field-changenamemodel-account_status">
				<div class="col-xs-3 col-sm-2 text-right">
					<label class="col-lg-l control-label" for="changenamemodel-account_status">Account Status</label>
				</div>
				<span class="col-xs-5 col-sm-4">
					<div id="button-adaccount-status"><span class="btn btn-xs btn-success">ACTIVE</span></div>
					<input type="hidden" name="ChangeNameModel[account_status]" id="changenamemodel-account_status" value="<?= $model->account_status ?>">
				</span>
			</div>

			<?= $form->field($model, 'new_account_name')->textInput(['style' => 'width:300px;', 'placeholder' => '请输入新的Account Name']) ?>
			<div style="margin-left:50px;">
				<?= Html::Button('提交', ['class'=>'btn btn-primary name-submit-button','name' =>'submit-button', 'id' => '']) ?>
				<?= Html::resetButton('重置', ['class'=>'btn name-reset-button','name' =>'reset-button', 'id' => '']) ?>	
			</div>
		</div>
		<?php ActiveForm::end(); ?>

		<form id="changenamemodel-upload_file-form" class="form-horizontal" method="post" style="margin-top:30px;">
		<div class="form-group field-changenamemodel-upload_file required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="changenamemodel-upload_file">文件上传</label>
			</div>
			<span class="col-xs-5 col-sm-4" style="width:330px;">
				<input id="changenamemodel-upload_file" name="ChangeNameModel[upload_file]" type="file" class="file" multiple data-show-upload="false" data-show-caption="true">
			</span>
		</div>
		<hr style="width:90%;">
		</form>
		<div style="margin-left:60px;display:none;" id="changenamemodel-upload_file-button">
			<button type="button" id="" class="btn btn-primary name-submit-button" name="submit-button">提交</button>
			<button type="reset" id="" class="btn name-reset-button" name="reset-button">重置</button>	
		</div>
		</div>
	</div>
</div>
