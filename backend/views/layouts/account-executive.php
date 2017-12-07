<?php

use kartik\nav\NavX;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\AppAsset;
use backend\assets\Alert;

/* @var $this \yii\web\View */
/* @var $content string */

Yii::$app->homeUrl	= '/account-executive/entity-list';

$reportMenuItems		= [];
$adaccountMenuItems		= [];
$fbaccountMenuItems		= [];
$userMenuItems			= [];


/* 广告主帐号管理 */
$adaccountMenuItems = [
    'label' => '广告主帐户管理',
	    'items' => [
			['label' => '新公司主体审核', 'url' => ['/account-executive/entity-list'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			'<li class="divider"></li>',
			['label' => '帐号信息异常查看', 'url' => ['/account-executive/account-abnormal', 'status' => 7], 'linkOptions' => ['style' => 'line-height:1.2;']],
			'<li class="divider"></li>',
			['label' => '广告帐户申请', 'url' => ['/account-executive/refer-list'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			'<li class="divider"></li>',
			['label' => '帐户付款信息匹配', 'url' => ['/payment-manager/pay-list'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			'<li class="divider"></li>',
			['label' => '广告主金额管理', 'items' => [
				['label' => '广告主金额查看', 'url' => ['/payment-manager/company-amount-list'], 'linkOptions' => ['style' => 'line-height:1.2;']],
				['label' => '广告主分天数据查看', 'url' => ['/payment-manager/days-amount-list'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			],
			]
		]
	];

/* Facebook帐号管理 */
$fbaccountMenuItems = [
	'label' => 'Facebook帐户管理',
	'items' => [
		['label' => '帐户额度管理', 'items' => [
			['label' => '帐户额度变更', 'url' => ['/account-manager/spendcap-change']],
				['label' => '额度变更记录', 'url' => ['/account-manager/spendcap-list']],
		]],
		'<li class="divider"></li>',
		['label' => '帐户关联管理', 'items' => [
			['label' => '帐户关联变更', 'url' => ['/account-manager/binding-change']],
			['label' => '关联变更记录', 'url' => ['/account-manager/binding-list']],
		]],
		'<li class="divider"></li>',
		['label' => '帐户名称管理', 'items' => [
			['label' => '帐户名称变更', 'url' => ['/account-manager/name-change']],
			['label' => '名称变更记录', 'url' => ['/account-manager/name-list']],
		]],
	]
];

// 用户昵称显示
if (!Yii::$app->user->isGuest) {
    $userMenuItems = [
	    'label' => Yii::$app->user->identity->email.'  ',
	    'items' => [
		['label' => 'Setting', 'url' => ['/site/change-password'], 'linkOptions' => ['style' => 'line-height:1.2;']],
		'<li class="divider"></li>',
		['label' => 'Logout', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post', 'style' => 'line-height:1.2;']],
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
			echo NavX::widget([
				'options' => ['class' => 'navbar-nav'],
				'items' => [
					$adaccountMenuItems,
					$fbaccountMenuItems,	
				],
				]);
	
			/* 用户 */
			echo NavX::widget([
				'options' => ['class' => 'navbar-nav navbar-right'],
				'items' => [$userMenuItems],
				]);

			NavBar::end();
        ?>
        <div class="container">
        <?= Breadcrumbs::widget([
			'homeLink' => [
				'label' => Yii::t('yii', '首页'),
				'url'	=> Yii::$app->homeUrl,
			],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
        </div>
    </div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
