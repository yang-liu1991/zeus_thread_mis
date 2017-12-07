<?php

namespace backend\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\UploadImage;
use backend\models\account\EntityModel;
use backend\models\account\AccountExecutiveModel;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThEntityInfoSearch;
use backend\models\record\ThAgencyBusinessSearch;
use backend\controllers\ThreadBaseController;

/**
 * EntityController implements the CRUD actions for AdAccountInfo model.
 */
class AccountExecutiveController extends ThreadBaseController
{
	public function init()
	{
		parent::init();
		$this->layout = '@app/views/layouts/account-executive.php';
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
                        'actions' => ['index', 'entity-view', 'entity-list', 'account-comment', 'account-abnormal', 'refer-list', 'account-commit', 'account-delete', 'account-reason', 'get-page-link'],
                        'allow' => true,
                        'roles' => ['ae_group'],
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
		return $this->redirect(['account-executive/entity-list']);
	}


    /**
     * Displays a single AdAccountInfo model.
     * @param integer $id
     * @return mixed
     */
    public function actionEntityView($id)
	{
		$model = new EntityModel();
		$model->getAttributeById($id);

        return $this->render('ad-management/entity-view', [
            'model' => $this->findModel($id),
		]);
    }
    

	/**
	 *	AE负责列表
	 */
	public function actionEntityList()
	{
		$searchModel	= new ThEntityInfoSearch();
		$queryParams	= Yii::$app->request->queryParams;
		$audit_status	= !empty($queryParams['audit_status']) ? $queryParams['audit_status'] : ThEntityInfoSearch::AUDIT_STATUS_WAIT;
		$searchParams	= !empty($queryParams['ThEntityInfoSearch']) ? $queryParams['ThEntityInfoSearch'] : [];
		/*按代理公司进行查询*/
		$queryParams['ThEntityInfoSearch']['company_id'] = $this->company_id;
		/* 这里状态为10表示显示所有，只作为一个判断，不表示审核真正的状态 */
		if($audit_status == ThEntityInfoSearch::AUDIT_STATUS_ALL) {
			$auditStatus    = [ThEntityInfoSearch::AUDIT_STATUS_WAIT, ThEntityInfoSearch::AUDIT_STATUS_SUCCESS, ThEntityInfoSearch::AUDIT_STATUS_FAILED];
			$dataProvider = $searchModel->search($queryParams, $auditStatus);
		} else {
			if($searchParams) $queryParams['ThEntityInfoSearch']  = $searchParams;
			if($audit_status == ThEntityInfoSearch::AUDIT_STATUS_WAIT || $audit_status == ThEntityInfoSearch::AUDIT_STATUS_SUCCESS || $audit_status == ThEntityInfoSearch::AUDIT_STATUS_FAILED) 
				$auditStatus = [$audit_status];
			$dataProvider = $searchModel->search($queryParams, $auditStatus);
		}
		$searchModel->audit_status = $audit_status;

		return $this->render('ad-management/entity-list', [
			'searchModel'	=> $searchModel,
            'dataProvider' => $dataProvider,
			]);
	}

	
	/**
	 *	异常帐号列表
	 */
	public function actionAccountAbnormal()
	{
		$searchModel	= new ThAccountInfoSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$status			= !empty($queryParams['status']) ? $queryParams['status'] : ThAccountInfoSearch::getAccountStatus()['ABNORMAL'];
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];
		if($searchParams) $queryParams['ThAccountInfoSearch'] = $searchParams;
		if($status == ThAccountInfoSearch::getAccountStatus()['ABNORMAL'] || $status == ThAccountInfoSearch::getAccountStatus()['FORCEOUT'])
		{
			/*按推荐人进行查询*/
			$queryParams['ThAccountInfoSearch']['company_id'] = $this->company_id;
			$queryParams['ThAccountInfoSearch']['status']		= $status;
			$searchModel->status	= $status;
			$dataProvider = $searchModel->search($queryParams);
		} else {
			return $this->redirect(['account-executive/entity-list']);
		}	

		return $this->render('ad-management/account-abnormal', [
			'searchModel'	=> $searchModel,
			'dataProvider'	=> $dataProvider,
		]);
	}

	
	/**
	 *	Facebook广告帐户申请列表
	 */
	public function actionReferList()
	{
		$entityModel	= new EntityModel();
		$searchModel	= new ThAccountInfoSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];
		$type			= !empty($queryParams['type']) ? $queryParams['type'] : 0;
		if($searchParams) $queryParams['ThAccountInfoSearch']['fbaccount_id'] = $searchParams['fbaccount_id'];
		$queryParams['ThAccountInfoSearch']['company_id'] = $this->company_id;
		/* 由于某种原因，agency只可以看到20161111日期之后的数据, 这也是送给光棍节的礼物吧!*/
		$queryParams['ThAccountInfoSearch']['begin_time'] = '20161111';
		if($type == ThAccountInfoSearch::PARTITIONED_CREDIT || $type == ThAccountInfoSearch::DIRECT_CREDIT)
			$queryParams['ThAccountInfoSearch']['type'] = $type;
		$businessInfo	= ThAgencyBusinessSearch::getAllBusinessByCompanyId($this->company_id);
		$dataProvider = $searchModel->search($queryParams);
		$searchModel->type	= $type;

		return $this->render('ad-management/refer-list', [
			'searchModel'	=> $searchModel,
			'dataProvider'	=> $dataProvider,
			'businessInfo'	=> $businessInfo
		]);
	}

	
	/**
	 *	开户提交操作,重写父类的方法
	 *	@params	int	th_account_info id 主键
	 *	@return bool
	 */
	public function actionAccountCommit()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new AccountExecutiveModel();
			$model->id			= !empty($_POST['id']) ? $_POST['id'] : '';
			$model->business_id = !empty($_POST['business_id']) ? $_POST['business_id'] : '';
			$model->additional_comment	= !empty($_POST['additional_comment']) ? $_POST['additional_comment'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($model->getAccountData($model->id))
			{
				$model->initAttributes($model->getAccountData($model->id));
				$model->type		= ThAccountInfoSearch::PARTITIONED_CREDIT;
				if(!$model->request_id)
				{
					if($model->validate())
					{
						$response = $model->accountCreate();
						if($response === true)
							return ['message' => 'success', 'status' => true];
						return ['message' => 'falied', 'response' => $response];
					}	
				} else {
					if($model->validate())
					{
						$response = $model->accountUpdate();
						if($response === true)
							return ['message' => 'success', 'status' => true];
						return ['message' => 'falied', 'response' => $response];
					}
				}	
			}
			return ['message' => 'falied', 'response' => '您可能没有权限提交开户，请确认！'];
		}
	}


	/**
	 *	开户取消操作
	 *	@params	int	th_account_info id 主键
	 *	@return bool
	 */
	public function actionAccountDelete()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new AccountExecutiveModel();
			$model->id	= !empty($_POST['id']) ? $_POST['id'] : '';
			$getBusinessInfo	= $model->getBusinessInfo($model->id);
			$model->business_id	= $getBusinessInfo['business_id'];
			$model->initAttributes($model->getAccountData($model->id));
			Yii::$app->response->format = Response::FORMAT_JSON;
			$response = $model->accountDelete($model->id);
			if($response === true) 
			{
				return ['message' => 'success', 'status' => true];
			}
			return ['message' => 'failed', 'response' => $response];
		}
	}
	
	
	/**
	 *	获取开户操作的异常信息
	 *	@params	int	th_account_info id 主键
	 *	@return reasons
	 */
	public function actionAccountReason()
	{
		if(Yii::$app->request->isAjax)
		{
			$id	= !empty($_POST['id']) ? $_POST['id'] : '';
			$model = ThAccountInfoSearch::findBySql("select reasons from th_account_info where id = $id")->one();
			$reasons	= $model->reasons;
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($reasons)
			{
				return ['message' => 'success', 'reasons' => $reasons];
			}
			return ['message' => 'failed', 'reasons' => $reasons];
		}
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
	 *	AE备注操作
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
