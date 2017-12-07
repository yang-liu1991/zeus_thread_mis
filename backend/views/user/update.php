<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'Update User: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-update">

    <legend><?= Html::encode($this->title) ?></legend>
    <?= $this->render('_form', [
        'model' => $model,
        'allRbacRole' => $allRbacRole,
    ]) ?>

</div>
