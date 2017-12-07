<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\assets\AppAsset;
use backend\models\record\ThEntityInfoSearch;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
AppAsset::register($this);
$this->registerCssFile('@web/js/jquery-jsonview/jquery.jsonview.min.css');
$this->registerJsFile('@web/js/jquery-jsonview/jquery.jsonview.min.js');
$this->registerJsFile('@web/js/request/layer.js');
$this->registerCssFile('@web/css/request/main.css');
$this->registerJsFile('@web/js/request/account.js');
$this->title = 'FaceBook广告帐户申请';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ad-account-info-create">

    <legend><?= Html::encode($this->title) ?></legend>

	<!-- 这里是需要更新的实体信息 -->
	<?= $this->render('_form', ['model' => $entityModel]) ?>

	<!-- 这里是需要提交的开户信息-->
	<div class="form-border">
		<form id="ad-account-info-form" class="form-horizontal" action="/advertiser/account-apply-add?entity-id=<?= $entityId; ?>" method="post">
			<input type="hidden" id="requestmodel-fbaccount_entity_id" value="<?= $entityId; ?>">
			<?php $errors = $model->getErrors(); ?>
			<div id="ad-account-info">
			<div class="form-group field-requestmodel-referral required">
				<div class="col-xs-3 col-sm-2 text-right">
					<label class="col-lg-l control-label" for="requestmodel-referral">推荐人</label>
				</div>
				<div class="col-xs-9 col-sm-7">
				<input type="text" id="requestmodel-referral" class="form-control requestmodel" name="RequestModel[0][referral]" style="width:250px;" placeholder="wangwu@example.com" value="<?= $model->referral; ?>">
					<?php if(array_key_exists('referral', $errors)): ?>
                        <span class="has-error" id="requestmodel-referral_er">无效的推荐人</span>
                    <?php endif; ?>	
				</div>
			</div>
			<div class="form-group field-requestmodel-timezone_id required">
				<div class="col-xs-3 col-sm-2 text-right">
					<label class="col-lg-l control-label" for="requestmodel-timezone_id">时区选择</label>
				</div>
				<div class="col-xs-9 col-sm-7">
				<select id="requestmodel-timezone_id" class="form-control requestmodel" name="RequestModel[0][timezone_id]" style="width:250px;">
				<?php 
					$option = '<option value="">请选择时区</option>';
					foreach($model->getFbTimezoneIds() as $key => $value) $option .= '<option value="' . $key . '">' . $value . '</option>';
					echo $option;
				?>
				</select>
				<?php if(array_key_exists('timezone_id', $errors)): ?>
                        <span class="has-error" id="requestmodel-timezone_id_er">无效的时区</span>
                    <?php endif; ?>
				</div>
			</div>
			<div class="form-group field-requestmodel-number required">
				<div class="col-xs-3 col-sm-2 text-right">
					<label class="col-lg-l control-label" for="requestmodel-number">开户数量</label>
				</div>
				<div class="col-xs-9 col-sm-7">
				<input type="text" id="requestmodel-number" class="form-control requestmodel" name="RequestModel[0][number]" style="width:100px;" placeholder="1" value="<?= $model->number; ?>">
					<?php if(array_key_exists('number', $errors)): ?>
						<span class="has-error" id="requestmodel-number">无效的开户量</span>
					<?php endif; ?>
				</div>
			</div>
			<hr/ style="width:85%;">
			</div>
		</form>	

		</div>
		<!-- 以下为增加website的内容 -->
		<div class="form-group account-button">
			<?= Html::Button('提交', ['class'=>'btn btn-primary','name' =>'submit-button', 'id' => 'account-submit-button']) ?>
			<?= Html::Button('增加', ['class'=>'btn btn-success','name' =>'submit-button', 'id' => 'account-add-button']) ?>
		</div>
</div>
