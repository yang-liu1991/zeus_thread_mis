<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */

$this->title = 'Create Role';
$this->params['breadcrumbs'][] = ['label' => 'Role', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Role';
?>
<div class="role-create">
    <legend><?= Html::encode($this->title) ?></legend>
    <?= $this->render('_form', [
        'model' => $model,
        'allPermissions' => $allPermissions,
    ]) ?>

</div>
