<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\record\ThAgencyBusiness */

$this->title = 'View Agency Businesses';
$this->params['breadcrumbs'][] = ['label' => 'Agency Businesses List', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
    foreach (Yii::$app->session->getAllFlashes() as $key => $info)
    {
        if(Yii::$app->session->hasFlash('agency-create-success'))
        {
            $message = 'Agency Businesses 添加成功!';
            echo '<div class="alert alert-success">' . $message . '</div>';
        } else if(Yii::$app->session->hasFlash('agency-update-success')) {
            $message = 'Agency Businesses 更新成功!';
            echo '<div class="alert alert-success">' . $message . '</div>';
        }
    }
?>
<div class="th-agency-business-view">

    <legend><?= Html::encode($this->title) ?></legend>

    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class'=>'table table-striped table-bordered table-condensed detail-view', 'style'=>'table-layout:fixed;word-break:break-all;font-size:13px;font-family: "Microsoft YaHei" ! important;'],
        'template' => '<tr><th style="width:120px;">{label}</th><td>{value}</td></tr>',
        'attributes' => [
            'id',
            'business_id',
            'business_name',
            'company_id',
            'access_token',
            'referral',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
