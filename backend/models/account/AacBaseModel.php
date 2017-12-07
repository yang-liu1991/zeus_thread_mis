<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-12-20 15:17:56
 * Desc: 这是调用AAC接口的一个基类
 */

namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use common\models\RequestApi;
use backend\models\ThreadBaseModel;
use backend\models\account\FbTimezoneIds;
use backend\models\record\ThEntityInfo;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThAccountInfoSearch;

class AacBaseModel extends ThreadBaseModel
{
	/* 开户请求API */
	const CREATING_CREATION_API = 'https://graph.facebook.com/v2.9/%s/adaccountcreationrequests';
	/* 更新开户请求API */
	const UPDATE_CREATION_API	= 'https://graph.facebook.com/v2.9/%s';
	/* 删除开户请求API */
	const DELETE_CREATION_API	= 'https://graph.facebook.com/v2.9';
	/* 获取extended_credit_id API */
	const EXTENDED_CREDITID_API = 'https://graph.facebook.com/%s/extendedcredits/';
	/* 获取businesses_id */
	const BUSINESSES_ID_API		= 'https://graph.facebook.com/v2.9/me/businesses';
	/* 获取page link */
	const PAGE_LINK_API			= 'https://graph.facebook.com/v2.9/%s';

	public $id;
	public $entity_id;
	/* 开户参数 */
	public $access_token;
	public $extended_credit_id;
	public $ad_accounts_info;
	public $business_registration;
	public $business_registration_id;
	public $advertiser_business_id;
	public $vertical;
	public $subvertical;
	public $is_smb;
	public $official_website_url;
	public $promotable_page_ids;
	public $promotable_page_urls;
	public $promotable_app_ids;
	public $promotable_urls;
	public $english_legal_entity_name;
	public $chinese_legal_entity_name;
	public $address_in_chinese;
	public $address_in_english;
	public $business_id;
	public $request_id;
	public $referral;
	/* reseller开户时为空，为agency开户时填任意的BM id */
	public $planning_agency_business_id;
	public $contact;
	public $status;
	/*0为非PC，1为PC*/
	public $type;
	public $additional_comment;
	public $account_createdtime;
	public $account_updatedtime;

	public function rules()
	{
		return [
			[['access_token', 'extended_credit_id', 'ad_accounts_info', 'business_registration', 'business_registration_id', 'vertical', 'subvertical', 'official_website_url', 'english_legal_entity_name', 'chinese_legal_entity_name', 'address_in_chinese', 'address_in_english', 'business_id', 'contact'], 'required', 'message' => '{attributes} 不能为空！'],
			[['promotable_page_ids', 'promotable_page_urls'], 'promotablePageValidate', 'skipOnEmpty' => false],
			[['promotable_app_ids', 'promotable_urls', 'business_id', 'promotable_page_urls', 'advertiser_business_id', 'additional_comment', 'type', 'is_smb'], 'safe']
		];
	}

		/**
	 *	Promotable Page Validate
	 *	Promotable Page Ids 和Promotable Page Urls必须有一项
	 */
	public function promotablePageValidate()
	{
		if(!$this->promotable_page_ids && !$this->promotable_page_urls)
		{
			$this->addError('promotable_page_ids', '推广Page Ids和推广Page Urls至少选一项！');
			$this->addError('promotable_page_urls', '推广Page Ids和推广Page Urls至少选一项！');
		}
	}

	/**
	 *	获取timezone id
	 *	@params	str	timezone
	 */
	protected function getTimezoneId($timezone)
	{
		return FbTimezoneIds::getTimezoneId($timezone);
	}

	/**
	 *	获取promotable_urls
	 *	@params	str
	 */
	protected function getPromotableUrls($promotable_urls)
	{
		$promotable_urls = json_decode($promotable_urls, true);
		$promotable_urls = $promotable_urls["normal"];
		return $promotable_urls;
	}


	/**
	 *	获取需要上传的图片
	 *	@params	str
	 *	@return jpg
	 */
	protected function getBusinessRegistrtion($url)
	{
		try {
			$basePath	= Yii::$app->basePath;
			$tmpDir		= $basePath."/runtime/tmpdir/";
			if(!file_exists($tmpDir)) mkdir($tmpDir);
			$baseName	= md5(basename($url)).".jpeg";
			$response	= RequestApi::requestGetImage($url, $tmpDir.$baseName);
			Yii::info(sprintf("[getBusinessRegistrtion] Success, url:%s, basename:%s, response:%s",
				$url, $baseName, $response));
			if($response) return $tmpDir.$baseName;
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf("[getBusinessRegistrtion] Exception, url:%s, reason:%s",
				$url, $message->getMessage()));
		}
	}

	/**
	 *	根据userid 获取 email
	 *	@params int userid
	 *	@return str email
	 */
	protected function getEmailById($userId)
	{
		try {
			$sql = sprintf("select email from zeus_user where id = %d", $userId);
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();
			if($results)	return $results;
			throw new Exception(sprintf('getEmailById Error, There is no data, userid:%d!', $userId));
		} catch(Exception $message) {
			Yii::error(sprintf("[getEmailById] Exception, userId:%d, reason:%s", $userId, $message->getMessage()));
			return false;
		}
	}

	/**
	 *	根据id获取entity_id和created_at
	 *	@params	int	id
	 *	@return int entity_id
	 */
	public function getConstraintDataById($id)
	{
		try {
			$sql = sprintf("select entity_id, created_at from th_account_info where id = %d", $id);
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();
			if($results)	return $results;
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf("[getConstraintDataById] Exception, Id:%d, reason:%s", $id, $message->getMessage()));
			return false;
		}
	}


	/*
	 *	根据id获取businessInfo，包括business_agency_id和business_id
	 *	@params	int	id
	 *	@return
	 */
	public function getBusinessInfo($id)
	{
		try {
			$sql = sprintf("select business_agency_id, business_id from th_account_info where id = %d", $id);
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();
			if($results)	return $results;
			throw new Exception(sprintf('getBusinessInfo Error, There is no data, id:%s!', $id));
		} catch(Exception $message) {
			Yii::error(sprintf("[getAgencyBusinessId] Exception, Id:%d, reason:%s", $id, $message->getMessage()));
			return false;
		}
	}


	/*
	 *	根据business_id获取授权access_token
	 */
	public function getAccessToken()
	{
		try {
			$sql = sprintf("select access_token from th_agency_business where business_id = %d", $this->business_id);
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();
			if($results)	return $results["access_token"];
			throw new Exception('getAccessToken Error, There is no data!');
		} catch(Exception $message) {
			Yii::error(sprintf("[getAccessToken] Exception, Id:%d, reason:%s", $this->id, $message->getMessage()));
			return false;
		}
	}
	
	
	/*
	 *	根据th_account_info主建id获取entity comment
	 *	@return string
	 */
	public function getEntityComment()
	{
		try {
			$sql = sprintf("select e.comment from `th_entity_info` as e left join `th_account_info` as a on e.id = a.entity_id where a.id = %d", $this->id);
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();
			if($results)	return $results["comment"];
			throw new Exception('getEntityComment Error, There is no data!');
		} catch(Exception $message) {
			Yii::error(sprintf("[getEntityComment] Exception, Id:%d, reason:%s", $this->id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	获取extended_credit_id
	 *	@return
	 */
	protected function getExtendedCreditId()
	{
		try {
			$url = sprintf(self::EXTENDED_CREDITID_API, $this->business_id);
			$params = ['access_token' => $this->access_token];
			$encodeUrl = sprintf('%s?%s', $url, http_build_query($params));
			$response = RequestApi::requestGet($encodeUrl);
			Yii::info(sprintf("[getExtendedCreditId] Success, businessesId:%s, requestUrl:%s, response:%s",
				$this->business_id, $encodeUrl, $response));
			$result = json_decode($response);
			$extended_credit_id = '';
			if(property_exists($result, 'data') && $result->data)
			{
				foreach($result->data as $data) $extended_credit_id = $data->id;
			}

			if($extended_credit_id) return $extended_credit_id;
			throw new Exception(sprintf('getExtendedCreditId Error, Please confirm whether have partitioned credit! Business_id:%s',
				$this->business_id));
		} catch(Exception $message) {
			Yii::error(sprintf("[getExtendedCreditId] Exception, businessesId:%s, reason:%s",
				$this->business_id, $message->getMessage()));
			return false;
		}
	}

	/**
	 *	获取page link
	 *	@params	str	pageid
	 *	@return
	 */
	public function getPageLink()
	{
		try {
			$url = sprintf(self::PAGE_LINK_API, $this->promotable_page_ids);
			$params	= ['access_token' => $this->access_token, 'fields' => 'link'];
			$encodeUrl = sprintf('%s?%s', $url, http_build_query($params));
			$response = RequestApi::requestGet($encodeUrl);
			Yii::info(sprintf("[getPageLink] Success, promotablePageIds:%s, requestUrl:%s, response:%s",
				$this->promotable_page_ids, $encodeUrl, $response));
			$result = json_decode($response);
			if(!empty($result->link)) return $result->link;
			throw new Exception('getPageLink Error!');
		} catch(Exception $message) {
			Yii::error(sprintf("[getPageLink] Exception, promotablePageIds:%s, reason:%s",
				$this->promotable_page_ids, $message->getMessage()));
			return false;
		}
	}

	

	/**
	 *	自定义更新开户信息
	 *	@params	str	sql
	 *	@return
	 */
	protected function updateAccountInfoBySql($sql)
	{
		try {
			$connections	= Yii::$app->db;
			$command		= $connections->createCommand($sql);
			$results		= $command->execute();
			if($results) return $results;
			throw new Exception('updateAccountInfoBySql Error!');
		} catch(Exception $message) {
			Yii::error(sprintf("[updateAccountInfoBySql] Exception, sql:%s, reason:%s", $sql, $message->getMessage()));
			return false;
		}
	}

}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
