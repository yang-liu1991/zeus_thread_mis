<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\assets\AppAsset;
use backend\assets\Alert;
use backend\models\account\EntityModel;

/* @var $this \yii\web\View */
/* @var $content string */
$this->title = '广告主管理系统';

$adaccountMenuItems		= [];
$fbaccountMenuItems		= [];
$userMenuItems			= [];

#$getQueryParams = Yii::$app->request->getQueryParams();
#$id = !empty($getQueryParams['id']) ? $getQueryParams['id'] : '';

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

if (Yii::$app->user->can('ad_group'))
{
	
	/* 广告主帐号管理 */
    $adaccountMenuItems[] = [
	    'label' => '广告主帐户管理',
	    'items' => [
	    ['label' => '主体信息注册', 'url' => ['/entity/create']],
	    ['label' => '主体信息变更', 'url' => ['/entity/update', 'id' => $id]],
	    ['label' => '主体信息查看', 'url' => ['/entity/view', 'id' => $id]],
	    ]
	];
	
	/* Facebook帐号管理 */
	$fbaccountMenuItems[] = [
	    'label' => 'Facebook帐户管理',
	    'items' => [
	    ['label' => 'Facebook广告帐户申请', 'url' => ['/request/create']],
	    ['label' => 'Facebook广告帐户列表', 'url' => ['/request/index']],
	    ]
	];

}

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
                'brandLabel' => '广告主系统',
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

			/* Facebook帐号管理 */
			echo Nav::widget([
				'options' => ['class' => 'navbar-nav'],
				'items' => $fbaccountMenuItems,
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

		<!-- 提示信息 -->
		<?php
			/*
			Yii::$app()->clientScript->registerScript(
				'myHideEffect',
				'$(".flash-success").animate({opacity: 1.0}, 3000).fadeOut("slow");',
				CClientScript::POS_READY
			);
			 */
		?>
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
