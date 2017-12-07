<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-08-16 14:45:12
 */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use common\models\Conversion;
use backend\models\record\ThEntityInfo;
use backend\models\record\ThAdCreativesSearch;

$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/creatives.js');
?>

<style>
.item-view {
	margin:10px;
	padding-left:5px;
	text-align: left;
	line-height:15px;
}

.image-view {
	min-height:200px;
	width:340px;
}

.item-view-form {
	width:340px;
	height:700px;
	overflow:auto;
	display:flex;
	border: 1px solid #dcdcdc;
	flex-direction: column;
	justify-content: space-between;
	border-radius: 4px;
	margin:5px 30px 10px 0px;
}

</style>
<?php 
	$status = [
		1 => sprintf('<span class="btn btn-xs btn-warning">%s</span>', '等待审核'),
		2 => sprintf('<span class="btn btn-xs btn-success">%s</span>', '审核通过'),
		3 => sprintf('<span class="btn btn-xs btn-danger">%s</span>', '审核失败'),
	];

	if($model->app_details)
	{
		$app_details_obj = json_decode($model->app_details);
	} else {
		$app_details_obj = (object)[];
	}
?>

<div class="item-view-form">
	<div class="item-view">
		<input id="item-view-accountid" type="hidden" value="<?= $model->account_id ?>" />
		<?= sprintf('广告帐户ID：%s', $model->account_id) ?>
	</div>
	<div class="item-view">
		<input id="item-view-adid" type="hidden" value="<?= $model->ad_id ?>" />
		<?= sprintf('广告AD ID：%s', $model->ad_id) ?>
	</div>
	<div class="item-view">
		<input id="item-view-admessage" type="hidden" value="<?= $model->ad_message ?>" />
		<?= sprintf('广告语：%s', hex2bin($model->ad_message)) ?>
	</div>
	<div class="item-view">
		<input id="item-view-promotedurl" type="hidden" value="<?= $model->promoted_url ?>" />
		<?= sprintf('推广链接: <a target="_blank" href="%s">%s</a>', $model->promoted_url, $model->promoted_url) ?>
	</div>
	<div class="item-view">
		<?= "审核状态:"?>
		<?= $status[$model->audit_status]; ?>
	</div>
	<div class="item-view">
		<?= "审核信息:"?>
		<?= !empty($model->audit_message) ? $model->audit_message : '无'; ?>
	</div>

	<div class="item-view">
		<?= "产品名称:" ?>
		<?= property_exists($app_details_obj, 'app_name') ? $app_details_obj->app_name : ''?>
	</div>

	<div class="item-view">
		<?= "业务类型:" ?>
		<?php
			$company_category = '';
			if(property_exists($app_details_obj, 'category'))
			{
				$category = $app_details_obj->category;
				if(property_exists($category, 'primary_category')) $company_category = $category->primary_category.' ';
				if(property_exists($category, 'subtitle_category')) $company_category .= $category->subtitle_category;
			}
			echo $company_category;
		?>
	</div>
	<div class="item-view">
		<?= "开发者信息:" ?>
		<?= property_exists($app_details_obj, 'developer') ? $app_details_obj->developer : ''?>
	</div>
	<div class="item-view">
		<?= "安装次数:" ?>
		<?= property_exists($app_details_obj, 'install_number') ? $app_details_obj->install_number : ''?>
	</div>
	<div class="item-view">
		<?= "更新日期:" ?>
		<?= property_exists($app_details_obj, 'update_time') ? $app_details_obj->update_time : ''?>
	</div>
	<div class="item-view">
		<?= "应用内商品价格:" ?>
		<?= property_exists($app_details_obj, 'purchases') ? $app_details_obj->purchases : ''?>
	</div>

	<!-- 拜托，只有管理员才有权限操作，你个小鬼就不要试了! ^~~^ -->
	<?php if(Yii::$app->user->can('admin_group')): ?>
	<div class="form-group item-view">
		<?= '操作:' ?>
		<?php if($model->audit_status != ThAdCreativesSearch::CREATIVES_STATUS_FAILED): ?>
			<?= Html::button('拒绝', ['class'=>'btn btn-danger btn-xs', 'name' =>'submit-button', 'id' => 'audit_refuse_detail']) ?>
		<?php endif; ?>
		<?php if($model->audit_status != ThAdCreativesSearch::CREATIVES_STATUS_SUCCESS): ?>
			<?= Html::button('通过', ['class'=>'btn btn-primary btn-xs', 'name' =>'submit-button', 'id' => 'audit_accept_detail']) ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="image-view ">
		<?=  Html::a(Html::img($model->image_url, ['style'=>'max-width:320px;margin:20px 0 5px 0;']),
			['admin-manager/entity-view', 'id' => !empty($model->accountInfo->entity_id) ? $model->accountInfo->entity_id : '']) ?>
	</div>

</div>

<!-- 以下为审核信息表单 -->
<div id="audit_message" style="display:none;margin:20px;auto;">
	<form id="audit_accept_form" method="post" action="">
		<input type="hidden" id="audit_status" name="auditStatus">
		<input type="hidden" id="entity_id" name="entityId" value="">
		<table>
			<tr>
				<td><span class="star">审核意见：</span></td>
				<td><textarea id="audit_text" name="auditText" class="form-contorl" style="width:450px;height:300px;"></textarea></td>
			</tr>
		</table>
	</form>
</div>


