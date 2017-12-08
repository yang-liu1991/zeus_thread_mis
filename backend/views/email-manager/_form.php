<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-02-24 16:56:57
 */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\assets\AppAsset;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
AppAsset::register($this);

$this->registerJsFile('@web/js/jq-upload/jquery.ui.widget.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload.js');
$this->registerCssFile('@web/css/bootstrap-fileinput/css/fileinput.min.css');
$this->registerJsFile('@web/js/bootstrap-fileinput/js/fileinput.min.js');
$this->registerCssFile('@web/css/summernote/summernote.css');
$this->registerJsFile('@web/js/summernote/summernote.js');
$this->registerJsFile('@web/js/request/email-form.js');

?>

	<?php $form = ActiveForm::begin([
		'id' => 'create-email-form',
		'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data'],
		'enableClientValidation' => true,
		'enableAjaxValidation' => true,
		'validationUrl' => Url::toRoute(['validate-email']),
		'fieldConfig' => [
			'template' => "<div class='col-xs-3 col-sm-2 text-right'>{label}</div><span class='col-xs-5 col-sm-4'>{input}{error}</span>",
			'labelOptions' => ['class' => 'col-lg-l control-label'],
		],	
	]); ?>

	<?php if($model->scenario == 'create'): ?>
		<div><input type="hidden" id="emailmodel-scenario" value="create"><div>
		<div class="form-group field-emailmodel-receiver required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-receiver">收件人</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="text" id="emailmodel-receiver" class="form-control" name="EmailModel[receiver]" value="" placeholder="sales@example.com">
				<div class="help-block"></div>
			</div>
		</div>	
		<div class="form-group field-emailmodel-receiver_file">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-receiver_file"></label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="file" id="emailmodel-receiver_file" name="EmailModel[receiver_file]" multiple>
				<div class="help-block"></div>
			</div>
		</div>	
		<div class="form-group field-emailmodel-subject required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-subject">邮件主题</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="text" id="emailmodel-subject" class="form-control" name="EmailModel[subject]" value="" placeholder="">
				<div class="help-block"></div>
			</div>
		</div>	
		<div class="form-group field-emailmodel-content required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-receiver">邮件内容</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<div class="form-control" id="summernote"></div>
				<input type="hidden" id="emailmodel-content" name="EmailModel[content]">
				<div class="help-block"></div>
			</div>
		</div>	
	<?php elseif($model->scenario == 'update'): ?>
		<div><input type="hidden" id="emailmodel-scenario" value="update"><div>
		<div class="form-group field-emailmodel-receiver required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-receiver">收件人</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="text" id="emailmodel-receiver" class="form-control" name="EmailModel[receiver]" value="<?= $model->receiver; ?>" placeholder="sales@example.com">
				<div class="help-block"></div>
			</div>
		</div>	
		<div class="form-group field-emailmodel-receiver_file">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-receiver_file"></label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="file" id="emailmodel-receiver_file" name="EmailModel[receiver_file]" multiple>
				<div class="help-block"></div>
			</div>
		</div>	
		<div class="form-group field-emailmodel-subject required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-subject">邮件主题</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="text" id="emailmodel-subject" class="form-control" name="EmailModel[subject]" value="<?= $model->subject; ?>" placeholder="">
				<div class="help-block"></div>
			</div>
		</div>	
		<div class="form-group field-emailmodel-content required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="emailmodel-receiver">邮件内容</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<div class="form-control" id="summernote"></div>
				<div style="display:none;"><input type="hidden" id="emailmodel-content" name="EmailModel[content]" value='<?= str_replace("'", '"', $model->content); ?>'></div>
				<div class="help-block"></div>
			</div>
		</div>	
	<?php endif; ?>
<?php ActiveForm::end(); ?>

