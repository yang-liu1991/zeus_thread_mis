<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-07-26 10:27:16
 */

namespace backend\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use backend\models\user\User;
use backend\models\user\UserModel;
use backend\models\account\ExportModel;
use backend\models\account\EntityModel;
use backend\models\message\MessageModel;
use backend\models\account\AdminManagerModel;
use backend\models\record\ThEntityInfoSearch;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThAgencyBusinessSearch;


class ThreadBaseController extends Controller
{

	public $user_id;
	public $company_id;
	public $messageList;

	/**
	 *	初始化layout
	 */
	public function init()
	{
		if(Yii::$app->user->can('admin_group'))
		{
			$this->layout = '@app/views/layouts/admin-manager.php';
		} elseif(Yii::$app->user->can('ad_group')) {
			$this->layout = '@app/views/layouts/advertiser.php';
		} elseif(Yii::$app->user->can('ae_group')) {
			$this->layout = '@app/views/layouts/account-executive.php';
		} elseif(Yii::$app->user->can('sale_group')) {
			$this->layout = '@app/views/layouts/sales.php';
		}

		$this->user_id		= (UserModel::getLoginInfo()) ? UserModel::getLoginInfo()->id : 0;
		$this->company_id	= (UserModel::getLoginInfo()) ? UserModel::getLoginInfo()->company_id : 0;
		$this->messageList	= MessageModel::getMessage();
	}


	/**
	 *	获取page link
	 *	@params	int	entity id
	 *	@return array
	 */
	public function actionGetPageLink()
	{
		if(Yii::$app->request->isAjax)
		{
			$id = !empty($_POST['id']) ? $_POST['id'] : '';
			$model	= new AdminManagerModel();
			$bussinessInfo	= ThAgencyBusinessSearch::getOneBusinessByCompanyId($this->company_id);
			$promotablePageIds	= ThEntityInfoSearch::findBySql(sprintf("select promotable_page_ids from th_entity_info where id = %d",
				$id))->one();
			$promotableObj	= json_decode($promotablePageIds->promotable_page_ids);
			$promotable_page_ids	= !empty($promotableObj) ? $promotableObj[0] : '';
			$access_token	= !empty($bussinessInfo->access_token) ? $bussinessInfo->access_token : '';
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($promotableObj && $access_token)
			{
				$model->access_token	= $access_token;
				$model->promotable_page_ids	= $promotable_page_ids;
				$link = $model->getPageLink();
				if($link) return ['message' => 'success', 'link' => $link];
			}
			return ['message' => 'failed', 'link' => ''];
		}
	}


	/**
	 *	Facebook广告帐户申请列表
	 */
	public function actionReferExport()
	{
		$searchModel    = new ThAccountInfoSearch();
		$exportModel    = new ExportModel();
		$queryParams    = Yii::$app->request->queryParams;
		$type			= !empty($queryParams['type']) ? $queryParams['type'] : 0;
		$searchParams	= !empty($queryParams['ThAccountInfoSearch']) ? $queryParams['ThAccountInfoSearch'] : [];
		if($type == ThAccountInfoSearch::PARTITIONED_CREDIT || $type == ThAccountInfoSearch::DIRECT_CREDIT)
			$queryParams['ThAccountInfoSearch']['type'] = $type;
		$dataProvider = $searchModel->search($queryParams);
		$dataProvider->setPagination(false);
		$objectPHPExcel	= $exportModel->buildExcelObj($dataProvider->getModels());

		if($objectPHPExcel) 
			$exportModel->downloadExcelFile($objectPHPExcel);
	}


	/**
	 * 返回统一封装的error message
	 */
	public function getErrorMessage($errors) {
		$errorMsgs = "";
		if(!empty($errors)) {
			foreach($errors as $key=>$error) {
				foreach($error as $v) {
					$errorMsgs .= $v . '<br/>';
				}
			}
		}
		return $errorMsgs;
	}

	/**
	 *	获取开户操作的异常信息
	 *	@params	int	th_account_info id 主键
	 *	@return array
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
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
