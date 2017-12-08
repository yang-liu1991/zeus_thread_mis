<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-14 18:10:07
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->registerJsFile('@web/js/plugin/jquery.md5.js?v=' . D3_VERSION);
$this->registerJsFile('@web/js/site/password.js');

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\ResetPassword */

$this->title = '重置密码';
$this->params['breadcrumbs'][] = $this->title;
?>
<legend><?= Html::encode($this->title) ?></legend>
<div class="site-reset-password">

    <p>Please choose your new password:</p>

    <div class="row">
        <div class="col-lg-5">
			<?php $form = ActiveForm::begin([
				'id' => 'reset-password-form',
				'options' => ['class' => 'form-horizontal'],
				'fieldConfig' => [
					'template' => "<div class='col-xs-3 col-sm-4 text-right'>{label}</div><div class='col-xs-9 col-sm-7'>{input}{error}</div>",
					'labelOptions' => ['class' => 'col-lg-l control-label'],
				]	
			]); ?>
            
			<?= $form->field($model, 'password')->passwordInput() ?>
            <div class="form-group">
                <?= Html::button(Yii::t('rbac-admin', '保存'), ['class' => 'btn btn-primary', 'id' => 'reset-password']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
