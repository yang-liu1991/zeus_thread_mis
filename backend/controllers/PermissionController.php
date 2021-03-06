<?php

namespace backend\controllers;

use Yii;
use backend\models\auth\AuthItem;
use backend\models\auth\AuthItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\rbac\Permission;
use backend\controllers\ThreadBaseController;

/**
 * PermissionController implements the CRUD actions for AuthItem model.
 */
class PermissionController extends ThreadBaseController
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
                        'roles' => ['permission', 'admin_group'],
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
     * Lists all AuthItem models.
     * @return mixed
     */
	public function actionIndex()
	{
		$searchModel = new AuthItemSearch();

		# 设置查询的类型为 Permission
		$queryParams = Yii::$app->request->getQueryParam('AuthItemSearcher', []);
		$queryParams = array_merge($queryParams, ['type' => Permission::TYPE_PERMISSION]);
		Yii::$app->request->setQueryParams(['AuthItemSearcher' => $queryParams]);

		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('//auth/permission/index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
			]);
	}

    /**
     * Displays a single AuthItem model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('//auth/permission/view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AuthItem();

        if ($model->load(Yii::$app->request->post()) && $model->saveRecord()) {
            return $this->redirect(['view', 'id' => $model->name]);
        } else {
	    $model->type = Permission::TYPE_PERMISSION;
            return $this->render('//auth/permission/create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->saveRecord()) {
            return $this->redirect(['view', 'id' => $model->name]);
        } else {
	    $model->type = Permission::TYPE_PERMISSION;
            return $this->render('//auth/permission/update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
	$auth = Yii::$app->authManager;
	$permission = $auth->getPermission($id);
	$auth->remove($permission);
        //$this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
