<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-08-19 17:28:20
 */

namespace console\controllers;

use Yii;
use yii\base\Exception;
use console\models\AccountSpendModel;
use console\models\SpendReportModel;
use console\models\PromotableUrlsModel;
use console\models\AccountsStatusModel;
use console\models\UserPermissionsModel;
use console\controllers\ConsoleBaseController;

class AccountsCollectController extends ConsoleBaseController
{
	/**
	 *	检测推广帐户的异常链接
	 */
	public function actionPromotableurlsCheck()
	{
		$model = new PromotableUrlsModel();
		$model->accountIds = $model->getAdAccount();
		foreach($model->accountIds as $accountId)
		{
			print_r($accountId);
			if($accountId["fbaccount_id"])
			{
				$model->init();
				$model->accountId = $accountId['fbaccount_id'];
				if(!$model->run()) continue;
			}
		}

		echo 'Run Success!';
	}

	

	/**
	 *	检测广告开户，自动更新状态
	 */
	public function actionAccountsStatus()
	{
		$model = new AccountsStatusModel();
		$requestInfo = $model->getAccountRequestId();
		foreach($requestInfo as $request)
		{
			$requestId	= $request["request_id"];
			$entityId	= $request['entity_id'];
			/* 如果request_id不存在则直接continue */
			if(!$requestId) continue;
			$adAccountInfo		= $model->getAccountInfo($requestId);
			if($adAccountInfo)
			{
				$model->initAttributes();
				if(!$model->setAttributes($adAccountInfo)) continue;
				/* 邮件通知 */
				if(!$model->findSendEmail($requestId, $model->status))
				{
					$model->sendingEmail($requestId);
				}

				if($model->requestChangeReasons)
				{
					$data	= ['status' => $model->status, 'reasons' => $model->requestChangeReasons];
					$updateResult = $model->updateAccountData($requestId, $data);
					continue;
				}

				if($model->disapprovalReasons)
				{
					$data	= ['status' => $model->status, 'reasons' => $model->disapprovalReasons];
					$updateResult = $model->updateAccountData($requestId, $data);
					continue;
				}

				if($model->adAccounts)
				{
					$model->updateEntityStatus($entityId);
					$adAccountsData	= $model->adAccounts->data;
					foreach($adAccountsData as $adAccount)
					{
						$fbaccount_id	= $adAccount->account_id;
						$fbaccount_name	= $adAccount->name;
						$data	= ['fbaccount_id' => $fbaccount_id, 'status' => $model->status, 'reasons' => ''];
						$updateResult = $model->updateAccountData($requestId, $data, $fbaccount_name);
						$userPermissionsModel = new UserPermissionsModel();
						$checkResult = $userPermissionsModel->checkUserPermissions($fbaccount_id);
						if(!$checkResult) $addUserPermissionsResult = $userPermissionsModel->addUserPermissions($fbaccount_id);
						if($updateResult) printf("[%s]:[%s]", $requestId, json_encode($data));
					}
				} else {
					$data	= ['status' => $model->status, 'reasons' => ''];
					$updateResult = $model->updateAccountData($requestId, $data);
					if($updateResult) printf("[%s]:[%s]", $requestId, json_encode($data));
				}
			} 
		}

		echo 'Run Success';
	}


	/**
	 *	按天级获取消耗数据
	 */
	public function actionSpendReport()
	{
		$model = new SpendReportModel();
		$accountDatas = $model->getAccountData();
		if($accountDatas)
		{
			foreach($accountDatas as $accountData)
			{
				$account_id		= $accountData['fbaccount_id'];
				$business_id	= $accountData['business_id'];
				if(!$account_id) continue;
				$result = $model->run($account_id, $business_id);
				if($result && is_object($result))
				{
					printf("Account Id:%s, saveResult:%s\n", $account_id, json_encode($result));
				} else {
					printf("Account Id:%s, saveResult:%s\n", $account_id, 'Success');
				}
			}	
		} else {
			throw new Exception('spend report error, Not found account datas!');
		}
	}


	/**
	 *	实时获取额度、消耗、余额
	 */
	public function actionGetAccountSpend()
	{
		$model = new AccountSpendModel();
		$accountDatas = $model->getAccountData();
		if($accountDatas)
		{
			foreach($accountDatas as $accountData)
			{
				$account_id		= $accountData['fbaccount_id'];
				$business_id	= $accountData['business_id'];
				if(!$account_id) continue;
				$result = $model->run($account_id, $business_id);
				if(!$result)
				{
					printf("Account Id:%s, saveResult:%s\n", $account_id, 'Failed');
				} else {
					printf("Account Id:%s, saveResult:%s\n", $account_id, 'Success');
				}
			}	
		} else {
			throw new Exception('get account spend error, Not found account datas!');
		}

	}


	public function actionSendmail()
	{
		\common\models\SendMail::sendTextEmail(['liuyang@domob.cn', 'uselinux@sina.com'], 'test', 'eeeeee');
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
