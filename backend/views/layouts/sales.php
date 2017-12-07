<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\AppAsset;
use backend\assets\Alert;

/* @var $this \yii\web\View */
/* @var $content string */

$reportMenuItems		= [];
$adaccountMenuItems		= [];
$fbaccountMenuItems		= [];
$userMenuItems			= [];


/* 广告主帐号管理 */
$adaccountMenuItems[] = [
    'label' => '广告主帐户管理',
	    'items' => [
	    ['label' => '新公司主体审核', 'url' => ['/sales/entity-list']],
	    ['label' => '帐号信息异常查看', 'url' => ['/sales/account-abnormal', 'status' => 7]],
	    ]
	];

// 用户昵称显示
if (!Yii::$app->user->isGuest) {
    $userMenuItems[] = [
	    'label' => Yii::$app->user->identity->email.'  ',
	    'items' => [
		['label' => 'Logout', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']],
	    ],
	];
}

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title. ' - ' .Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => '蓝瀚CRM',
                'brandUrl' => Yii::$app->homeUrl,
				'innerContainerOptions' => [
                    'class' => 'container-fluid',
				],
                'options' => [
                    'class' => 'navbar-default',
                ],
            ]);


			/* 广告主帐户管理 */
			echo Nav::widget([
				'options' => ['class' => 'navbar-nav'],
				'items' => $adaccountMenuItems,
				]);
	
			/* 用户 */
			echo Nav::widget([
				'options' => ['class' => 'navbar-nav navbar-right'],
				'items' => $userMenuItems,
				]);

			NavBar::end();
        ?>
        <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
        <p class="pull-left">&copy; <?= sprintf('%s %s', Yii::$app->name, date('Y')) ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
