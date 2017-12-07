<?php

namespace backend\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\message\MessageModel;
use backend\models\account\EntityModel;
use backend\models\account\RequestModel;
use backend\models\account\AdminManagerModel;
use backend\models\creatives\CreativesExportModel;
use backend\models\creatives\CreativesUploadModel;
use backend\models\record\ThEntityInfoSearch;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThAgencyBusinessSearch;
use backend\models\record\ThAdCreativesSearch;
use backend\controllers\ThreadBaseController;

/**
 * EntityController implements the CRUD actions for AdAccountInfo model.
 */
class AdminManagerController extends ThreadBaseController
{
	public $enableCsrfValidation = false;
	public $messageList;

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
						'actions' => ['index', 'entity-view', 'entity-list', 'abnormal-list', 'refer-list', 'account-mapping', 'creatives-view', 'creatives-audit', 'creatives-export', 'account-commit', 'account-delete', 'account-reason', 'get-page-link', 'refer-export', 'get-entity-comment', 'upload-file'],
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
	 *	默认跳转到list
	 */
	public function actionIndex()
	{
		return $this->redirect(['entity-list']);
	}

    /**
	 * 主体审核查看
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
	 *	主体审核列表
	 */
	public function actionEntityList()
	{
		$searchModel	= new ThEntityInfoSearch();
		$queryParams	= Yii::$app->request->queryParams;
		$audit_status	= !empty($queryParams['audit_status']) ? $queryParams['audit_status'] : ThEntityInfoSearch::AUDIT_STATUS_WAIT;
		$searchParams	= !empty($queryParams['ThEntityInfoSearch']) ? $queryParams['ThEntityInfoSearch'] : [];
		
		/* 这里状态为10表示显示所有，只作为一个判断，不表示审核真正的状态 */
		if($audit_status == ThEntityInfoSearch::AUDIT_STATUS_ALL) {
			$auditStatus    = [ThEntityInfoSearch::AUDIT_STATUS_WAIT, ThEntityInfoSearch::AUDIT_STATUS_SUCCESS, ThEntityInfoSearch::AUDIT_STATUS_FAILED];
			$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $auditStatus);
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
	public function actionAbnormalList()
	{
		$searchModel	= new ThAccountInfoSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$status			= !empty($queryParams['status']) ? $queryParams['status'] : 0;
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];
		if($searchParams) $queryParams['ThAccountInfoSearch'] = $searchParams;
		if($status == ThAccountInfoSearch::getAccountStatus()['ABNORMAL'] || $status == ThAccountInfoSearch::getAccountStatus()['FORCEOUT'])
		{
			$queryParams['ThAccountInfoSearch']['status']	= $status;
			$searchModel->status	= $status;
			$dataProvider = $searchModel->search($queryParams);
		} else {
			return $this->redirect(['entity-list']);
		}	

		return $this->render('ad-management/abnormal-list', [
			'searchModel'	=> $searchModel,
			'dataProvider'	=> $dataProvider,
		]);
	}


	/**
	 *	Facebook广告帐户申请列表
	 */
	public function actionReferList()
	{
		$searchModel	= new ThAccountInfoSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$type			= !empty($queryParams['type']) ? $queryParams['type'] : 0;
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];
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
	 *	公司帐户对应例表
	 */
	public function actionAccountMapping()
	{
		$searchModel	= new ThAccountInfoSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];

		if($searchParams) $queryParams['ThAccountInfoSearch'] = $searchParams;
		$dataProvider = $searchModel->search($queryParams);
	
		return $this->render('fb-management/account-mapping', [
			'searchModel'	=> $searchModel,
			'dataProvider'	=> $dataProvider,
		]);
	}
	

	/**
	 *	创意图片展示列表
	 */
	public function actionCreativesView()
	{
		$searchModel	= new ThAdCreativesSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$audit_status	= !empty($queryParams['audit_status']) ? $queryParams['audit_status'] : ThAdCreativesSearch::CREATIVES_STATUS_WAIT;

		if($audit_status == ThAdCreativesSearch::CREATIVES_STATUS_WAIT || 
			$audit_status == ThAdCreativesSearch::CREATIVES_STATUS_SUCCESS || 
			$audit_status == ThAdCreativesSearch::CREATIVES_STATUS_FAILED) 
		{
			$searchModel->audit_status = $audit_status;
			$queryParams['ThAdCreativesSearch']['audit_status'] = $audit_status;
		}
		$dataProvider = $searchModel->search($queryParams);

		return $this->render('fb-management/creatives-view', [
			'searchModel'	=> $searchModel,
			'dataProvider'	=> $dataProvider,
		]);
	}


	/**
	 *	创意审核操作
	 */
	public function actionCreativesAudit()
	{

		if(Yii::$app->request->isAjax)
		{
			$account_id		= !empty($_POST['account_id']) ? $_POST['account_id'] : '';
			$ad_id			= !empty($_POST['ad_id']) ? $_POST['ad_id'] : '';
			$audit_status	= !empty($_POST['audit_status']) ? $_POST['audit_status'] : 0;
			$audit_message	= !empty($_POST['audit_message']) ? $_POST['audit_message'] : null;
			
			$result = ThAdCreativesSearch::updateAll(['audit_status' => $audit_status, 'audit_message' => $audit_message], 
				'account_id = :account_id and ad_id = :ad_id',
				[':account_id' => $account_id, ':ad_id' => $ad_id]
			);
			Yii::$app->response->format = Response::FORMAT_JSON;
			if ($result > 0)
			{
				return ['message' => 'success', 'status' => true];
			}
			return ['message' => 'falied', 'status' => false];
		}
	}


	/**
	 *	导出创意数据
	 */
	public function actionCreativesExport()
	{
		$searchModel = new ThAdCreativesSearch();
		$exportModel = new CreativesExportModel();
		$queryParams = Yii::$app->request->queryParams;
		$dataProvider = $searchModel->search($queryParams);
		$dataProvider->setPagination(false);
		$objectPHPExcel	= $exportModel->buildExcelObj($dataProvider->getModels());
		if($objectPHPExcel)
		{
			$exportModel->downloadExcelFile($objectPHPExcel);
			return true;
		} else {
			throw new NotFoundHttpException('Export Exception!');
		}	
	}

	
	/**
	 *	开户提交操作
	 *	@params	int	th_account_info id 主键
	 *	@return bool
	 */
	public function actionAccountCommit()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new AdminManagerModel();
			$model->id			= !empty($_POST['id']) ? $_POST['id'] : '';
			$model->business_id = !empty($_POST['business_id']) ? $_POST['business_id'] : '';
			$model->additional_comment	= !empty($_POST['additional_comment']) ? $_POST['additional_comment'] : '';
			$getBusinessInfo	= $model->getBusinessInfo($model->id);
			if($getBusinessInfo)
			{
				$model->planning_agency_business_id	= $getBusinessInfo['business_agency_id'];
				$model->initAttributes($model->getAccountData($model->id, $model->planning_agency_business_id));
				Yii::$app->response->format = Response::FORMAT_JSON;
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
			return ['message' => 'falied', 'response' => ''];
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
			$model = new AdminManagerModel();
			$model->id	= !empty($_POST['id']) ? $_POST['id'] : '';
			$getBusinessInfo  = $model->getBusinessInfo($model->id);
			$model->planning_agency_business_id = $getBusinessInfo['business_agency_id'];
			$model->business_id	= $getBusinessInfo['business_id'];
			$model->initAttributes($model->getAccountData($model->id, $model->planning_agency_business_id));
			Yii::$app->response->format = Response::FORMAT_JSON;
			$response = $model->accountDelete($model->id, $model->planning_agency_business_id);
			if($response === true) 
			{
				return ['message' => 'success', 'status' => true];
			}
			return ['message' => 'failed', 'response' => $response];
		}
	}


	/**
	 *	根据th_account_info主建id获取相应实体的comment
	 *	@params	int	th_account_info id
	 *	@return obj
	 */
	public function actionGetEntityComment()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new AdminManagerModel();
			$model->id = !empty($_POST['id']) ? $_POST['id'] : '';
			$entity_comment = $model->getEntityComment();
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($entity_comment)
			{
				$comment = implode("<br/>", json_decode($entity_comment, true));
				return ['message' => 'success', 'comment' => $comment];
			}
			return ['message' => 'failed', 'comment' => ''];
		}
	}


    /**
     *	文件上传的操作
     */
    public function actionUploadFile()
    {
        if(Yii::$app->request->isAjax)
        {
            $requestData = !empty($_FILES) ? $_FILES : '';
            $model = new CreativesUploadModel();
            Yii::$app->response->format = Response::FORMAT_JSON;
            $uploadFile = UploadedFile::getInstance($model, 'upload_file');

            if($uploadFile)
            {
                $uploadData	= $model->getUploadData($uploadFile);

                if($uploadData) return ['status' => 'true', 'files' => [$uploadFile], 'accountInfoList' => $uploadData];
            }
            return ['status' => 'false', 'accountInfoList' => []];
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
