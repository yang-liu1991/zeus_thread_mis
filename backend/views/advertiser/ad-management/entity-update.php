<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\record\AdAccountInfo */

$this->title = '公司主体信息变更';
$this->params['breadcrumbs'][] = $this->title;;
?>
<div class="ad-account-info-update">

    <legend><?= Html::encode($this->title) ?></legend>

	<?php
		foreach (Yii::$app->session->getAllFlashes() as $key => $info) 
		{
			if(Yii::$app->session->hasFlash('entity-update-error')) 
			{
				$errorMessage = '| ';
				foreach($info as $error) {
					$errorInfo = implode("|", $error);
					$errorMessage .= $errorInfo.'|';
				}
				$message = '实体信息更新失败，原因：'.$errorMessage;
				echo '<div class="alert alert-danger">' . $message . '</div>';
			}
		}
	?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
	
	<div class="form-group" style="margin-top:10px;margin-left:30px;">
		<?= Html::submitButton('更新', ['class'=>'btn btn-primary','name' =>'submit-button', 'id' => 'submit-button']) ?>
		<?= Html::resetButton('重置', ['class'=>'btn','name' =>'reset-button', 'id' => 'reset-button']) ?>
    </div>

</div>
