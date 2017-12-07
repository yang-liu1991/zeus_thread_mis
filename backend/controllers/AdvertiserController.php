<?php

/**
 *	广告主操作的入口，包括实体信息注册、更新；开户申请等操作；
 */
namespace backend\controllers;

use backend\models\record\ThMessage;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use backend\models\account\RemindModel;
use backend\models\account\EntityModel;
use backend\models\account\RequestModel;
use backend\models\account\FbVertical;
use backend\models\message\MessageModel;
use backend\models\user\UserModel;
use backend\models\record\ThEntityInfoSearch;
use backend\models\record\ThAccountInfoSearch;
use backend\controllers\ThreadBaseController;


class AdvertiserController extends ThreadBaseController
{
	public $enableCsrfValidation = false;

	public function init()
	{
		parent::init();
		$this->layout = '@app/views/layouts/advertiser.php';
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
                        'actions' => ['index', 'entity-list', 'entity-view', 'entity-create', 'entity-update', 'account-apply-add', 'account-apply', 'account-list', 'account-reason', 'account-remind', 'get-subvertical', 'validate-entity-form', 'validate-referral', 'upload-file'],
                        'allow' => true,
                        'roles' => ['ad_group'],
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
     * Lists all AdAccountInfo models.
     * @return mixed
     */
    public function actionIndex()
	{
		return $this->redirect(['entity-view']);
	}

    /**
     * Displays a single AdAccountInfo model.
     * @param integer $id
     * @return mixed
     */
    public function actionEntityView($id)
	{
		/* 没有注册的用户，请先注册 */
		$entityModel = EntityModel::findWhere(['user_id' => $this->user_id]);
		if(!$entityModel) 
		{
			Yii::$app->session->destroy();
			Yii::$app->session->setFlash('entity-not-found');
			return $this->redirect(['advertiser/entity-create']);
		}

		$model = new EntityModel();
		$model->getAttributeById($id);
		$entitymodel = entitymodel::findwhere(['id'	=> $id, 'user_id' => $this->user_id]);
		if($entitymodel)
		{
			return $this->render('ad-management/entity-view', [
				'model' => $this->findModel($id),
			]);
		} else {
			throw new NotFoundHttpException('Entity not Found!'); 
		}
    }

    /**
     * Creates a new AdAccountInfo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionEntityCreate()
    {
		/* 已经注册过的用户，不能再注册 */
		$userInfo = UserModel::getLoginInfo();
        $model = new EntityModel();
		$model->scenario = 'create';
		if ($model->load(Yii::$app->request->post())) 
		{
			$model->promotable_urls = !empty($_POST['EntityModel']['promotable_urls']) ? $_POST['EntityModel']['promotable_urls'] : [];
			$model->user_id	= $userInfo->id;

			if($model->validate() && $model->entityCreate())
			{
				Yii::$app->session->setFlash('entity-create-success');
				return $this->redirect(['entity-view', 'id' => $model->id]);
			} else {
				$errors = $model->getErrors();
				Yii::$app->session->setFlash('entity-create-error', $errors);
				return $this->render('ad-management/entity-create', [
					'model' => $model,
				]);
			}
		} else {
			return $this->render('ad-management/entity-create', [
					'model' => $model,
			]);
		}
	}

    /**
     * Updates an existing AdAccountInfo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEntityUpdate($id)
    {
		/* 没有注册的用户，请先注册 */
		$userInfo = UserModel::getLoginInfo();
		$entityModel = EntityModel::findWhere(['user_id' => $userInfo->id]);
		if(!$entityModel) 
		{
			Yii::$app->session->setFlash('entity-not-found');
			return $this->redirect(['advertiser/entity-create']);
		}

        $model = new EntityModel();
		$model->getAttributeById($id);
		$model->scenario = 'update';

		if ($model->load(Yii::$app->request->post()))
		{
			$model->promotable_urls = !empty($_POST['EntityModel']['promotable_urls']) ? $_POST['EntityModel']['promotable_urls'] : [];
			/* 只有审核通过后的更新，才会更新审核状态 */
			$model->audit_status	= ($model->audit_status == ThEntityInfoSearch::AUDIT_STATUS_SUCCESS) ? 0 : $model->audit_status;
			if($model->validate() && $model->entityUpdate($id)) 
			{
				Yii::$app->session->setFlash('entity-update-success');
				return $this->redirect(['entity-view', 'id' => $id]);
			} else {
				$errors = $model->getErrors();
				Yii::$app->session->setFlash('entity-update-error', $errors);
				return $this->render('ad-management/entity-update', [
					'model' => $model,
				]);
			}
		} else {
			return $this->render('ad-management/entity-update', [
				'model' => $model,
			]);
		}
    }


	/**
	 *	实体信息列表
	 */
	public function actionEntityList()
	{
		$searchModel	= new ThEntityInfoSearch();
		$queryParams	= Yii::$app->request->queryParams;
		$searchParams	= !empty($queryParams['ThEntityInfoSearch']) ? $queryParams['ThEntityInfoSearch'] : [];
		$queryParams['ThEntityInfoSearch']['user_id'] = $this->user_id;
		$auditStatus    = [ThEntityInfoSearch::AUDIT_STATUS_WAIT, ThEntityInfoSearch::AUDIT_STATUS_SUCCESS, ThEntityInfoSearch::AUDIT_STATUS_FAILED];
		/* 广告主需要看到所有审核状态的实体信息，包括未提交的 */
		$dataProvider = $searchModel->search($queryParams, $auditStatus);
		
		return $this->render('ad-management/entity-list', [
			'searchModel'	=> $searchModel,
            'dataProvider' => $dataProvider,
		]);
	}


	/**
	 *	帐户信息列表
	 */
	public function actionAccountApply()
	{
		$searchModel	= new ThEntityInfoSearch();
		$queryParams	= Yii::$app->request->queryParams;
		$searchParams	= !empty($queryParams['ThEntityInfoSearch']) ? $queryParams['ThEntityInfoSearch'] : [];
		$queryParams['ThEntityInfoSearch']['user_id'] = $this->user_id;
		$auditStatus    = [ThEntityInfoSearch::AUDIT_STATUS_WAIT, ThEntityInfoSearch::AUDIT_STATUS_SUCCESS, ThEntityInfoSearch::AUDIT_STATUS_FAILED];
		/* 广告主需要看到审核成功的实体，不成功也没办法开户，对吧！ */
		$dataProvider = $searchModel->search($queryParams, $auditStatus);
		
		return $this->render('ad-management/account-apply', [
			'searchModel'	=> $searchModel,
            'dataProvider' => $dataProvider,
		]);
	}


	/**
	 *	帐户信息列表
	 */
	public function actionAccountList()
	{
		$searchModel	= new ThAccountInfoSearch();
		$queryParams	= Yii::$app->request->queryParams;
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];
		$queryParams['ThAccountInfoSearch']['user_id'] = $this->user_id;
		/* 广告主需要看到所有审核状态的实体信息，包括未提交的 */
		$dataProvider = $searchModel->search($queryParams);

		return $this->render('ad-management/account-list', [
			'searchModel'	=> $searchModel,
            'dataProvider' => $dataProvider,
		]);
	}


	/**
	 *	开户提醒
	 */
	public function actionAccountRemind()
	{
		if(Yii::$app->request->isAjax)
		{
			$id	= !empty($_POST['id']) ? $_POST['id'] : '';
			$model = new RemindModel();
			$accountInfo = $model->getAccountInfo($id);
			$model->setAttributes($accountInfo);
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($model->validate() && $model->sendingEmail())
			{
				return ['status' => true, 'message' => ''];
			}
			$message = $model->getErrors();
			return ['status' => false, 'message' => json_encode($message)];
		}
	}


	/**
	 *	帐户申请提交
	 */
	public function actionAccountApplyAdd()
    {
		$queryParams		= Yii::$app->request->queryParams;
		$entityId			= !empty($queryParams['entity-id']) ? $queryParams['entity-id'] : Null;
		$entityModel		= new EntityModel();
		$requestModel		= new RequestModel();
		$entityModel->getAttributeById($entityId);
		$entityModel->scenario	= 'update';
		if($entityModel->load(Yii::$app->request->post()))
		{
			$requestList	= Yii::$app->request->post('RequestModel');
			$entityData		= Yii::$app->request->post('EntityModel');
			$entityModel->promotable_urls	= !empty($entityData['promotable_urls']) ? $entityData['promotable_urls'] : [];
			$requestModel->setUserAttributes();
			$requestModel->entity_id		= $entityId;
			$requestModel->fbaccount_name	= $entityModel->name_en;
			$requestModel->request_list		= $requestList;
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($entityModel->validate() && $entityModel->entityUpdate($entityId) && $requestModel->requestSave())
			{
				Yii::$app->session->setFlash('account-apply-success');
				MessageModel::saveMessage(ThMessage::CREATE_ACCOUNT, $requestModel->attributes);
				return ['status' => true, 'message' => ''];
			}
			$errors = $entityModel->getErrors();
			return ['status' => false, 'message' => json_encode($errors)];
		} else {
			return $this->render('ad-management/account-apply-add', [
				'entityModel' => $entityModel,
				'model' => $requestModel,
				'entityId'  => $entityId,
			]);
		}
	}

	
	/**
	 *	获取业务类型
	 *	@params	str	vertical
	 *	@return array	subvertical
	 */
	public function actionGetSubvertical()
	{
		if(Yii::$app->request->isAjax)
		{	
			$verticalId = !empty($_POST['verticalId']) ? $_POST['verticalId'] : 0;
			$option = '<option value="">请选择业务类型</option>';
			$vertical = FbVertical::getVerticals()[$verticalId];
			$subverticals = FbVertical::getVerticalMappings()[$vertical];
			if($subverticals)
			{
				foreach($subverticals as $key => $value)
				{
					$option .= '<option value="' . $key . '">' . $value . '</option>';
				}
			} else {
				$option = '请选择业务类型';
			}

			return $option;
		}
	}


	/**
	 *	Ajax validate create entity
	 *	@return bool
	 */
	public function actionValidateEntityForm()
	{
		$model = new EntityModel();
		$model->scenario = 'create';
		if(Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			return ActiveForm::validate($model);
		}
		return $this->render('ad-management/entity-create', [
			'model' => $model,
		]);
	}


	/**
	 *	Ajax validate referral
	 *	@return bool
	 */
	public function actionValidateReferral()
	{
		if(Yii::$app->request->isAjax)
		{
			$referral = !empty($_POST['referral']) ? $_POST['referral'] : '';
			$validateResult = RequestModel::checkReferral($referral);
			Yii::$app->response->format = Response::FORMAT_JSON;
			return $validateResult;
		} else {
			throw new NotFoundHttpException('The referral does not exist.');
		}
	}


	/**
	 *	Ajax upload File
	 *	@return array
	 */
	public function actionUploadFile()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new EntityModel();
			$imagePath = $model->uploadImage();
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($imagePath)
			{
				return ['status' => true, 'filePath' => $imagePath];
			} else {
				return ['status' => false, 'filePath' => ''];
			}
		} else {
			throw new NotFoundHttpException('The upload file requests does not exist.');
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
