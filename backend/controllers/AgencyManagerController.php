<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use backend\models\agency\AgencyModel;
use backend\models\record\ThAgencyBusiness;
use backend\models\record\ThAgencyBusinessSearch;
use backend\controllers\ThreadBaseController;


/**
 * AgencyManagerController implements the CRUD actions for ThAgencyBusiness model.
 */
class AgencyManagerController extends ThreadBaseController
{
    public $enableCsrfValidation = false;

    public function init()
    {
        parent::init();
        $this->layout = '@app/views/layouts/admin-manager.php';
    }

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
                        'actions' => ['index', 'list', 'create', 'update', 'view', 'delete'],
                        'allow'	=> true,
                        'roles'	=> ['admin_group'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }


    /**
     *  Index
     */
    public function actionIndex()
    {
        return $this->redirect('list');
    }


    /**
     * Lists all ThAgencyBusiness models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new ThAgencyBusinessSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ThAgencyBusiness model.
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
     * Creates a new ThAgencyBusiness model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AgencyModel();
        $model->scenario = 'create';

        if($model->load(Yii::$app->request->post()))
        {
            if ($model->validate() && $model->agencyCreate())
            {
                Yii::$app->session->setFlash('agency-create-success');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ThAgencyBusiness model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = new AgencyModel();
        $model->scenario = 'update';
        $model->initAttributes($id);
        if($model->load(Yii::$app->request->post()))
        {
            if($model->validate() && $model->agencyUpdate($id))
            {
                Yii::$app->session->setFlash('agency-update-success');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ThAgencyBusiness model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = new AgencyModel();
        if($model->agencyDelete($id))
        {
            Yii::$app->session->setFlash('agency-delete-success');
            return $this->redirect(['list']);
        }
    }

    /**
     * Finds the ThAgencyBusiness model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ThAgencyBusiness the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ThAgencyBusiness::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
