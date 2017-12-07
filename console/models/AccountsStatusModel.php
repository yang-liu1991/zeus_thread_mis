<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-09-02 14:52:52
 */

namespace console\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\HttpException;
use common\models\RequestApi;
use console\models\ConsoleBaseModel;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThNotificationRecord;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThEntityInfoSearch;


class AccountsStatusModel extends ConsoleBaseModel 
{

	/* 读取开户状态API */
	const READING_CREATION_API	= 'https://graph.facebook.com/v2.10/%s';
	/* 读取account id API */
	const READING_ACCOUNTID_API	= 'https://graph.facebook.com/v2.10/%s';

	public $id;
	public $requestId;
	public $contact;
	public $nameZh;
	public $promotableUrls;
	public $requestChangeReasons;
	public $disapprovalReasons;
	public $adAccounts;
	public $adAccountsInfo;
	public $status;
	public $accessToken;


	/**
	 *	初始化所有信息的值
	 */
	public function initAttributes()
	{
		$this->requestId			= '';
		$this->nameZh				= '';
		$this->contact				= '';
		$this->promotableUrls		= '';
		$this->requestChangeReasons	= '';
		$this->disapprovalReasons	= '';
		$this->adAccounts			= '';
		$this->adAccountsInfo		= '';
		$this->status				= '';
		$this->accessToken			= '';
	}


	/**
	 *	根据response赋值
	 *	@params	obj	response
	 */
	public function setAttributes($result)
	{
		try {
			$this->nameZh	= !empty($result->chinese_legal_entity_name) ? $result->chinese_legal_entity_name : '';
			$this->contact	= !empty($result->contact) ? $result->contact : '';
			$this->promotableUrls	= !empty($result->promotable_urls) ? $result->promotable_urls : '';
			$this->adAccountsInfo	= !empty($result->ad_accounts_info) ? $result->ad_accounts_info : '';
			$this->status	= !empty($result->status) ? strtoupper($result->status) : '';
			if($this->status == '') throw new Exception('Unknow stats!');
			if($this->status == ThAccountInfoSearch::ACCOUNT_STATUS_REQUESTED_CHANGE)
				$this->requestChangeReasons	= !empty($result->request_change_reasons) ? $result->request_change_reasons : '';
			if($this->status == ThAccountInfoSearch::ACCOUNT_STATUS_DISAPPROVED)
				$this->disapprovalReasons	= !empty($result->disapproval_reasons) ? $result->disapproval_reasons : '';
			if($this->status == ThAccountInfoSearch::ACCOUNT_STATUS_APPROVED)
				$this->adAccounts	= !empty($result->adaccounts) ? $result->adaccounts : '';
			return true;
		} catch(Exception $message) {
			Yii::error(sprintf('[setAttributes] Exception, result:%s, reason:%s', json_encode($result), $message->getMessage()));
			return false;
		}	
	}


	/*
	 *	根据business_id获取授权access_token
	 */
	private function getAccessToken()
	{
		try {
			$sql = sprintf("select access_token from th_agency_business where business_id = (
				select business_id from th_account_info where request_id = %s limit 1);", 
				$this->requestId);
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();
			if($results)	return $results["access_token"];
			throw new Exception('getAccessToken Failed!');
		} catch(Exception $message) {
			Yii::info(sprintf("[getAccessToken] Failed, RequestId:%d, reason:%s", $this->requestId, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	获取request id
	 *	@return
	 */
	public function getAccountRequestId()
	{
		try {
			$sql = "select distinct(request_id), entity_id from th_account_info where id >= 2592 and request_id is not null and fbaccount_id is null and status <= 4;";
			$command	= $this->getDbConnection()->createCommand($sql);
			$result		= $command->queryAll();
			if($result) return $result;
			return false;
		} catch(Exception $message) {
			Yii::info(sprintf("[getAccountRequestId] Exception, sql:%s, reason:%s", 
				$sql, $message->getMessage));
			return false;
		}
	}


	/**
	 *	更新实体信息的状态，只有开户成功，即实体信息审核成功
	 *	@params	int	$entity_id
	 *	@return bool
	 */
	public function updateEntityStatus($id)
	{
		try {
			$command    = $this->getDbConnection()->createCommand()->update('th_entity_info', 
				['audit_status' => ThEntityInfoSearch::AUDIT_STATUS_SUCCESS],
				sprintf('id = %d', $id)
			);
			if($command->execute()) return true;
			throw new Exception('updateEntityStatus Error!');
		} catch(Exception $message) {
			Yii::info(sprintf("[updateEntityStatus] Exception, entity_id:%s, reason:%s",
				$id, $message->getMessage()));
		}
	}


	/**
	 *	检测开户状态
	 *	@params	int	requestId
	 */
	public function getAccountInfo($requestId)
	{
		try {
			$this->requestId	= $requestId;
			$this->accessToken	= $this->getAccessToken($requestId);
			if(!$this->accessToken) throw new Exception('getAccessToken Failed!');
			$url = sprintf(self::READING_CREATION_API, $requestId);

			$params	= [
				'access_token'	=> $this->accessToken,
				'fields'	=> 'id,status,adaccounts{name,account_id},appeal_reason,disapproval_reasons,request_change_reasons,ad_accounts_info,contact,promotable_urls,chinese_legal_entity_name'
				];
			$encodeUrl = sprintf('%s?%s', $url, http_build_query($params));
			$response = RequestApi::requestGet($encodeUrl);
			if(!$response)
				throw new HttpException(500, 'The requested Item could not be found.'); 
			Yii::info(sprintf("[getAccountInfo] Success, requestUrl:%s, requestId:%s, response:%s",
				$encodeUrl, $requestId, $response));
			$result	= json_decode($response);
			if($result) return $result;
			throw new Exception('getAccountInfo Failed!');
		} catch(HttpException $message) {
			Yii::error(sprintf("[getAccountInfo] Failed, RequestId:%s, accessToken:%s, response:%s", 
				$requestId, $this->accessToken, $response));
			return false;
		} catch(Exception $message) {
			Yii::info(sprintf("[getAccountStatus] Failed, RequestId:%d, accessToken:%s, reason:%s", 
				$requestId, $this->accessToken, $message->getMessage()));
			return false;
		}
	}



	/**
	 *	更新开户信息
	 *	@params	int	$requestId
	 *	@params	int	$data
	 *	@retur	bool
	 */
	public function updateAccountData($requestId, $data, $fbaccount_name=null)
	{
		if(!$data['status']) return false;
		$status = ThAccountInfoSearch::getAccountStatus()[$data['status']];
		$reasons = !empty($data['reasons']) ? json_encode($data['reasons']) : Null;
		if(!$fbaccount_name)
		{
			$result = ThAccountInfo::updateAll(["status" => $status, 'reasons' => $reasons],
			'request_id = :request_id', [":request_id" => $requestId]);
		} else {
			$result = ThAccountInfo::updateAll(["status" => $status, "fbaccount_id" => $data['fbaccount_id']],
				'request_id = :request_id and fbaccount_name = :fbaccount_name', 
				[":request_id" => $requestId, ":fbaccount_name" => $fbaccount_name]);
		}	
		Yii::info(sprintf("[updateAccountData] Info  request_id:%s, data:%s, result:%s",
			$requestId, json_encode($data), $result));
		if($result) return true;
		return false;
	}


	/**
	 *	格式化email content
	 *	@params	str	request_id
	 *	return str	content
	 */
	private function buildEmailContent($request_id)
	{
		$content = '';
        $link = !empty($this->promotableUrls) ? implode(', ', $this->promotableUrls) : '';
		/* 如果帐户申请成功 */
		if(property_exists($this->adAccounts, 'data') && $this->status == 'APPROVED')
		{

			foreach($this->adAccounts->data as $adAccount)
			{
				$content .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
					$adAccount->account_id, $this->nameZh, $link, $this->status, Null);
			}
		/* 如果帐户申请失败 */
		} elseif($this->adAccountsInfo) {
			$reason = !empty($this->requestChangeReasons) ? $this->requestChangeReasons : $this->disapprovalReasons;
			$reason = !empty($reason) ? json_encode($reason) : '';
			foreach($this->adAccountsInfo as $adAccountInfo)
			{
				$content .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
					Null, $this->nameZh, $link, $this->status, $reason);
			}
		}
		return $content;
	}


	/**
	 *	获取邮件收件人，如果当时填写的不是邮箱的话，发送到注册人的邮箱中
	 *	@params	obj	contact
	 *	return str
	 */
	private function getEmailSendTo($contact)
	{
		if(filter_var($contact->name, FILTER_VALIDATE_EMAIL))	return $contact->name;
		return $contact->email;
	}


	/**
	 *	判断此request_id的相应状态是否已经发送过邮件提醒
	 *	@params	str	request_id
	 *	@params	str	status
	 *	return bool
	 */
	public function findSendEmail($request_id, $status)
	{
		try {
			$status = ThAccountInfoSearch::getAccountStatus()[$status];
			$model = ThNotificationRecord::find()->where(['request_id' => $request_id, 'status' => $status]);
			if($model->all()) return true;
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf('[findSendEmail] Exception, request_id:%s, status:%s, reason:%s',
				$request_id, $status, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	保存发送邮件的记录
	 *	@params	str	request_id
	 *	@params	str	status
	 *	return bool
	 */
	private function saveSendEmail($request_id, $status)
	{
		try {
			$status = ThAccountInfoSearch::getAccountStatus()[$status];
			$model = new ThNotificationRecord();
			$model->request_id	= $request_id;
			$model->status		= $status;
			if($model->save()) return true;
			throw new Exception('saveSendEmail Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[saveSendEmail] Exception, request_id:%s, status:%s, reason:%s',
				$request_id, $status, $message->getMessage()));
			return false;
		}	
	}


	/**
	 *	发送邮件通知的方法
	 *	@params	int	$request_id
	 *	return bool
	 */
	public function sendingEmail($request_id)
	{
		try {
			$content		= $this->buildEmailContent($request_id);
			$sendEmailTo	= $this->getEmailSendTo($this->contact);
			if($this->saveSendEmail($request_id, $this->status))
			{
				return Yii::$app->mailer->compose(['html' => 'accountStatusChange-html'], ['content' => $content])
					->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
					->setTo($sendEmailTo)
					->setSubject('Facebook开户信息更新通知')
					->send();
			}
			throw new Exception('sendingEmail Error!');
		} catch(\Swift_TransportException $message) {
			Yii::error(sprintf('[sendingEmail] Swift_TransportException, request_id:%s, reason:%s',
				$request_id, $message->getMessage()));
			return false;
		} catch(\Swift_RfcComplianceException $message) {
			Yii::error(sprintf('[sendingEmail] Swift_RfcComplianceException, request_id:%s, reason:%s',
				$request_id, $message->getMessage()));
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf('[sendingEmail] Exception, request_id:%s, reason:%s',
				$request_id, $message->getMessage()));
			return false;
		}
	}
}
# vim: set noexpandtab ts=4 sts=4 sw=4 :
