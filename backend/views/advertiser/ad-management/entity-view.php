<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-07-27 13:37:48
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */

$this->title = '公司主体信息详情';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ad-account-info-view">

    <legend><?= Html::encode($this->title) ?></legend>
	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) 
		{
			if(Yii::$app->session->hasFlash('entity-submit-success'))
			{
				$message = '注册信息提交成功，等待审核！';
				echo '<div class="alert alert-success">' . $message . '</div>';break;
			}
			if(Yii::$app->session->hasFlash('entity-audit-waiting'))
			{
				$message = '注册信息已提交，请耐心等待审核!';
				echo  '<div class="alert alert-info">' . $message . '</div>';break;
			}
			if(Yii::$app->session->hasFlash('entity-existing'))
			{
				$message = '实体信息已经存在!';
				echo '<div class="alert alert-info">' . $message . '</div>';break;
			}
		}
	?>

	<?= $this->render('_view', [
		'model' => $model]) 
	?>
</div>
