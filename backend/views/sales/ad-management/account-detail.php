<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-07-27 13:37:48
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */
$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/jquery.validate.js');
$this->registerJsFile('@web/js/request/audit.js');

$this->title = '帐户信息详情';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ad-account-info-view">

    <legend><?= Html::encode($this->title) ?></legend>
	<?= $this->render('_detailview', [
		'model' => $model]) 
	?>

	<!-- 拜托，只有管理员才有权限操作，你个小鬼就不要试了! ^~~^ -->
	<?php if(Yii::$app->user->can('admin_group')): ?>
	<div class="form-group" style="margin-right:50px;float:right;">
		<?= Html::button('拒绝', ['class'=>'btn btn-danger', 'name' =>'submit-button', 'id' => 'audit_refuse_detail']) ?>
		<?= Html::button('通过', ['class'=>'btn btn-primary', 'name' =>'submit-button', 'id' => 'audit_accept_detail']) ?>
	</div>
	<?php endif; ?>
</div>
<!-- 以下为审核信息表单 -->
<div id="audit_message" style="display:none;margin:20px;auto;">
	<form id="audit_accept_form" method="post" action="<?php Yii::$app->urlManager->createUrl(['entity/audit']); ?>">
		<input type="hidden" id="audit_status" name="auditStatus">
		<input type="hidden" id="entity_id" name="entityId" value="<?php echo $model->id ?>">
		<table>
			<tr>
				<td><span class="star">审核意见：</span></td>
				<td><textarea id="audit_text" name="auditText" class="form-contorl" style="width:450px;height:300px;"></textarea></td>
			</tr>
		</table>
	</form>
</div>
