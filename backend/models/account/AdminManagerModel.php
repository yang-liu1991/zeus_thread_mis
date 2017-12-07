<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-08-28 13:05:56
 */
namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use common\models\RequestApi;
use backend\models\account\AacBaseModel;
use backend\models\account\FbTimezoneIds;
use backend\models\record\ThEntityInfo;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThEntityInfoSearch;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThRemindRecordSearch;

class AdminManagerModel extends AacBaseModel
{
	public function rules()
	{
		return parent::rules();
	} 

	
	/**
	 *	根据id获取实体信息和开户信息
	 *	@params	int	id
	 *	@return Object
	 */
	public function getAccountData($id, $agencyBusinessId=Null)
	{
		try {
			if($agencyBusinessId)
			{
				$sql = sprintf("select *, a.created_at account_createdtime, 
					a.updated_at account_updatedtime from th_entity_info 
					as e left join th_account_info as a on e.id = a.entity_id where a.id = %d and business_agency_id = %s", 
					$id, $agencyBusinessId);
			} else {
					$sql = sprintf("select *, a.created_at account_createdtime, 
					a.updated_at account_updatedtime from th_entity_info 
					as e left join th_account_info as a on e.id = a.entity_id where a.id = %d and business_agency_id is null", $id);
			}
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();

			if($results)	return $results;
			throw new Exception(sprintf('getAccountData Error, There is no data, id:%s!', $id));
		} catch(Exception $message) {
			Yii::error(sprintf("[getAccountData] Exception, reason:%s", $message->getMessage()));
			return false;
		}
	}


	/**
	 *	获取所有开户信息
	 *	@params int	entity_id
	 *	@params	str	created_at
	 *	@return array
	 */
	private function getAdAccountsInfo($entityId, $referral, $agencyBusinessId, $createdAt)
	{
		try {
			if($agencyBusinessId)
			{
				$sql = sprintf('select fbaccount_name, timezone_id from th_account_info 
					where entity_id = %d and referral = "%s" and created_at = %s and business_agency_id = %s',
					$entityId, $referral, $createdAt, $agencyBusinessId);
			} else {
				$sql = sprintf('select fbaccount_name, timezone_id from th_account_info 
					where entity_id = %d and referral = "%s" and created_at = %s and business_agency_id is null',
					$entityId, $referral, $createdAt);
			}

			$connections	= Yii::$app->db;
			$command		= $connections->createCommand($sql);
			$results		= $command->queryAll();
			if($results) return $results;
			throw new Exception('getAdAccountsInfo Error, There is no data!');
		} catch(Exception $message) {
			Yii::error(sprintf("[getAdAccountsInfo] Exception, entityId:%d, agencyBusinessId:%s, reason:%s", $entityId, $agencyBusinessId, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	初始化数据
	 *	@params	array $data
	 *	@return bool
	 */
	public function initAttributes($data)
	{
		try {
			$email = $this->getEmailById($data['user_id']);
			$adAccountsInfo	= $this->getAdAccountsInfo($data['entity_id'], $data['referral'], $data['business_agency_id'], $data['created_at']);
			$adAccountInfoList	= [];
			foreach($adAccountsInfo as $adAccountInfo)
			{
				$adAccount = json_encode([
					'ad_account_name'	=> $adAccountInfo['fbaccount_name'],
					'timezone_id'		=> $this->getTimezoneId($adAccountInfo['timezone_id'])
					]);
				$adAccountInfoList[]	= $adAccount;
			}
			$this->entity_id			= $data['entity_id'];
			$this->request_id			= $data['request_id'];
			$this->access_token			= $this->getAccessToken();
			$this->extended_credit_id	= $this->getExtendedCreditId();
			$this->ad_accounts_info		= $adAccountInfoList;
			$this->business_registration	= $data["business_registration"];
			$this->business_registration_id	= $data["business_registration_id"];
			$this->advertiser_business_id	= $data['advertiser_business_id'];
			$this->vertical					= $data["vertical"];
			$this->subvertical				= $data["subvertical"];
			$this->is_smb					= ($data['is_smb'] == ThEntityInfoSearch::IS_SMB) ? 'True' : 'False';
			$this->official_website_url		= $data["official_website_url"];
			$this->promotable_page_ids		= json_decode($data["promotable_page_ids"]) ? 
				array_filter(json_decode($data["promotable_page_ids"])) : '';
			$this->promotable_app_ids		= json_decode($data["promotable_app_ids"]) ? 
				array_filter(json_decode($data["promotable_app_ids"])) : '';
			$this->promotable_page_urls		= json_decode($data['promotable_page_urls']) ? 
				array_filter(json_decode($data['promotable_page_urls'])) : '';
			$this->promotable_urls			= ($data["promotable_urls"]) ? $this->getPromotableUrls($data["promotable_urls"]) : '';
			$this->english_legal_entity_name= $data["name_en"];
			$this->chinese_legal_entity_name= $data["name_zh"];
			$this->address_in_chinese		= $data["address_zh"];
			$this->address_in_english		= $data["address_en"];
			$this->planning_agency_business_id	= $data["business_agency_id"];
			$this->contact					= json_encode(["name" => $data["contact"], "email" => $email["email"]]);
			$this->referral					= $data['referral'];
			$this->status					= $data['status'];
			$this->type						= $data['type'];
			$this->account_createdtime		= $data['account_createdtime'];
			$this->account_updatedtime		= $data['account_updatedtime'];

			return true;
		} catch(Exception $message) {
			Yii::error(sprintf("[initAttributes] Exception, ID:%d, data:%s, reason:%s",
				$this->id, json_encode($data), $message->getMessage()));
			return false;
		}
	}
	
	
	/**
	 *	构造请求数据
	 *	@params return array
	 */
	private function buildRequestData()
	{
		try {
			$params = [];
			$params["access_token"]				= $this->access_token;
			$params["extended_credit_id"]		= $this->extended_credit_id;
			for($i=0; $i<count($this->ad_accounts_info); $i++)
			{
				$params["ad_accounts_info[$i]"]	= $this->ad_accounts_info[$i];
			}
			$params["business_registration"]	= sprintf("@%s;%s",
				$this->getBusinessRegistrtion(Yii::$app->params['ugcServer']['imgdir'].$this->business_registration), 'type=image/jpeg');
			$params["business_registration_id"]	= $this->business_registration_id;
			/* 判断是否有授权BM id */
			if($this->advertiser_business_id) $params['advertiser_business_id'] = $this->advertiser_business_id;
			$params["vertical"]					= $this->vertical;
			$params["subvertical"]				= $this->subvertical;
			$params["is_smb"]					= $this->is_smb;
			$params["official_website_url"]		= $this->official_website_url;

			/* 如果提交的是粉丝页id */
			/* 他们这个地方真恶心，都需要做判断 */
			if($this->promotable_page_ids)
			{
				for($i=0; $i<count($this->promotable_page_ids); $i++)
				{
					$params["promotable_page_ids[$i]"]		= $this->promotable_page_ids[$i];
				}
				if($this->promotable_page_urls)
				{
					for($i=0; $i<count($this->promotable_page_urls); $i++)
					{
						$params["promotable_page_urls[$i]"]		= $this->promotable_page_urls[$i];
					}
				} else {
					$params["promotable_page_urls"] = "[]";
				}
			/* 如果提交的是粉丝页url */
			} elseif($this->promotable_page_urls) {
				for($i=0; $i<count($this->promotable_page_urls); $i++)
				{
					$params["promotable_page_urls[$i]"]		= $this->promotable_page_urls[$i];
				}
				$params["promotable_page_ids"] = "[]";
			} else {
				throw new BadRequestHttpException('Get Page links or Id Error!');
			}

			if($this->promotable_app_ids)
			{
				for($i=0; $i<count($this->promotable_app_ids); $i++)
				{
					$params["promotable_app_ids[$i]"]		= $this->promotable_app_ids[$i];
				}
			} else {
				/*电商类广告，appids为空数组*/
				$params["promotable_app_ids"] = "[]";
			}

			if($this->promotable_urls)
            {
                for($i=0; $i<count($this->promotable_urls); $i++)
                {
                    $params["promotable_urls[$i]"]			= $this->promotable_urls[$i];
                }
            } else {
			    $params["promotable_urls"] = "[]";
            }

			$params["english_legal_entity_name"]= $this->english_legal_entity_name;
			$params["chinese_legal_entity_name"]= $this->chinese_legal_entity_name;
			$params["address_in_chinese"]		= $this->address_in_chinese;
			$params["address_in_english"]		= $this->address_in_english;
			$params["planning_agency_business_id"]	= $this->planning_agency_business_id;
			$params["contact"]					= $this->contact;
			$params['additional_comment']		= $this->additional_comment;
			Yii::info(sprintf("[buildRequestData] data:%s", json_encode($params)));

			return $params;
		} catch(Exception $message) {
				Yii::error(sprintf("[buildRequestData] Exception, ID:%d, data:%s, reason:%s",
				$this->id, json_encode($data), $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	提交开户请求
	 *	@params	int	id
	 */
	public function accountCreate()
	{
		try {
			$url	= sprintf(self::CREATING_CREATION_API, $this->business_id);
			$params	= $this->buildRequestData();
			$response = RequestApi::requestPost($url, $params);
			Yii::info(sprintf("[accountCreate] Info url:%s, params:%s, response:%s",
				$url, json_encode($params), $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'ad_account_creation_request_id'))
				{
					$requestId = $result->ad_account_creation_request_id;
					if($this->accountUpdateByEntityId([
						"request_id"	=> $requestId, 
						'business_id'	=> $this->business_id,
						'extended_credit_id'	=> $this->extended_credit_id,
						'additional_comment'	=> $this->additional_comment,
						'updated_at'	=> time(),
						'type'			=> $this->type,
						"status" => ThAccountInfoSearch::getAccountStatus()["PENDING"]],
					$this->entity_id, $this->referral, $this->account_createdtime, $this->planning_agency_business_id
					) && $this->updateRemindStatus($this->id, ThRemindRecordSearch::WAITING_FB)) return true;
				}
				return $response;
			}
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf("[accountCreate] Exception ID:%d, reason:%s", $this->id, $message->getMessage()));
			return false;
		}	
	}


	/**
	 *	更新开户请求
	 *	@params	int	id
	 */
	public function accountUpdate()
	{
		try {
			if($this->status != ThAccountInfoSearch::getAccountStatus()["WAIT"] && 
				$this->status != ThAccountInfoSearch::getAccountStatus()["PENDING"] && 
				$this->status != ThAccountInfoSearch::getAccountStatus()["REQUESTED_CHANGE"])
			{
				throw new Exception("Onle PENDING or REQUESTED_CHANGE can be update!");
			}
			$url	= sprintf(self::UPDATE_CREATION_API, $this->request_id);
			$params	= $this->buildRequestData();
			$response = RequestApi::requestPost($url, $params);
			Yii::info(sprintf("[accountUpdate] Info id:%d, request_id:%s, url:%s, params:%s, response:%s",
				$this->id, $this->request_id, $url, json_encode($params), $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'success') && $result->success == true)
				{
					if($this->accountUpdateByEntityId([
						'additional_comment'	=> $this->additional_comment,
						'updated_at'	=> time(),
						"status" => ThAccountInfoSearch::getAccountStatus()["PENDING"]],
					$this->entity_id, $this->referral, $this->account_createdtime, $this->planning_agency_business_id
					) && $this->updateRemindStatus($this->id, ThRemindRecordSearch::WAITING_FB)) return true;
				}
				return $response;
			}
			throw new Exception("Response Error!");
		} catch(Exception $message) {
			Yii::error(sprintf("[accountUpdate] Exception ID:%d, request_id:%s, reason:%s", 
				$this->id, $this->request_id, $message->getMessage()));
			return $message->getMessage();
		}
	}

	
	/**
	 *	取消开户申请
	 *	@params	int	id
	 *	@return
	 */
	public function accountDelete($id)
	{
		try {
			$accountObj		= ThAccountInfo::find()->where(["id" => $id])->one(); 
			if($accountObj->status != ThAccountInfoSearch::getAccountStatus()["PENDING"] && 
				$accountObj->status != ThAccountInfoSearch::getAccountStatus()["REQUESTED_CHANGE"])
			{
				throw new Exception("Onle PENDING or REQUESTED_CHANGE can be delete!");
			}
			$url	= self::DELETE_CREATION_API;
			$params	= ["access_token" => $this->access_token, "id" => $accountObj->request_id];
			$response = RequestApi::requestDelete($url, $params);
			Yii::info(sprintf("[accountDelete] Info ID:%d, request_id:%s, url:%s, params:%s, response:%s",
				$id, $accountObj->request_id, $url, json_encode($params), $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'success') && $result->success == true)
				{
					if($this->accountUpdateByEntityId([
						'updated_at'	=> time(),
						"status" => ThAccountInfoSearch::getAccountStatus()["CANCELLED"]],
						$this->entity_id, $this->referral, $this->account_createdtime, $this->planning_agency_business_id
						)) return true;
				}
				return $response;
			}
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf("[accountDelete] Exception ID:%d, request_id:%s, reason:%s", 
				$id, $accountObj->request_id, $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	更新开户信息
	 *	@params	int	$entity_id
	 *	@params	str	$created_at
	 *	@return bool
	 */
	private function accountUpdateByEntityId($attributes, $entity_id, $referral, $created_at, $agencyBusinessId=Null)
	{
		if($agencyBusinessId)
		{
			$result = ThAccountInfo::updateAll($attributes, 
				'entity_id = :entity_id and business_agency_id = :planning_agency_business_id and referral = :referral and created_at = :created_at', 
				[":entity_id" => $entity_id, ":planning_agency_business_id" => $agencyBusinessId, ":referral" => $referral, ":created_at" => $created_at]);
		} else {
			$setValues = '';
			foreach($attributes as $key => $value)
			{
				if(!$value) continue;
				$setValues .= sprintf('%s = "%s",', $key, $value);
			}
			$setValues	= rtrim($setValues, ",");
			$sql = sprintf('update th_account_info set %s where entity_id = %d and referral = "%s" and  created_at = %s and business_agency_id is null',
				$setValues, $entity_id, $referral, $created_at);
			$result = $this->updateAccountInfoBySql($sql);
		}	
		Yii::info(sprintf("[accountUpdateByEntityId] Info entity_id:%d, attributes:%s, result:%s",
			$entity_id, json_encode($attributes), $result));
		if($result) return true;
		return false;
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
