<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\record\ThAgencyBusiness */

$this->title = 'Create Agency Businesses';
$this->params['breadcrumbs'][] = ['label' => 'Agency Businesses List', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="th-agency-business-create">

    <legend><?= Html::encode($this->title) ?></legend>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
