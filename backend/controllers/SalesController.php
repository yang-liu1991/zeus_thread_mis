<?php

namespace backend\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\user\UserModel;
use backend\models\account\EntityModel;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThEntityInfoSearch;
use backend\controllers\ThreadBaseController;

/**
 * SalesController implements the CRUD actions for AdAccountInfo model.
 */
class SalesController extends ThreadBaseController
{
	public function init()
	{
		parent::init();
		$this->layout = '@app/views/layouts/sales.php';
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
                        'actions' => ['index', 'entity-view', 'entity-list', 'detail', 'account-comment', 'account-abnormal'],
                        'allow' => true,
                        'roles' => ['sale_group'],
                    ],
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
	 *	default action
	 */
	public function actionIndex()
	{
		return $this->redirect(['sales/entity-list']);
	}


    /**
     * Displays a single AdAccountInfo model.
     * @param integer $id
     * @return mixed
     */
    public function actionEntityView($id)
	{
		$model = new EntityModel();
		$model->getAttribute($id);

        return $this->render('ad-management/entity-view', [
            'model' => $this->findModel($id),
		]);
    }
    

	/**
	 *	sale负责列表
	 */
	public function actionEntityList()
	{
		$entityModel	= new EntityModel();
		$searchModel	= new ThAccountInfoSearch();
		$queryParams	= Yii::$app->request->queryParams;
		$audit_status	= !empty($queryParams['audit_status']) ? $queryParams['audit_status'] : ThEntityInfoSearch::AUDIT_STATUS_WAIT;
		$searchParams	= !empty($queryParams['EntityModel']) ? $queryParams['EntityModel'] : [];
		$userInfo		= UserModel::getLoginInfo();	
		
		/*按推荐人进行查询*/
		$queryParams['ThAccountInfoSearch']['referral'] = $userInfo->email;
		/*按公司中文名称进行搜索*/
		if($searchParams) $queryParams['ThAccountInfoSearch']['name_zh'] = $searchParams['name_zh'];
		/* 这里状态为10表示显示所有，只作为一个判断，不表示审核真正的状态 */
		if($audit_status == ThEntityInfoSearch::AUDIT_STATUS_ALL) {
			$dataProvider = $searchModel->search($queryParams);
		} elseif($audit_status == ThEntityInfoSearch::AUDIT_STATUS_WAIT || $audit_status == ThEntityInfoSearch::AUDIT_STATUS_SUCCESS || $audit_status == ThEntityInfoSearch::AUDIT_STATUS_FAILED) {
			$queryParams['ThAccountInfoSearch']['audit_status'] = $audit_status;
			$dataProvider = $searchModel->search($queryParams);
		}
		$entityModel->audit_status = $audit_status;

		return $this->render('ad-management/entity-list', [
			'searchModel'	=> $searchModel,
			'entityModel'	=> $entityModel,
            'dataProvider' => $dataProvider,
			]);
	}

	
	/**
	 *	异常帐号列表
	 */
	public function actionAccountAbnormal()
	{
		$entityModel	= new EntityModel();
		$searchModel	= new ThAccountInfoSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$status			= !empty($queryParams['status']) ? $queryParams['status'] : ThAccountInfoSearch::getAccountStatus()['ABNORMAL'];
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];
		$userInfo		= UserModel::getLoginInfo();	
		if($searchParams) $queryParams['ThAccountInfoSearch'] = $searchParams;
		if($status == ThAccountInfoSearch::getAccountStatus()['ABNORMAL'] || $status == ThAccountInfoSearch::getAccountStatus()['FORCEOUT'])
		{
			/*按推荐人进行查询*/
			$queryParams['ThAccountInfoSearch']['referral']	= $userInfo->email;
			$queryParams['ThAccountInfoSearch']['status']		= $status;
			$searchModel->status	= $status;
			$dataProvider = $searchModel->search($queryParams);
		} else {
			return $this->redirect(['sales/entity-list']);
		}	

		return $this->render('ad-management/account-abnormal', [
			'searchModel'	=> $searchModel,
			'dataProvider'	=> $dataProvider,
		]);
	}



    /**
     * Deletes an existing AdAccountInfo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


	/**
	 *	帐户信息详情
	 */
	public function actionDetail($id)
	{
		$model = new EntityModel();
		$model->getAttributeById($id);

        return $this->render('ad-management/account-detail', [
            'model' => $this->findModel($id),
		]);
	}

	
	/**
	 *	备注操作
	 */
	public function actionAccountComment()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new EntityModel();
			$id	= !empty($_POST['id']) ? $_POST['id'] : '';
			$model->entity_note	= !empty($_POST['entity_note']) ? $_POST['entity_note'] : null;
			$updateParams	= ['entity_note' => $model->entity_note];
			
			Yii::$app->response->format = Response::FORMAT_JSON;	
			if ($model->validate() && $model->entityUpdateById($id, $updateParams, 'note'))
			{
				return ['message' => 'success', 'status' => true];
			}
			return ['message' => 'falied', 'status' => false];
		}
	}

    /**
     * Finds the AdAccountInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AdAccountInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EntityModel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
