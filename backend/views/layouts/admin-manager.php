<?php

use kartik\nav\NavX;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\AppAsset;
use backend\assets\Alert;
use backend\models\user\User;
use backend\models\record\ThMessage;
use backend\models\message\MessageModel;

/* @var $this \yii\web\View */
/* @var $content string */

Yii::$app->homeUrl	= '/admin-manager/entity-list';
$this->registerJsFile('@web/js/request/message-menu.js');

$reportMenuItems		= [];
$adaccountMenuItems		= [];
$fbaccountMenuItems		= [];
$userMenuItems			= [];

/* 广告主帐号管理 */
$adaccountMenuItems = [
	'label' => '广告主帐户管理',
	'innerContainerOptions' => ['class' => 'menu-top'],
	'items' => [
		[
			'label' => '新公司主体审核', 
			'url' => ['/admin-manager/entity-list'], 
			'linkOptions' => ['style' => 'line-height:1.2;']
		],
		'<li class="divider"></li>',
		[
			'label' => '帐号信息异常查看', 
			'url' => ['/admin-manager/abnormal-list', 'status' => 7], 
			'linkOptions' => ['style' => 'line-height:1.2;']
		],
		'<li class="divider"></li>',
		[
			'label' => 'Facebook广告帐户申请', 
			'url' => ['/admin-manager/refer-list'], 
			'linkOptions' => ['style' => 'line-height:1.2;']
		],
	],
];

/* Facebook帐号管理 */
$fbaccountMenuItems = [
	'label' => 'Facebook帐户管理',
	'items' => [
		['label' => '公司帐户对应', 'url' => ['/admin-manager/account-mapping'], 'linkOptions' => ['style' => 'line-height:1.2;']],
		'<li class="divider"></li>',
		['label' => '广告创意列表', 'url' => ['/admin-manager/creatives-view'], 'linkOptions' => ['style' => 'line-height:1.2;']],
		'<li class="divider"></li>',
		['label' => '帐户额度管理', 'items' => [
			['label' => '帐户额度变更', 'url' => ['/account-manager/spendcap-change']],
			['label' => '额度变更记录', 'url' => ['/account-manager/spendcap-list']],
		], 'linkOptions' => ['style' => 'line-height:1.2;']],
		'<li class="divider"></li>',
		['label' => '帐户绑定管理', 'items' => [
			['label' => '帐户关联变更', 'url' => ['/account-manager/binding-change']],
			['label' => '关联变更记录', 'url' => ['/account-manager/binding-list']],
		], 'linkOptions' => ['style' => 'line-height:1.2;']],
		'<li class="divider"></li>',
		['label' => '帐户名称管理', 'items' => [
			['label' => '帐户名称变更', 'url' => ['/account-manager/name-change']],
			['label' => '名称变更记录', 'url' => ['/account-manager/name-list']],
		]],
	]
];

/* 其他工具 */
$otherToolMenuItems = [
	'label' => '其他工具',
	'items' => [
		['label' => '邮件发送管理', 'items' => [
			['label' => '增加邮件模板', 'url' => ['/email-manager/create-email']],
			['label' => '邮件模板列表', 'url' => ['/email-manager/email-list']],
		], 'linkOptions' => ['style' => 'line-height:1.2;']],
	]
];
/* 所有用户与权限管理 */
$userMenuItems = [
	'label' => '帐号管理', 
	'items' => [
		['label' => '用户管理', 'items' => [
			['label' => '用户列表', 'url' => ['/user/index'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			['label' => '添加用户', 'url' => ['/user/create'], 'linkOptions' => ['style' => 'line-height:1.2;']]
		]],
		'<li class="divider"></li>',
		['label' => '角色管理', 'items' => [
			['label' => '角色列表', 'url' => ['/role/index'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			['label' => '添加角色', 'url' => ['/role/create'], 'linkOptions' => ['style' => 'line-height:1.2;']]
		]],
		'<li class="divider"></li>',
		['label' => '权限管理', 'items' => [
			['label' => '权限列表', 'url' => ['/permission/index'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			['label' => '添加权限', 'url' => ['/permission/create'], 'linkOptions' => ['style' => 'line-height:1.2;']]
		]],
		'<li class="divider"></li>',
		['label' => '代理管理', 'items' => [
			['label' => '代理列表', 'url' => ['/agency-manager/list'], 'linkOptions' => ['style' => 'line-height:1.2;']],
			['label' => '添加代理', 'url' => ['/agency-manager/create'], 'linkOptions' => ['style' => 'line-height:1.2;']]
		]],
	],
];

// 用户昵称显示
if (!Yii::$app->user->isGuest) {
    $loginMenuItems = [
	    'label' => Yii::$app->user->identity->email.'  ',
	    'items' => [
		['label' => 'MessageBox', 'url' => ['/message/list']],
		['label' => 'Logout', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']]
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
				'options' => ['class' => 'navbar-nav'],
				'items' => [$userMenuItems],
				]);
			
			/* 黑科技 */
			echo NavX::widget([
				'options' => ['class' => 'navbar-nav'],
				'items' => [$otherToolMenuItems],
				]);

			/* 登录 */
			echo NavX::widget([
				'options' => ['class' => 'navbar-nav navbar-right'],
				'items' => [$loginMenuItems],
				]);

			/* 消息提醒 */
			echo NavX::widget([
				'options' => ['class' => 'navbar-nav navbar-right'],
				'encodeLabels' => false,
				'items' => [
					[
						'options' => ['id' => 'message-menu'],
						'linkOptions' => ['style' => 'line-height:1.2;'],
						'label' => '<span style="background-color:#337ab7;" class="badge badge-info">'. $this->context->messageList->getTotalCount() .'</span> 消息 <span class="glyphicon glyphicon-envelope"></i>',
						//'items' => $messageIterms
						'items' => [
							[
								'label' => '<span>开户</span><span style="background-color: #00aa00" class="badge badge-success">'. MessageModel::getMessage(0)->getTotalCount() .'</span>',
								'url'	=> ['/message/list', 'type' => ThMessage::CREATE_ACCOUNT]
							],
							'<li class="divider"></li>',
							[
								'label' => '<span>额度</span><span style="background-color: #00aa00" class="badge badge-success">'. MessageModel::getMessage(1)->getTotalCount() .'</span>',
								'url'	=> ['/message/list', 'type' => ThMessage::CHANGE_CREDITLIMIT]
							],
							'<li class="divider"></li>',
							[
								'label' => '<span>关联</span><span style="background-color: #00aa00" class="badge badge-success">'. MessageModel::getMessage(2)->getTotalCount() .'</span>',
								'url'	=> ['/message/list', 'type' => ThMessage::CHANGE_BINDING]
							],
							'<li class="divider"></li>',
							[
								'label' => '<span>更名</span><span style="background-color: #00aa00" class="badge badge-success">'. MessageModel::getMessage(3)->getTotalCount() .'</span>',
								'url'	=> ['/message/list', 'type' => ThMessage::CHANGE_NAME]
							]
						],
					],
				],
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
