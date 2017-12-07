<?php

namespace backend\controllers;

use Yii;
use backend\models\user\User;
use backend\models\user\UserSearch;
use backend\models\user\AddUserForm;
use backend\models\user\ModifyForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends ThreadBaseController
{
	/**
	 *	layouts
	 */
	public function init()
	{
		parent::init();
		$this->layout = '@app/views/layouts/admin-manager.php';
	}


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'update', 'create', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'update', 'create', 'delete'],
                        'allow' => true,
                        'roles' => ['admin_group'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
			];
	}

    /**
     * Lists all User models.
     * @return mixed
     */
	public function actionIndex()
	{
		$searchModel = new UserSearch();
		# 默认情况下，查询激活状态的用户
		if(!isset(Yii::$app->request->queryParams['UserSearch']['status'])){
			Yii::$app->request->setQueryParams(['UserSearch' => ['status' => User::STATUS_ACTIVE]]);
		}

		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
			]);
	}

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AddUserForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
				return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
		    'allRbacRole' => User::getAllRbacRoles()
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
		$model = new ModifyForm();
        $model->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
				'allRbacRole' => User::getAllRbacRoles()
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
		$model->status = 0;
		$model->save();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
