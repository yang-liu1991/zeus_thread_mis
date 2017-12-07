<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\assets\AppAsset;
use backend\models\account\FbVertical;
use backend\models\record\ThEntityInfoSearch;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
/* @var $form yii\widgets\ActiveForm */
AppAsset::register($this);
$this->registerJsFile('@web/js/jq-upload/jquery.ui.widget.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload.js');
$this->registerCssFile('@web/css/bootstrap-fileinput/css/fileinput.min.css');
$this->registerCssFile('@web/css/request/main.css');
$this->registerJsFile('@web/js/bootstrap-fileinput/js/fileinput.min.js');
$this->registerJsFile('@web/js/request/entity-form.js');
?>

<div class="form-border">

	<?php $form = ActiveForm::begin([
		'id' => 'ad-entity-info-form',
		'options' => ['class' => 'form-horizontal'],
		'enableClientValidation' => true,
		'enableAjaxValidation' => true,
		'validationUrl' => Url::toRoute(['validate-entity-form']),
		'fieldConfig' => [
			'template' => "<div class='col-xs-3 col-sm-2 text-right'>{label}</div><div class='col-xs-9 col-sm-7'>{input}{error}</div>",
			'labelOptions' => ['class' => 'col-lg-l control-label'],
		],	
	]); ?>
		

	<input type="hidden" id="entity_scenario" name="entity_scenario" value="<?php echo $model->scenario ?>">
	<?php if($model->scenario == 'create'): ?>
		<?= $form->field($model, 'name_zh')->textInput(['style' => 'width:300px;', 'placeholder' => '多盟']) ?>
		<?= $form->field($model, 'name_en')->textInput(['style' => 'width:300px;', 'placeholder' => 'domob']) ?>
		<?= $form->field($model, 'address_zh')->textInput(['style' => 'width:300px;', 'placeholder' => '北京市朝阳区酒仙桥北路九号恒通国际创新园']) ?>
		<div class="form-group field-entitymodel-address_zh required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-full-name">公司英文文地址</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<?= Html::input('text', 'EntityModel[full_name]', '', ['id' => 'entitymodel-full_name', 'class' => 'form-control', 'style' => 'width:300px;', 'placeholder' => 'Full Name']); ?>
				<?= Html::input('text', 'EntityModel[address_line_1]', '', ['id' => 'entitymodel-address_line_1', 'class' => 'form-control', 'style' => 'width:300px;', 'placeholder' => 'Address line 1']); ?>
				<?= Html::input('text', 'EntityModel[address_line_2]', '', ['id' => 'entitymodel-address_line_2', 'class' => 'form-control', 'style' => 'width:300px;', 'placeholder' => 'Address line 2']); ?>
				<?= Html::input('text', 'EntityModel[city]', '', ['id' => 'entitymodel-city', 'class' => 'form-control', 'style' => 'width:200px;', 'placeholder' => 'city']); ?>
				<?= Html::input('text', 'EntityModel[state]', '', ['id' => 'entitymodel-state', 'class' => 'form-control', 'style' => 'width:200px;', 'placeholder' => 'state']); ?>
				<?= Html::input('text', 'EntityModel[zip]', '', ['id' => 'entitymodel-zip', 'class' => 'form-control', 'style' => 'width:200px;', 'placeholder' => 'zip']); ?>
				<?= Html::input('text', 'EntityModel[country]', 'CN', ['id' => 'entitymodel-country', 'class' => 'form-control', 'style' => 'width:200px;', 'readonly' => 'true', 'placeholder' => 'country']); ?>
				<?= Html::hiddenInput('EntityModel[address_en]', "", ['id' => 'entitymodel-address_en']); ?>
			</div>
		</div>
		<?= $form->field($model, 'official_website_url')->textInput(['style' => 'width:300px;', 'placeholder' => 'http://www.domob.cn']) ?>
		<?= $form->field($model, 'payname')->textInput(['style' => 'width:300px;', 'placeholder' => '蓝色光标传播集团']) ?>
		<div class="form-group field-entitymodel-contact required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-contact">联系人</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="text" id="entitymodel-contact" class="form-control" name="EntityModel[contact]" style="width:300px;" placeholder="sales@example.com">
				<span class="label label-info">账户申请有任何状态更新，系统会自动往联系人邮箱发送邮件!</span>
				<div class="help-block"></div>
			</div>
		</div>
		<?= $form->field($model, 'vertical')->dropDownList(FbVertical::getVerticals(), ['prompt' => '请选择业务类型', 'style' => "width:300px;"]); ?>
		<?= $form->field($model, 'subvertical')->dropDownList([], ['prompt' => '请选择业务类型', 'style' => "width:300px;"]); ?>
		<div class="form-group field-entitymodel-is_smb required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label">是否SMB</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="hidden" name="EntityModel[is_smb]" value="">
				<div id="entitymodel-is_smb" style="width:300px;">
					<label><input type="radio" name="EntityModel[is_smb]" value="1"> 是</label>&nbsp;&nbsp;&nbsp;
					<label><input type="radio" name="EntityModel[is_smb]" value="0" checked=""> 否</label>
				</div>
				<span class="label label-info">仅有业务类型为非GAMING情况下，才会有SMB属性！</span>
				<div class="help-block"></div>
			</div>
		</div>

		<?= $form->field($model, 'business_registration_id')->textInput(['style' => 'width:300px;', 'placeholder' => '500381000035622']) ?>
		<div class="form-group field-entitymodel-business_registration required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-business_registration">公司营业执照</label>
			</div>
			<div class="col-xs-9 col-sm-7" style="width:330px;">
				<input id="entitymodel-business_registration_path" name="EntityModel[business_registration_path]" type="hidden">
				<input id="entitymodel-business_registration" name="EntityModel[business_registration]" type="file" class="file" multiple data-show-upload="false" data-show-caption="true">
			</div>
		</div>
		<?= $form->field($model, 'advertiser_business_id')->textInput(['style' => 'width:300px;', 'placeholder' => '976797449063408']) ?>
		<div class="form-group field-entitymodel-promotable_app_ids">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-promotable_app_ids">推广App Ids</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="text" id="entitymodel-promotable_app_ids" class="form-control" name="EntityModel[promotable_app_ids]" style="width:300px;" placeholder="976797449063408">
				<span class="label label-info">如推广授权APP，请务必填写App id，推广App Ids和推广链接至少选一项填写！</span>
				<div class="help-block"></div>
			</div>
		</div>

		<?= $form->field($model, 'promotable_page_ids')->textInput(['style' => 'width:300px;', 'placeholder' => '976797449063408']) ?>
		<?= $form->field($model, 'promotable_page_urls')->textInput(['style' => 'width:500px;', 'placeholder' => 'https://www.facebook.com/memoryanalysis2/']) ?>
		<?= $form->field($model, 'promotable_url')->textInput(['style' => 'width:500px;', 'placeholder' => 'http://www.domob.cn']) ?>
		<?= Html::button('<span style="size:2px;"> + </span>', ['class'=>'', 'style'=>'margin:0 0 3px 195px;width:60px;', 'id'=>'addwebsite']);?>
		<?= $form->field($model, 'comment')->textArea(['rows' => '3', 'style' => 'width:500px;']) ?>
	<?php elseif($model->scenario == 'update'): ?>
		<!-- 提交之前，所有信息都可以更新 -->
		<?= $form->field($model, 'name_zh')->textInput(['style' => 'width:300px;', 'placeholder' => '多盟']) ?>
		<?= $form->field($model, 'name_en')->textInput(['style' => 'width:300px;', 'placeholder' => 'domob']) ?>
		<?= $form->field($model, 'address_zh')->textInput(['style' => 'width:300px;', 'placeholder' => '北京市朝阳区酒仙桥北路九号恒通国际创新园']) ?>
		<div class="form-group field-entitymodel-address_zh required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-full-name">公司英文文地址</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<?= Html::input('text', 'EntityModel[full_name]', '', ['id' => 'entitymodel-full_name', 'class' => 'form-control', 'style' => 'width:300px;', 'placeholder' => 'Full Name']); ?>
				<?= Html::input('text', 'EntityModel[address_line_1]', '', ['id' => 'entitymodel-address_line_1', 'class' => 'form-control', 'style' => 'width:300px;', 'placeholder' => 'Address line 1']); ?>
				<?= Html::input('text', 'EntityModel[address_line_2]', '', ['id' => 'entitymodel-address_line_2', 'class' => 'form-control', 'style' => 'width:300px;', 'placeholder' => 'Address line 2']); ?>
				<?= Html::input('text', 'EntityModel[city]', '', ['id' => 'entitymodel-city', 'class' => 'form-control', 'style' => 'width:200px;', 'placeholder' => 'city']); ?>
				<?= Html::input('text', 'EntityModel[state]', '', ['id' => 'entitymodel-state', 'class' => 'form-control', 'style' => 'width:200px;', 'placeholder' => 'state']); ?>
				<?= Html::input('text', 'EntityModel[zip]', '', ['id' => 'entitymodel-zip', 'class' => 'form-control', 'style' => 'width:200px;', 'placeholder' => 'zip']); ?>
				<?= Html::input('text', 'EntityModel[country]', 'CN', ['id' => 'entitymodel-country', 'class' => 'form-control', 'style' => 'width:200px;', 'readonly' => 'true', 'placeholder' => 'country']); ?>
				<?= Html::hiddenInput('EntityModel[address_en]', $model->address_en, ['id' => 'entitymodel-address_en']); ?>
			</div>
		</div>

		<?= $form->field($model, 'official_website_url')->textInput(['style' => 'width:300px;', 'placeholder' => 'http://www.domob.cn']) ?>
		<?= $form->field($model, 'payname')->textInput(['style' => 'width:300px;', 'placeholder' => '蓝色光标传播集团']) ?>
		<div class="form-group field-entitymodel-contact required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-contact">联系人</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input type="text" id="entitymodel-contact" class="form-control" name="EntityModel[contact]" style="width:300px;" value="<?= $model->contact; ?>" placeholder="sales@example.com">
				<span class="label label-info">账户申请有任何状态更新，系统会自动往联系人邮箱发送邮件!</span>
				<div class="help-block"></div>
			</div>
		</div>
		<?= $form->field($model, 'vertical')->dropDownList(FbVertical::getVerticals(), ['prompt' => '请选择业务类型', 'style' => "width:300px;"]); ?>
		<?= $form->field($model, 'subvertical')->dropDownList(FbVertical::getSubVerticalsByIndex($model->vertical), ['prompt' => '请选择业务类型', 'style' => "width:300px;"]); ?>
		<div class="form-group field-entitymodel-is_smb required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label">是否SMB</label>
			</div>
			<div class="col-xs-9 col-sm-7">
				<input id="entitymodel-is_smb-value" type="hidden" name="EntityModel[is_smb]" value="<?= $model->is_smb; ?>">
				<div id="entitymodel-is_smb" style="width:300px;">
					<label><input type="radio" name="EntityModel[is_smb]" value="1"> 是</label>&nbsp;&nbsp;&nbsp;
					<label><input type="radio" name="EntityModel[is_smb]" value="0"> 否</label>
				</div>
				<span class="label label-info">仅有业务类型为非GAMING情况下，才会有SMB属性！</span>
				<div class="help-block"></div>
			</div>
		</div>

		<?= $form->field($model, 'business_registration_id')->textInput(['style' => 'width:300px;', 'placeholder' => '500381000035622']) ?>
		<div class="form-group field-entitymodel-business_registration required">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-business_registration">公司营业执照</label>
			</div>
			<div class="col-xs-9 col-sm-7" style="width:330px;">
				<input id="entitymodel-business_registration_path" name="EntityModel[business_registration_path]" type="hidden" value="<?= $model->business_registration; ?>">
				<input id="entitymodel-business_registration" name="EntityModel[business_registration]" type="file" class="file" multiple data-show-upload="false" data-show-caption="true">
			</div>
		</div>

		<?= $form->field($model, 'advertiser_business_id')->textInput(['style' => 'width:300px;', 'placeholder' => '976797449063408']) ?>
		<div class="form-group field-entitymodel-promotable_app_ids">
			<div class="col-xs-3 col-sm-2 text-right">
				<label class="col-lg-l control-label" for="entitymodel-promotable_app_ids">推广App Ids</label>
			</div>
			<div class="col-xs-9 col-sm-7">
			<input type="text" id="entitymodel-promotable_app_ids" class="form-control" name="EntityModel[promotable_app_ids]" style="width:300px;" value="<?= $model->promotable_app_ids; ?>" placeholder="976797449063408">
				<span class="label label-info">如推广授权APP，请务必填写App id，推广App Ids和推广链接至少选一项填写！</span>
				<div class="help-block"></div>
			</div>
		</div>


		<?= $form->field($model, 'promotable_page_ids')->textInput(['style' => 'width:300px;', 'placeholder' => '976797449063408']) ?>
		<?= $form->field($model, 'promotable_page_urls')->textInput(['style' => 'width:500px;', 'placeholder' => 'https://www.facebook.com/memoryanalysis2/']) ?>
		<!-- 提交之后，只能更新website -->
		<?= $form->field($model, 'promotable_url')->textInput(['style' => 'width:500px;']) ?>
		<?php 
			if(is_array(array_unique($model->promotable_urls)))
			{
				foreach(array_unique($model->promotable_urls) as $promotable_url)
				{
					echo '<div>
							<button style="width:60px;float:right;margin-right:360px;margin-top:5px;" id="deletewebsite"> - </button>
							<div style="margin:0px 5px 5px 195px;"><input type="text" id="adentitymodel-promotable_url" class="form-control" name="EntityModel[promotable_urls][]" value="'.$promotable_url.'" style="width:500px;"></div>
						</div>';
				}
			}
		?>
		<?= Html::button('<span style="size:2px;"> + </span>', ['class'=>'', 'style'=>'margin:0 0 3px 195px;width:60px;', 'id'=>'addwebsite']);?>
		<?= $form->field($model, 'comment')->textArea(['rows' => '3', 'style' => 'width:500px;']) ?>
	<?php endif; ?>
    <?php ActiveForm::end(); ?>
</div>
