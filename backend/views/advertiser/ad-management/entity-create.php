<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */

$this->title = '实体信息注册';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ad-account-info-create">

    <legend><?= Html::encode($this->title) ?></legend>


	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) {
			if(Yii::$app->session->hasFlash('account-success'))
			{
				$message = '帐户注册成功，请注册实体信息！';
				echo '<div class="alert alert-success">' . $message . '</div>';
			} elseif(Yii::$app->session->hasFlash('entity-not-found')) {
				$message = '请先注册实体信息再进行其他操作！';
				echo '<div class="alert alert-info">' . $message . '</div>';
			} elseif(Yii::$app->session->hasFlash('entity-create-error')) {
				$errorMessage = '| ';
				foreach($info as $error) {
					$errorInfo = implode("|", $error);
					$errorMessage .= $errorInfo.'|';
				}
				$message = '实体信息注册失败，原因：'.$errorMessage;
				echo '<div class="alert alert-danger">' . $message . '</div>';
			}
		}
	?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
	
	<div class="form-group" style="margin-top:10px;margin-left:30px;">
		<?= Html::submitButton('保存', ['class'=>'btn btn-primary','name' =>'submit-button', 'id' => 'submit-button']) ?>
		<?= Html::resetButton('重置', ['class'=>'btn','name' =>'reset-button', 'id' => 'reset-button']) ?>
    </div>
</div>
