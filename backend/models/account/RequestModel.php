<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-07-19 15:52:33
 */

namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use backend\models\user\User;
use backend\models\user\UserModel;
use yii\data\ActiveDataProvider;
use backend\models\ThreadBaseModel;
use backend\models\account\FbTimezoneIds;
use backend\models\record\ThRemindRecord;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThAgencyBusinessSearch;

class RequestModel extends ThreadBaseModel
{
	const BLUEFOCUS_REFERRAL = 'fb@bluefocus.com';

	/* 主健 */
	public $id;
	/* 帐号id */
	public $user_id;
	/* 公司id */
	public $company_id;
	/* 实体id */
	public $entity_id;
	/* 时区 */
	public $timezone_id;
	/* 开户名称 */
	public $fbaccount_name;
	/* 推荐人 */
	public $referral;
	/* 推荐码 */
	public $recommend_code;
	/* 实际付款主体 */
	public $pay_name_real;
	/* 结算方式 */
	public $pay_type;
	/* 开户状态 */	
	public $status = 0;
	/* 开户类型 */
	public $type = ThAccountInfoSearch::DIRECT_CREDIT;
	/* 开户数量 */
	public $number = 1;
	/* 开户信息 */
	public $request_list = [];


	public function rules()
	{
		return [
			[['company_id', 'entity_id', 'fbaccount_name', 'referral', 'timezone_id', 'type', 'number'], 'required', 'on' => ['create']],
			[['status', 'type', 'number'], 'integer', 'on' => ['create']],
			[['referral'], 'email'],
			[['request_list'], 'safe']
		];	
	}

	
	/**
	 *	@inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'fbaccount_name'	=> '开户名称',
            'referral'			=> '推荐人',
			'timezone_id'		=> '时区选择',
            'recommend_code'	=> '推荐码',
		];
	}


	/**
	 *	@inheritdoc
	 */
	public function scenarios()
	{
		$scenarios = parent::scenarios();
		return array_merge($scenarios, [
			'create' => ['timezone_id', 'fbaccount_name', 'referral', 'recommend_code', 'status', 'type'],	
		]);
	}

	/**
	 *	检查推荐人是否有效
	 */
	public static function checkReferral($referral)
	{
		$result = User::find()->where(['email' => $referral])->all();
		if($result && in_array($referral, self::getAllReferral())) return true;
		return false;
	}
		

	/**
	 *	get FbTimezones
	 */
	public static function getFbTimezoneIds()
	{
		return FbTimezoneIds::getTimezoneIdName();
	}


	/**
	 *	get AgencyBusinessy
	 *	@params	string	referral
	 *	@return obj
	 */
	public static function getAgencyBusiness($referral)
	{
		return ThAgencyBusinessSearch::getBusinessByReferral($referral);
	}

	
	/**
	 *	get all referral
	 */
	public static function getAllReferral()
	{
		$referral_list = [];
		$bussinessinfo_list = ThAgencyBusinessSearch::find()->all();
		foreach($bussinessinfo_list as $bussinessinfo)
		{
			if($bussinessinfo->referral) array_push($referral_list, $bussinessinfo->referral);
		}
		return $referral_list;
	}
	
	/**
	 *	get userinfo by email
	 */
	public static function getUserInfo($email)
	{
		return UserModel::getUserInfo($email);
	}


	/**
	 *	get account request type
	 */
	private function getAccountType($referral)
	{
		/* 如果是蓝瀚自己开户，则不为 PC  */
		if($this->referral == self::BLUEFOCUS_REFERRAL)
		{
			$this->type = ThAccountInfoSearch::DIRECT_CREDIT;
		/* 如果agency的bm为空，则为PC */
		} elseif(!self::getAgencyBusiness($referral)->business_id) {
			$this->type	= ThAccountInfoSearch::PARTITIONED_CREDIT; 
		}	
		return $this->type;
	}


	/**
	 *	get fbaccount name
	 */
	private function getFbAccountName($suffix)
	{
		/* 小于9时，前导补0 */
		if($suffix <= 9) $suffix = sprintf('%s%d', '0', $suffix);
		$fbaccount_name = $this->fbaccount_name;
		$fbaccount_name = str_replace("\t", ' ', $fbaccount_name);
		$name_array	= explode(' ', $fbaccount_name);
		if(count($name_array) > 3)
		{
			$account_name = sprintf('%s-%s-%s-%s-%d-%s', 
				$name_array[0], $name_array[1], $name_array[2], date('md'), substr(time(), -5), $suffix);
			return $account_name;
		}
		$account_name = sprintf('%s-%s-%d-%s', implode('-', $name_array), date('md'), substr(time(), -5), $suffix);
		return $account_name;
	}


	/**
	 *	save remind record
	 *	@params	int	account pk id
	 *	@return bool
	 */
	private function  remindRecordSave($account_id)
	{
		try {
			$remindModel = new ThRemindRecord();
			$remindModel->account_id	= $account_id;
			if($remindModel->validate() && $remindModel->save()) return true;
			throw new Exception(sprintf('remindRecordSave error : %s', json_encode($remindModel->getErrors())));
		} catch(Exception $message) {
			Yii::error(sprintf('[remindRecordSave] Exception, account_id:%d, reason:%s', 
				$account_id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	create record
	 */
	public function requestSave()
	{
		try {
			if($this->request_list)
			{
				$transaction = Yii::$app->db->beginTransaction();
				$account_total = 0;
				foreach($this->request_list as $request)
				{
					if(array_key_exists('referral', $request)) $this->referral = $request['referral'];
					for($i=1; $i<=$request['number']; $i++)
					{
						$account_total += 1;
						$accountObj = new ThAccountInfo();
						$accountObj->attributes			= $this->attributes;
						$accountObj->company_id			= $this->getUserInfo($this->referral)->company_id;
						$accountObj->timezone_id		= self::getFbTimezoneIds()[$request['timezone_id']];
						$accountObj->fbaccount_name		= $this->getFbAccountName($account_total);
						$accountObj->business_agency_id	= self::getAgencyBusiness($this->referral)->business_id;
						$accountObj->type				= $this->getAccountType($this->referral);

						if(!$accountObj->save())
						{
							throw new Exception(sprintf('accountInfo save Exception, data:%s, reason:%s', 
								json_encode($accountObj->attributes), json_encode($accountObj->getErrors())));
						} else {
							$account_id = $accountObj->getPrimaryKey();
							if(!$this->remindRecordSave($account_id))
								throw new Exception(sprintf('remindRecordSave Error, account_id:%d', $account_id));
						}
					}
				}
				$transaction->commit();
				return true;
			}
		} catch(Exception $message) {
			$transaction->rollBack();
			Yii::error(sprintf('[requestSave] Exception, request_list:%s, user_id:%s, reason:%s',
				json_encode($this->request_list), $this->user_id, $message->getMessage()
			));
			return false;
		}
	}
	
	
	/*
	 *	update status
	 */
	public function  requestUpdateById($id, $updateParams, $extaction)
	{
		try{
			$result = ThAccountInfo::updateall($updateParams, 'id = :id', ['id' => $id]);
			if($result > 0)
			{
				Yii::info(sprintf('[requestUpdateById] [action]:%s, [success] [Id]:%d, [User]:%s, [Data]:%s',
					$extaction, $id, UserModel::getLoginInfo()->email, json_encode($updateParams)
				));
				return true;
			}
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf('[requestUpdateById] [action]:%s [error] [Reason]:%s, [User]:%s, [Data]:%s',
				$extaction, json_encode($message->getMessage()), UserModel::getLoginInfo()->email,
				json_encode($updateParams)
			));
		}
	}


	/**
	 * getAttributes
	 */
	public function getAttribute($id)
	{
		$requestObj = self::findOne($id);
		$this->attributes = $requestObj->attributes;
	}

	/**
	 *	findOne
	 */
	public static function findOne($id)
	{
		return ThAccountInfo::findOne($id);
	}

	/**
	 *	findWhere one
	 *	@params array
	 *	@return Obj
	 */
	public static function findWhere($params)
	{
		return ThAccountInfo::find()->where($params)->one();
	}

	/**
	 *	findWhere all
	 *	@params array
	 *	@return Obj
	 */
	public static function findWhereAll($params)
	{
		return ThAccountInfo::find()->where($params)->all();
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
