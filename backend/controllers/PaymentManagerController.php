<?php

namespace backend\controllers;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use backend\models\user\UserModel;
use backend\models\payment\AmountModel;
use backend\models\payment\PaymentModel;
use backend\models\payment\PayExportModel;
use backend\models\payment\DaysExportModel;
use backend\models\payment\CompanyExportModel;
use backend\controllers\ThreadBaseController;
use backend\models\record\ThSpendReportSearch;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThPaymentHistorySearch;
use backend\models\record\ThEntityInfoSearch;


/**
 * PaymentManagerController implements the CRUD actions for ThPayment model.
 */
class PaymentManagerController extends ThreadBaseController
{
	public $enableCsrfValidation = false;
	
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
						'actions'	=> ['pay-list', 'pay-create', 'pay-update', 'get-pay-info', 'get-pay-history', 'get-company-name', 'pay-export', 'days-amount-list', 'get-days-account-amount', 'days-amount-export', 'company-amount-list', 'company-amount-export', 'get-company-account-amount'],
						'allow'		=> true,
						'roles'		=> ['ae_group'],
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
     * Lists all ThPayment models.
     * @return mixed
     */
    public function actionPayList()
    {
        $searchModel = new ThAccountInfoSearch();
		$queryParams = Yii::$app->request->queryParams;
		$queryParams['ThAccountInfoSearch']['company_id'] = $this->company_id;
		$queryParams['ThAccountInfoSearch']['status'] = ThAccountInfoSearch::getAccountStatus()['APPROVED'];

		$dataProvider = $searchModel->search($queryParams);
		
		return $this->render('pay-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	
	/**
	 *	Ajax 提交添加付款信息
     */
    public function actionPayCreate()
    {
		if(Yii::$app->request->isAjax)
		{
			$model = new PaymentModel();
			$model->setUserAttributes();
			$model->scenario = 'update_payment';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($model->load(Yii::$app->request->post()) && $model->validate())
			{
				if($model->paymentUpdate())
					return ['status' => true, 'error_message' => ''];
			}
			return ['status' => false, 'error_message' => $model->getErrors()];
		} else {
			throw new NotFoundHttpException('Request not found!'); 
		}
	}


    /**
	 *  Ajax 获取付款信息
     */
    public function actionGetPayInfo()
    {
		if(Yii::$app->request->isAjax)
		{
			$model = new PaymentModel();
			Yii::$app->response->format = Response::FORMAT_JSON;
			$account_id	= !empty($_POST['account_id']) ? $_POST['account_id'] : '';
			if($account_id)
			{
				$model->account_id = $account_id;
				if($model->getPaymentAttributes())
					return ['status' => true, 'error_message' => '', 'pay_info' => $model->attributes];	
			}
			return ['status' => false, 'error_message' => '获取付款信息错误！', 'pay_info' => ''];
		} else {
			throw new NotFoundHttpException('Request not found!'); 
		}	
    }


    /**
	 *  Ajax 获取历史编辑信息
     */
    public function actionGetPayHistory()
    {
		if(Yii::$app->request->isAjax)
		{
			$model = new PaymentModel();
			Yii::$app->response->format = Response::FORMAT_JSON;
			$account_id	= !empty($_POST['account_id']) ? $_POST['account_id'] : '';
			if($account_id)
			{	
				$model->account_id = $account_id;
				$model->setUserAttributes();
				$paymentHistoryInfo = $model->formatPaymentHistory();
				if($paymentHistoryInfo)	
					return ['status' => true, 'error_message' => '', 'history_info' => $paymentHistoryInfo];	
				return ['status' => true, 'error_message' => '', 'history_info' => []];	
			}
			return ['status' => false, 'error_message' => '获取付款信息错误！', 'pay_info' => ''];
		} else {
			throw new NotFoundHttpException('Request not found!'); 
		}
    }


	/**
	 *	ajax 导出帐户付款信息数据
	 */
	public function actionPayExport()
	{
		$searchModel = new ThAccountInfoSearch();
		$exportModel = new PayExportModel();
		$queryParams = Yii::$app->request->queryParams;
		$queryParams['ThAccountInfoSearch']['company_id'] = $this->company_id;
		$queryParams['ThAccountInfoSearch']['status'] = ThAccountInfoSearch::getAccountStatus()['APPROVED'];
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
	 *	Ajax 获取广告主名称
	 */
	public function actionGetCompanyName($name_zh=Null, $id=Null)
	{
		if(Yii::$app->request->isAjax)
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			$out = ['results' => ['id' => '', 'text' => '']];
		    if (!is_null($name_zh)) {
				$query = new Query;
				$query->select('id, name_zh as text')
				->from('th_entity_info')
				->where(['like', 'name_zh', $name_zh])
				->limit(20);
				$command = $query->createCommand();
				$data = $command->queryAll();
				$out['results'] = array_values($data);
			}
    		return $out;
		}
	}

	
	/**
     * Lists all Amount.
     * @return mixed
     */
    public function actionDaysAmountList()
    {
		$model = new AmountModel();
        $searchModel = new ThAccountInfoSearch();
		$spendReportModel = new ThSpendReportSearch();
		$queryParams = Yii::$app->request->queryParams;
		$queryParams['ThAccountInfoSearch']['company_id'] = $this->company_id;
		$queryParams['ThAccountInfoSearch']['status'] = ThAccountInfoSearch::getAccountStatus()['APPROVED'];
		$spendReportModel->date_start	= !empty($queryParams['ThSpendReportSearch']) ? 
			$queryParams['ThSpendReportSearch']['date_start'] : '';
		$spendReportModel->date_stop	= !empty($queryParams['ThSpendReportSearch']) ? 
			$queryParams['ThSpendReportSearch']['date_stop'] : '';
		$dataProvider = $searchModel->search($queryParams);
		
		return $this->render('days-amount-list', [
			'spendReportModel' => $spendReportModel,
            'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
        ]);
	}


	/**
	 *	ajax获取report 数据
	 */
	public function actionGetDaysAccountAmount()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new AmountModel();
			$searchModel	= new ThSpendReportSearch();
			$queryParams	= Yii::$app->request->queryParams;
			$model->account_id	= !empty($_POST['account_id']) ? $_POST['account_id'] : '';
			$queryParams['ThSpendReportSearch']['account_id']	= $model->account_id;
			$queryParams['ThSpendReportSearch']['date_start']	= !empty($_POST['date_start']) ? $_POST['date_start'] : '';
			$queryParams['ThSpendReportSearch']['date_stop']	= !empty($_POST['date_stop']) ? $_POST['date_stop'] : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			$dataProvider = $searchModel->search($queryParams);
			$amountInfo = $model->formatAmountInfo($dataProvider, $queryParams);
			return ['status' => true, 'amount_info' => $amountInfo];
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}

	
	/**
	 *	ajax 导出帐户金额数据
	 */
	public function actionDaysAmountExport()
	{
		$accountInfoSearchModel = new ThAccountInfoSearch();
		$exportModel = new DaysExportModel();
		$queryParams = Yii::$app->request->queryParams;
		$queryParams['ThAccountInfoSearch']['company_id'] = $this->company_id;
		$queryParams['ThAccountInfoSearch']['status'] = ThAccountInfoSearch::getAccountStatus()['APPROVED'];
		$accountDataProvider		= $accountInfoSearchModel->search($queryParams);
		$accountDataProvider->setPagination(false);
		$objectPHPExcel	= $exportModel->buildExcelObj($accountDataProvider->getModels(), $queryParams);
		if($objectPHPExcel)
		{
			$exportModel->downloadExcelFile($objectPHPExcel);
			return true;
		} else {
			throw new NotFoundHttpException('Export Exception!');
		}
	}


	/**
	 *	广告主付款信息
	 */
	public function actionCompanyAmountList()
	{
        $searchModel = new ThEntityInfoSearch();
		$spendReportModel = new ThSpendReportSearch();
		$queryParams = Yii::$app->request->queryParams;
		$entityIdObjs = ThAccountInfoSearch::findBySql(sprintf(
			'select entity_id from th_account_info where company_id = %d', 
			$this->company_id))->all();
		foreach($entityIdObjs as $entityIdObj) $entityIds[] = $entityIdObj->entity_id;
		$searchModel->entity_ids	= $entityIds;
		$spendReportModel->date_start	= !empty($queryParams['ThSpendReportSearch']) ? 
			$queryParams['ThSpendReportSearch']['date_start'] : '';
		$spendReportModel->date_stop	= !empty($queryParams['ThSpendReportSearch']) ? 
			$queryParams['ThSpendReportSearch']['date_stop'] : '';
		$dataProvider = $searchModel->search($queryParams, [ThEntityInfoSearch::AUDIT_STATUS_SUCCESS]);

		return $this->render('company-amount-list', [
			'spendReportModel' => $spendReportModel,
            'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
        ]);
	
	}
	
	
	/**
	 *	ajax获取report 数据
	 *	根据entity_id，获取公司级别的account 数据
	 */
	public function actionGetCompanyAccountAmount()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new AmountModel();
			$searchModel	= new ThSpendReportSearch();
			$postParams	= Yii::$app->request->post();
			$model->setAttributes($postParams);
			Yii::$app->response->format = Response::FORMAT_JSON;
			$companyAmountInfo = $model->formatCompanyAmountInfo();
			return ['status' => true, 'company_amount_info' => $companyAmountInfo];
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}


	/**
	 *	导出广告主金额数据
	 */
	public function actionCompanyAmountExport() 
	{
		$exportModel = new CompanyExportModel();
		$searchModel = new ThEntityInfoSearch();
		$queryParams = Yii::$app->request->queryParams;
		$entityIdObjs = ThAccountInfoSearch::findBySql(sprintf(
			'select entity_id from th_account_info where company_id = %d', 
			$this->company_id))->all();
		foreach($entityIdObjs as $entityIdObj) $entityIds[] = $entityIdObj->entity_id;
		$searchModel->entity_ids	= $entityIds;
		$entityDataProvider = $searchModel->search($queryParams, [ThEntityInfoSearch::AUDIT_STATUS_SUCCESS]);
		$entityDataProvider->setPagination(false);
		$objectPHPExcel = $exportModel->buildExcelObj($entityDataProvider->getModels(), $queryParams);
		if($objectPHPExcel)
		{
			$exportModel->downloadExcelFile($objectPHPExcel);
			return true;
		} else {
			throw new NotFoundHttpException('Export Exception!');
		}
	}


    /**
     * Finds the ThPayment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ThPayment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ThPayment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
