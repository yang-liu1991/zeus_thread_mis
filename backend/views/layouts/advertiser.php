<?php

use kartik\nav\NavX;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\AppAsset;
use backend\assets\Alert;
use backend\models\account\EntityModel;

/* @var $this \yii\web\View */
/* @var $content string */
Yii::$app->homeUrl	= '/advertiser/entity-list';

$adaccountMenuItems		= [];
$fbaccountMenuItems		= [];
$userMenuItems			= [];

$id = null;	
$user_id = !empty(Yii::$app->user->identity->id) ? Yii::$app->user->identity->id : null;
if($user_id)
{
	$adEntityModel = EntityModel::findWhere(['user_id' => $user_id]);
	if($adEntityModel) 
	{
		$id = $adEntityModel->id;
	}
}

/* 广告主帐号管理 */
$adaccountMenuItems = [
	'label' => '广告主帐户管理',
	'items' => [
		['label' => '主体信息注册', 'url' => ['/advertiser/entity-create'], 'linkOptions' => ['style' => 'line-height:1.2;']],
		'<li class="divider"></li>',
		['label' => '主体信息列表', 'url' => ['/advertiser/entity-list'], 'linkOptions' => ['style' => 'line-height:1.2;']],
	]
	];

/* Facebook帐号管理 */
$fbaccountMenuItems = [
		'label' => 'Facebook帐户管理',
		'items' => [
			['label' => '帐户申请管理', 'items' => [
				['label' => '帐户申请', 'url' => ['/advertiser/account-apply']],
				['label' => '帐户列表', 'url' => ['/advertiser/account-list']],
			], 'linkOptions' => ['style' => 'line-height:1.2;']],
			'<li class="divider"></li>',
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
    $userMenuItems[] = [
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
                'brandLabel' => '广告主系统',
                'brandUrl' => Yii::$app->homeUrl,
				'innerContainerOptions' => [
                    'class' => 'container-fluid',
				],
                'options' => [
                    'class' => 'navbar-default',
                ],
            ]);

			/* 用户 */
			echo Nav::widget([
				'options' => ['class' => 'navbar-nav navbar-right'],
				'items' => $userMenuItems,
				]);
	
	
		echo NavX::widget([
				'options'=>['class'=>'navbar-nav'],
				'items' => [
					$adaccountMenuItems,
					$fbaccountMenuItems,
					]
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

       <?= $content ?>
        </div>
    </div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
