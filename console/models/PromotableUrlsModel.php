<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-08-19 16:24:12
 */
namespace console\models;

use Yii;
use yii\base\Model;
use yii\web\HttpException;
use linslin\yii2\curl;
use console\models\ConsoleBaseModel;


class PromotableUrlsModel extends ConsoleBaseModel
{
	/* 获取帐号信息的接口 */
	const APIURL = 'https://graph.facebook.com/v2.10/act_%s/adsets';

	public $accountIds;
	public $accountId;

	/* url */
	public $adWebsiteUrlNormal;
	public $adWebsiteUrlAbNormal;
	public $adPromotedObjectUrls;

	public function rules()
	{
		return [];
	}
	
	public function init()
	{
		$this->adWebsiteUrlNormal	= [];
		$this->adWebsiteUrlAbNormal	= [];
		$this->adPromotedObjectUrls	= [];
	}


	/**
	 *	获取accountId
	 *	@ return result
	 */
	public function getAdAccount()
	{
		try {
			$sql = "select fbaccount_id from th_account_info";
			$command	= $this->getDbConnection()->createCommand($sql);
			$result		= $command->queryAll();

			return $result;
		} catch(Exception $message) {
			Yii::error(sprintf('[getAdAccount] Exception, beginTime:%s, endTime:%s, reason:%s',
				$beginTime, $endTime, $message->getMessage()));
			return False;
		}
	}


	/**
	 *	根据accountId获取系统中记录的链接
	 *	@return json
	 */
	private function getPromotableUrls()
	{
		try{
			$accountId	= $this->accountId;
			$sql = sprintf("select e.promotable_urls,a.fbaccount_id from th_entity_info 
				as e left join th_account_info 
				as a on e.id = a.entity_id 
				where a.fbaccount_id = %s;", $accountId);
			$command	= $this->getDbConnection()->createCommand($sql);
			$result		= $command->queryOne();

			return $result;
		} catch(Exception $message) {
			Yii::error(sprintf('[getPromotableUrls] Exception, accountId:%s, reason:%s', 
				$accountId, $message->getMessage()));
			return False;
		}
	}


	/**
	 *	获取account下所有在投放的链接
	 *	@return obj
	 */
	private function getAdsetsPromoted($nextUrl = Null)
	{
		$accountId	= $this->accountId;
		try {
			if(!$nextUrl)
			{
				$api = sprintf(self::APIURL, $accountId);
				$requestUrl = $api . '?fields=name,promoted_object,status&access_token=' . Yii::$app->params['facebookApi']['access_token'];
			} else {
				$requestUrl	= $nextUrl;
			}
			$response = $this->sendGetRequest($requestUrl);
			if(!$response) throw HttpException('500', 'getAdsetsPromoted Exception');
			Yii::info(sprintf('[getAdsetsPromoted] Success, accountId:%s, requestUrl:%s, response:%s',
				$accountId, $requestUrl, $response));

			return $response;
		} catch(Exception $message) {
			Yii::error(sprintf('[getAdsetsPromoted] failed, accountId:%s, requestUrl:%s, reason:%s',
				$accountId,  $requestUrl, $message->getMessage()));
			return False;
		}
	}


	/**
	 *	将异常的url进行保存
	 *	@return
	 */
	private function saveAbNormalWebsite($adWebsiteObject)
	{
		try {
			$accountId = $this->accountId;
			$transaction = $this->getDbConnection()->beginTransaction();
			$entitySql = sprintf("update th_entity_info set promotable_urls =  '%s' where id = (select entity_id from th_account_info where fbaccount_id = %s limit 1)",
				$adWebsiteObject, $accountId
			);
			$accountSql = sprintf("update th_account_info set status = 7, updated_at = %s where fbaccount_id = %s", time(), $accountId);
			$entityCommand	= $this->getDbConnection()->createCommand($entitySql)->execute();
			$accountCommand = $this->getDbConnection()->createCommand($accountSql)->execute();
			$transaction->commit();
			Yii::info(sprintf("[saveAbNormalWebsite] Success, accountId:%s, promotable_urls:%s",
				$accountId, $adWebsiteObject
			));
			return true;
		} catch(Exception $message) {
			Yii::error(sprintf("[saveAbNormalWebsite] Falied, accountId:%s, promotable_urls:%s, reason:%s",
				$accountId, $adWebsiteObject, $message->getMessage()
			));
			$transaction->rollBack();
			return False;
		}	
	}


	/**
	 *	将监测信息保存到临时文件，以便邮件提醒
	 *	params	str	accountId
	 *	params	obj	adWebsiteObject
	 */
	private function saveAdConllectsFile($adWebsiteObject)
	{
		try{
			$accountId	= $this->accountId;
			$basePath	= Yii::$app->basePath;
			$dataDir	= sprintf("%s/runtime/data", $basePath);
			$adWebsiteData	= json_decode($adWebsiteObject);
			$adWebsiteData->accountId	= $accountId;
			$adWebsiteData->promotedUrl	= $this->adPromotedObjectUrls;
			$adWebsiteObject	= json_encode($adWebsiteData);
			if(!file_exists($dataDir))	{ mkdir($dataDir); }
			$adConllectsFile = 'AdConllectData_' . date('Ymd') . '.csv';
			$results = file_put_contents($dataDir. '/' .$adConllectsFile, $adWebsiteObject."\n", FILE_APPEND);
			Yii::info(sprintf("[saveAdConllectsFile] Success, accountId:%s, promotable_urls:%s",
				$accountId, $adWebsiteObject
			));
			if($results) return true;
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf("[saveAdConllects] Falied, accountId:%s, promotable_urls:%s, reason:%s",
				$accountId, $adWebsiteObject, $message->getMessage()
			));	
			return false;
		}
	}


	/**
	 *	缓冲获取到的promote obj
	 *	@params	obj	adSetsDatas
	 */
	private function pushPromoteUrl($adSetsDatas)
	{
		foreach($adSetsDatas as $adSetsData)
		{
			if($adSetsData->status != 'ACTIVE') continue;
			if(property_exists($adSetsData, 'promoted_object'))
			{
				$promotedObjectUrl	= !empty($adSetsData->promoted_object->object_store_url) ? $adSetsData->promoted_object->object_store_url : '';
				if(!in_array($promotedObjectUrl, $this->adPromotedObjectUrls))
					array_push($this->adPromotedObjectUrls, $promotedObjectUrl);
			}
		}
	}


	/**
	 *	监测promote obj，判断是否和记录中的一致
	 *
	 */
	private function checkPromoteUrl()
	{
		$accountId = $this->accountId;
		$adWebsiteUrlNormal		= !empty($this->adWebsiteUrlNormal) ? $this->adWebsiteUrlNormal : [];
		$adWebsiteUrlAbNormal	= !empty($this->adWebsiteUrlAbNormal) ? $this->adWebsiteUrlAbNormal : [];
		$websiteUrls = array_unique(array_merge($adWebsiteUrlNormal, $adWebsiteUrlAbNormal));
		$normalUrl		= [];
		$abNormalUrl	= [];
		if(!$websiteUrls) return ['normal' => $normalUrl, 'abnormal' => $abNormalUrl];

		foreach($websiteUrls as $url)
		{
			$url = str_replace('https', 'http', $url);
			Yii::info(sprintf('[checkPromoteUrl] accountId:%s, websiteUrl:%s, promotedUrl:%s',
				$accountId, $url, json_encode($this->adPromotedObjectUrls)
			));
			/*如果不在promote url中，则标记为异常*/
			if(!in_array($url, $this->adPromotedObjectUrls))
			{
				array_push($abNormalUrl, $url);
			}
		}

		return ['normal' => $adWebsiteUrlNormal, 'abnormal' => array_unique($abNormalUrl)]; 
	}


	/**
	 *	初始化website的值
	 *	params	str	accountId
	 */
	private function getWebsiteAttributes()
	{
		$accountId = $this->accountId;
		$adWebsiteObject	= $this->getPromotableUrls($accountId);
		if($adWebsiteObject)
		{
			$adWebsiteUrl	= json_decode($adWebsiteObject['promotable_urls']);
			$this->adWebsiteUrlNormal	= !empty($adWebsiteUrl->normal) ? array_filter(array_unique($adWebsiteUrl->normal)) : [];
			$this->adWebsiteUrlAbNormal	= !empty($adWebsiteUrl->abnormal) ? array_filter(array_unique($adWebsiteUrl->abnormal)) : [];
		}
	}


	/**
	 *	获取FaceBook投放中的url
	 *	params	str	accountId
	 */
	private function getPromotedAttributes()
	{
		$accountId	= $this->accountId;
		$adSetsObject		= $this->getAdsetsPromoted();
		if(!$adSetsObject) throw new Exception('getPromotedAttributes Exception');

		$adSetsObjectDecode = json_decode($adSetsObject);
		/* 如果返回error数据, 则记录之后退出*/
		if(property_exists($adSetsObjectDecode, 'error'))
		{
			Yii::error(sprintf("[getPromotedAttributes] error accountId:%s, results:%s",
				$accountId, $adSetsObject
			));
			return true;
		}
		
		$adSetsDatas = $adSetsObjectDecode->data;
		$this->pushPromoteUrl($adSetsDatas);
		while(property_exists($adSetsObjectDecode, 'paging'))
		{
			$adSetsNextUrl	= !empty($adSetsObjectDecode->paging->next) ? $adSetsObjectDecode->paging->next : Null;
			/* 如果没有next url，就退出 */
			if (!$adSetsNextUrl) break;
			$adSetsNextUrl	= $this->rebuildRequestUrl($adSetsNextUrl, ["limit" => 500]);
			$adSetsObject	= $this->getAdsetsPromoted($adSetsNextUrl);
			$adSetsObjectDecode = json_decode($adSetsObject);
			$adSetsDatas        = $adSetsObjectDecode->data;
			$this->pushPromoteUrl($adSetsDatas);
		}
		Yii::info(sprintf("[getPromotedAttributes] accountId:%s, adPromotedUrls: %s", $accountId, json_encode($this->adPromotedObjectUrls)));

		return true;
	}


	/**
	 *	主控制方法
	 *	@params	str	accountId
	 *	@params	str	accessToken
	 *	return
	 */
	public function run()
	{
		$accountId = $this->accountId;
		$this->getWebsiteAttributes();
		if($this->getPromotedAttributes())
		{
			$websiteArray	= $this->checkPromoteUrl();
			$websiteObject	= json_encode($websiteArray);

			if($websiteArray['abnormal'])
			{
				$this->saveAbNormalWebsite($websiteObject);
				$this->saveAdConllectsFile($websiteObject);	
			}
		
			$result = sprintf("[run] account_id:%s", $accountId);
			echo $result."\n";
			return true;
		}
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
