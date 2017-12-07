<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\user\LoginForm;
use backend\models\user\AddUserForm;
use backend\models\user\ChangePassword;
use backend\models\user\ResetPassword;
use backend\models\user\PasswordResetRequest;
use backend\models\account\EntityModel;
use backend\controllers\ThreadBaseController;

/**
 * Site controller
 */
class SiteController extends ThreadBaseController
{
	public $defaultAction = 'index';
	

   /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'signup', 'reset-password', 'request-password-reset'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'change-password'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }


	/**
	 *	登录操作
	 */
    public function actionLogin()
    {
		if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			$userId = Yii::$app->user->identity->id;
			/* 登录用户为广告主 */
			if(Yii::$app->user->can('ad_group'))
			{
				$entityModel = EntityModel::findWhere(['user_id' => $userId]);
				if($entityModel) 
				{
					return $this->redirect(['advertiser/entity-list']);
				} else {
					return $this->redirect(['advertiser/entity-create']);
				}
			/* 登录用户为管理员 */
			} elseif(Yii::$app->user->can('admin_group')) {
				return $this->redirect(['admin-manager/index']);
			/* 登录用户为AE */
			} elseif(Yii::$app->user->can('ae_group')) {
				return $this->redirect(['account-executive/index']);
			/* 登录用户为sale */
			} elseif(Yii::$app->user->can('sale_group')) {
				return $this->redirect(['sales/index']);
			}
			
			return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }


	/**
	 *	注册操作
	 */
	public function actionSignup()
	{
		$model = new AddUserForm();
		$model->rbacRole = ['ad_group']; 
		
		if($model->load(Yii::$app->request->post()))
		{
			if($user = $model->signup())
			{
				Yii::$app->session->setFlash('account-success');
				if(Yii::$app->getUser()->login($user))
				{
					return $this->redirect(['advertiser/entity-create']);
				}
			}
		} 

		return $this->render('signup', [
			'model' => $model,
		]);
	}


	/**
	 *	注销操作
	 */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

	 /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequest();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }
        return $this->render('request-password-reset-token', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPassword($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');
            return $this->goHome();
        }
        return $this->render('reset-password', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
            return $this->goHome();
        }
        return $this->render('change-password', [
                'model' => $model,
        ]);
    }

    /**
     * Activate new user
     * @param integer $id
     * @return type
     * @throws UserException
     * @throws NotFoundHttpException
     */
    public function actionActivate($id)
    {
        /* @var $user User */
        $user = $this->findModel($id);
        if ($user->status == User::STATUS_INACTIVE) {
            $user->status = User::STATUS_ACTIVE;
            if ($user->save()) {
                return $this->goHome();
            } else {
                $errors = $user->firstErrors;
                throw new UserException(reset($errors));
            }
        }
        return $this->goHome();
    }
}
