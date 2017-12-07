<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-11-23 17:15:56
 */
namespace backend\controllers;

use backend\models\record\ThMessage;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use common\struct\AccountChangeType;
use backend\models\message\MessageModel;
use backend\models\account\BindingModel;
use backend\models\account\ChangeNameModel;
use backend\models\account\CreditLimitModel;
use backend\controllers\ThreadBaseController;
use backend\models\record\ThAgencyBindingSearch;
use backend\models\record\ThAgencyCreditlimitSearch;
use backend\models\record\ThChangeRecordSearch;


class AccountManagerController extends ThreadBaseController
{
	public $enableCsrfValidation = false;

	public function init()
	{
		parent::init();
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
                        'actions' => ['get-account-info', 'spendcap-list', 'spendcap-change', 'validate-credit-limit', 'validate-binding', 'validate-name', 'binding-change', 'binding-list', 'name-change', 'name-list', 'change-reason', 'binding-reason', 'credit-limit-reason', 'upload-file'],
                        'allow' => true,
                        'roles' => ['admin_group', 'ae_group', 'ad_group'],
                    ],
					[
						'actions'	=> ['reject-change', 'submit-change', 'reject-binding', 'submit-binding', 'reject-credit-limit', 'submit-credit-limit'],
						'allow'		=> true,
						'roles'		=> ['admin_group']
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
	 *	额度调整
	 */
	public function actionSpendcapChange()
	{
		$model = new CreditLimitModel();
		$model->scenario = 'setAccountInfo';
		$queryParams = Yii::$app->request->queryParams;
		$model->action	= !empty($queryParams['action']) ? $queryParams['action'] : AccountChangeType::ACTION_SINGLE;
		if($model->load(Yii::$app->request->post()))
		{
			$spendCapFormData = Yii::$app->request->post('CreditLimitModel');
			$accounts	= $model->mergeAccountFormData($spendCapFormData);
			Yii::$app->response->format = Response::FORMAT_JSON;
			$model->setUserAttributes();
			if($model->saveSpendcapRecord())
			{
				MessageModel::saveMessage(ThMessage::CHANGE_CREDITLIMIT, $model->attributes);
				Yii::$app->session->setFlash('spend-cap-change-success');	
				return ['status' => true, 'error_message' => ''];
			} else {
				$error_message = $model->error_message;
				return ['status' => false, 'error_message' => $model->getErrors()];
			}
		}

		return $this->render('credit-limit/spendcap-change',[
			'model' => $model
			]);
	}

	
    /**
     * Lists all ThAgencyCreaditlimit models.
     * @return mixed
     */
    public function actionSpendcapList()
    {
		$searchModel = new ThAgencyCreditlimitSearch();
		$queryParams	= Yii::$app->request->queryParams;
		if(Yii::$app->user->can('ad_group'))
		{
			$queryParams['ThAgencyCreditlimitSearch']['user_id'] = $this->user_id;
		} elseif(Yii::$app->user->can('ae_group')) {
			$queryParams['ThAgencyCreditlimitSearch']['company_id'] = $this->company_id;
		} elseif(Yii::$app->user->can('admin_group')) {
		} else {
			throw new NotFoundHttpException('Please Login!');	
		}
		$dataProvider = $searchModel->search($queryParams);

        return $this->render('credit-limit/spendcap-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


	/**
	 *	Ajax validate creditlimit
	 *	@return bool
	 */
	public function actionValidateCreditLimit()
	{
		$model = new CreditLimitModel();
		$model->scenario = 'setAccountInfo';
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) 
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			return ActiveForm::validate($model);
		}
		return $this->render('credit-limit/spend-change', [
			'model' => $model
		]);
	}


	/**
	 *	Ajax validate binding
	 *	@return bool
	 */
	public function actionValidateBinding()
	{
		$model = new BindingModel();
		$model->scenario = 'setAccountInfo';
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) 
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			return ActiveForm::validate($model);
		}
		return $this->render('binding/binding-change', [
			'model' => $model
		]);
	}
	
	
	/**
	 *	Ajax validate change name
	 *	@return bool
	 */
	public function actionValidateName()
	{
		$model = new ChangeNameModel();
		$model->scenario = 'setAccountInfo';
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) 
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			return ActiveForm::validate($model);
		}
		return $this->render('name/name-change', [
			'model' => $model
		]);
	}


	/**
	 *	ajax 获取account info
	 */
	public function actionGetAccountInfo()
	{
		if(Yii::$app->request->isAjax)
		{
			$requestData = Yii::$app->request->post();
			if(array_key_exists('ChangeNameModel', $requestData))
			{
				$model	= new ChangeNameModel();
			} elseif(array_key_exists('BindingModel', $requestData)) {
				$model	= new BindingModel();
			} elseif(array_key_exists('CreditLimitModel', $requestData)) {
				$model	= new CreditLimitModel();
			} else {
				throw New HttpException('Unknow Post Data');
			}

			$model->scenario = 'getAccountInfo';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($model->load(Yii::$app->request->post()))
			{
				if($model->validate())
				{
					$accountInfo	= $model->getFormatResponse($model->account_id);
					if($accountInfo) return ['status' => true, 'accountInfo' => $accountInfo];
				}
				return ['status' => false, 'error_message' => json_encode($model->getErrors())];
			}
			return ['status' => false, 'error_message' => ['account_id' => $model->account_id, 
				'message' => '获取Account信息发生错误！']];
		} else {	
			throw new NotFoundHttpException('Something unexpected happened!');
		}
	}


	/**
	 *	帐户绑定操作
	 */
	public function actionBindingChange()
	{
		$model = new BindingModel();
		$queryParams = Yii::$app->request->queryParams;
		$model->scenario	= 'setAccountInfo';
		$model->action		= !empty($queryParams['action']) ? $queryParams['action'] : AccountChangeType::ACTION_SINGLE;
		if($model->load(Yii::$app->request->post()))
		{
			$bindingFormData = Yii::$app->request->post('BindingModel');
			$accounts = $model->mergeAccountFormData($bindingFormData);
			Yii::$app->response->format = Response::FORMAT_JSON;
			$model->setUserAttributes();
			if($model->saveBindingRecord())
			{
			    MessageModel::saveMessage(ThMessage::CHANGE_BINDING, $model->attributes);
				Yii::$app->session->setFlash('binding-change-success');	
				return ['status' => true, 'error_message' => ''];
			} else {
				$error_message = $model->error_message;
				return ['status' => false, 'error_message' => $error_message];
			}
		}

		return $this->render('binding/binding-change',[
			'model' => $model
		]);
	}


	/**
     * Lists all ThAgencyBinding models.
     * @return mixed
     */
    public function actionBindingList()
    {
		$searchModel = new ThAgencyBindingSearch();
		$queryParams	= Yii::$app->request->queryParams;
		if(Yii::$app->user->can('ad_group'))
		{
			$queryParams['ThAgencyBindingSearch']['user_id'] = $this->user_id;
		} elseif(Yii::$app->user->can('ae_group')) {
			$queryParams['ThAgencyBindingSearch']['company_id'] = $this->company_id;
		} elseif(Yii::$app->user->can('admin_group')) {
		} else {
			throw new NotFoundHttpException('Please Login!');	
		}
        $dataProvider = $searchModel->search($queryParams);

        return $this->render('binding/binding-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	
	/**
	 *	名称更新操作
	 */
	public function actionNameChange()
	{
		$model = new ChangeNameModel();
		$queryParams = Yii::$app->request->queryParams;
		$model->scenario	= 'setAccountInfo';
		$model->action		= !empty($queryParams['action']) ? $queryParams['action'] : AccountChangeType::ACTION_SINGLE;
		if($model->load(Yii::$app->request->post()))
		{
			$nameChangeFormData = Yii::$app->request->post('ChangeNameModel');
			$accounts	= $model->mergeAccountFormData($nameChangeFormData);
			Yii::$app->response->format = Response::FORMAT_JSON;
			$model->setUserAttributes();
			if($model->saveChangeRecord())
			{
				MessageModel::saveMessage(ThMessage::CHANGE_NAME, $model->attributes);
				Yii::$app->session->setFlash('name-change-success');	
				return ['status' => true, 'error_message' => ''];
			} else {
				$error_message = $model->error_message;
				return ['status' => false, 'error_message' => $error_message];
			}
		}
		return $this->render('name/name-change',[
			'model' => $model
		]);
	}


	/**
     * Lists all ThAgencyBinding models.
     * @return mixed
     */
    public function actionNameList()
    {
		$searchModel = new ThChangeRecordSearch();
		$queryParams	= Yii::$app->request->queryParams;
		$queryParams['ThChangeRecordSearch']['type'] = AccountChangeType::ACCOUNT_NAME;
		if(Yii::$app->user->can('ad_group'))
		{
			$queryParams['ThChangeRecordSearch']['user_id'] = $this->user_id;
		} elseif(Yii::$app->user->can('ae_group')) {
			$queryParams['ThChangeRecordSearch']['company_id'] = $this->company_id;
		} elseif(Yii::$app->user->can('admin_group')) {
		} else {
			throw new NotFoundHttpException('Please Login!');	
		}
        $dataProvider = $searchModel->search($queryParams);

        return $this->render('name/name-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


	/**
	 *	submit name change
	 */
	public function actionSubmitChange()
	{
		if(Yii::$app->request->isAjax)
		{
			$postParams	= Yii::$app->request->post();
            $change_record_list = !empty($postParams['change_record_list']) ? $postParams['change_record_list'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($change_record_list)
			{
                $error_message_list = [];
			    foreach($change_record_list as $change_record_id)
                {
                    $model = new ChangeNameModel;
                    $model->id	= $change_record_id;
                    if(!$model->changeAccountName())
                        array_push($error_message_list, [$model->id => $model->error_message]);
                }
                if(!$error_message_list)
                {
                    return ['message' => 'success', 'status' => true];
                } else {
                    return ['message' => 'failed', 'status' => false, 'error_message_list' => $error_message_list];
                }
			}
			return ['message' => 'failed', 'status' => false, 'error_message' => 'Unkow Error!'];	
		}
		throw new NotFoundHttpException('Unknow submit change!');
	}


	/**
	 *	reject name change
	 */
	public function actionRejectChange()
	{
		if(Yii::$app->request->isAjax)
		{
			$postParams	= Yii::$app->request->post();
			$change_record_id	= !empty($postParams['change_record_id']) ? $postParams['change_record_id'] : '';
			$reject_reason		= !empty($postParams['reject_reason']) ? $postParams['reject_reason'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($change_record_id)
			{
				$model = new ChangeNameModel;
				$model->id		= $change_record_id;
				$model->reason	= $reject_reason;
				if($model->rejectChange()) return ['message' => 'success', 'status' => true];
			}
			return ['message' => 'failed', 'status' => false];	
		}
		throw new NotFoundHttpException('Unknow Reject change!');
	}


	/**
	 *	view change reason
	 */
	public function actionChangeReason()
	{
		if(Yii::$app->request->isAjax)	
		{
			$postParams	= Yii::$app->request->post();
			$change_record_id	= !empty($postParams['change_record_id']) ? $postParams['change_record_id'] : '';
			$model	= new ChangeNameModel;;
			$reason	= $model->getRejectReason($change_record_id);
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($reason)
			{
				return ['message' => 'success', 'reason' => $reason];
			}
			return ['message' => 'success', 'reason' => $reason];
		}
		throw new NotFoundHttpException('Unknow view change reason!');
	}

	
	/**
	 *	submit name change
	 */
	public function actionSubmitBinding()
	{
		if(Yii::$app->request->isAjax)
		{
			$postParams	= Yii::$app->request->post();
            $binding_record_list = !empty($postParams['binding_record_list']) ? $postParams['binding_record_list'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($binding_record_list)
			{
                $error_message_list = [];
			    foreach($binding_record_list as $binding_record_id)
                {
                    $model = new BindingModel;
                    $model->scenario = 'setAccountInfo';
                    $model->id	= $binding_record_id;
                    if(!$model->submitBindingRecord())
                        array_push($error_message_list, [$model->id => $model->error_message]);
                }
                if(!$error_message_list)
                {
                    return ['message' => 'success', 'status' => true];
                } else {
                    return ['message' => 'failed', 'status' => false, 'error_message_list' => $error_message_list];
                }
			}
			return ['message' => 'failed', 'status' => false, 'error_message' => 'Unknow'];	
		}
		throw new NotFoundHttpException('Unknow submit binding!');
	}


	/**
	 *	reject binding
	 */
	public function actionRejectBinding()
	{
		if(Yii::$app->request->isAjax)
		{
			$postParams	= Yii::$app->request->post();
			$binding_record_id	= !empty($postParams['binding_record_id']) ? $postParams['binding_record_id'] : '';
			$reject_reason		= !empty($postParams['reject_reason']) ? $postParams['reject_reason'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($binding_record_id)
			{
				$model = new BindingModel;
				$model->id		= $binding_record_id;
				$model->reason	= $reject_reason;
				if($model->rejectBinding()) return ['message' => 'success', 'status' => true];
			}
			return ['message' => 'failed', 'status' => false];	
		}
		throw new NotFoundHttpException('Unknow Reject change!');
	}


	/**
	 *	view binding reason
	 */
	public function actionBindingReason()
	{
		if(Yii::$app->request->isAjax)	
		{
			$postParams	= Yii::$app->request->post();
			$binding_record_id	= !empty($postParams['binding_record_id']) ? $postParams['binding_record_id'] : '';
			$model	= new BindingModel;
			$reason	= $model->getRejectReason($binding_record_id);
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($reason)
			{
				return ['message' => 'success', 'reason' => $reason];
			}
			return ['message' => 'success', 'reason' => $reason];
		}
		throw new NotFoundHttpException('Unknow binding reason!');
	}

	
	/**
	 *	submit creditlimit
	 */
	public function actionSubmitCreditLimit()
	{
		if(Yii::$app->request->isAjax)
		{
			$postParams	= Yii::$app->request->post();
			$credit_limit_record_list = !empty($postParams['credit_limit_record_list']) ? $postParams['credit_limit_record_list'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($credit_limit_record_list)
			{
			    $error_message_list = [];
			    foreach($credit_limit_record_list as $credit_limit_record_id)
                {
                    $model = new CreditLimitModel;
                    $model->scenario = 'setAccountInfo';
                    $model->id	= $credit_limit_record_id;
                    if(!$model->submitCreditLimit())
                        array_push($error_message_list, [$model->id => $model->error_message]);
                }
                if(!$error_message_list)
                {
                    return ['message' => 'success', 'status' => true];
                } else {
                    return ['message' => 'failed', 'status' => false, 'error_message_list' => $error_message_list];
                }
			}
			return ['message' => 'failed', 'status' => false, 'error_message' => 'Unknow'];	
		}
		throw new NotFoundHttpException('Unknow submit CreditLimit!');
	}


	/**
	 *	reject creditlimit
	 */
	public function actionRejectCreditLimit()
	{
		if(Yii::$app->request->isAjax)
		{
			$postParams	= Yii::$app->request->post();
			$credit_limit_record_id	= !empty($postParams['credit_limit_record_id']) ? $postParams['credit_limit_record_id'] : '';
			$reject_reason		= !empty($postParams['reject_reason']) ? $postParams['reject_reason'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($credit_limit_record_id)
			{
				$model = new CreditLimitModel;
				$model->id		= $credit_limit_record_id;
				$model->reason	= $reject_reason;
				if($model->rejectCreditLimit()) return ['message' => 'success', 'status' => true];
			}
			return ['message' => 'failed', 'status' => false];	
		}
		throw new NotFoundHttpException('Unknow Reject credit limit!');
	}


	/**
	 *	view creditlimit reason
	 */
	public function actionCreditLimitReason()
	{
		if(Yii::$app->request->isAjax)	
		{
			$postParams	= Yii::$app->request->post();
			$credit_limit_record_id	= !empty($postParams['credit_limit_record_id']) ? $postParams['credit_limit_record_id'] : '';
			$model	= new CreditLimitModel;
			$reason	= $model->getRejectReason($credit_limit_record_id);
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($reason)
			{
				return ['message' => 'success', 'reason' => $reason];
			}
			return ['message' => 'success', 'reason' => $reason];
		}
		throw new NotFoundHttpException('Unknow credit limit reason!');
	}


	/**
	 *	文件上传的操作
	 */
	public function actionUploadFile()
	{
		if(Yii::$app->request->isAjax)
		{
			$requestData = !empty($_FILES) ? $_FILES : '';
			if(array_key_exists('ChangeNameModel', $requestData))
			{
				$model	= new ChangeNameModel();
			} elseif(array_key_exists('BindingModel', $requestData)) {
				$model	= new BindingModel();
			} elseif(array_key_exists('CreditLimitModel', $requestData)) {
				$model	= new CreditLimitModel();
			} else {
				throw New NotFoundHttpException('Unknow Post Data');
			}
			Yii::$app->response->format = Response::FORMAT_JSON;
			$uploadFile = UploadedFile::getInstance($model, 'upload_file');

			if($uploadFile)
			{
				$uploadData	= $model->getUploadData($uploadFile);
				$accountInfoList = $model->getAccountInfoList($uploadData);
				if($accountInfoList) return ['status' => 'true', 'accountInfoList' => $accountInfoList];
			}
			return ['status' => 'false', 'accountInfoList' => []];
		}
	}


	protected function findModel($id)
    {
        if (($model = ThAgencyCreaditlimit::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
