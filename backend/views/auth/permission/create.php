<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */

$this->title = 'Create Permission';
$this->params['breadcrumbs'][] = ['label' => 'Permission', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Create';
?>
<div class="permission-create">
    <legend><?= Html::encode($this->title) ?></legend>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
