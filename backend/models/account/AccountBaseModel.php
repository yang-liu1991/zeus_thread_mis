<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-03-16 14:18:23
 */


namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\RequestApi;
use common\models\AmountConversion;
use yii\data\ActiveDataProvider;
use backend\models\ThreadBaseModel;
use common\struct\AccountChangeType;
use common\struct\FacebookAccountStatus;
use backend\models\record\ThAccountBlacklist;

class AccountBaseModel extends ThreadBaseModel
{
	/* 获取Account信息的API */
	const   READ_ACCOUNT_INFO   = 'https://graph.facebook.com/v2.9/act_%s';
	const   LANHAN_BM           = '511273569054473';

	public $account_id;
	public $account_name;
	public $account_status;
	public $business_id;
	public $business_name;
	public $error_message;
	public $action;



	/**
	 *	对所操作的account信息进行验证
	 */
	public function rules()
	{
		return [
			[['account_status'], 'statusValidate', 'skipOnEmpty' => false, 'on' => ['setAccountInfo'], 'message' => '{attribute}只有ACTIVE的帐户才可以进行操作！'],
			[['account_id'], 'blacklistValidate', 'on' => ['getAccountInfo']],
			[['account_id'], 'ownerValidate', 'on' => ['getAccountInfo']],
			[['account_id'], 'agencyValidate', 'on' => ['getAccountInfo']],
			[['action'], 'safe']
		];
	}
	
	
	/**
	 *	@inheritdoc
	 */
	public function scenarios()
	{
		$scenarios = parent::scenarios();
		return array_merge($scenarios, [
			'getAccountInfo'	=> ['account_id'],
			'setAccountInfo'	=> ['account_id', 'account_status', 'action'],
		]);
	}


	/**
	 *	blacklist validate
	 *	如果在黑名单中，则不操作
	 */
	public function blacklistValidate()
	{
		$this->setUserAttributes();
		$result = ThAccountBlacklist::find()->where(['account_id' => $this->account_id, 'company_id' => $this->company_id])->one();
		if($result)
			$this->addError('account_id', sprintf('Account Id:%s, 此Account Id已经被禁止操作！', $this->account_id));
	}


	/**
	 *	owner validate
	 *	如果account id的owner不为蓝瀚的大BM，则不让操作，主要是为了防止操作授权过的PC帐户
	 */
	public function ownerValidate()
	{
		$account_info = $this->getAccountInfo($this->account_id);
		if((property_exists($account_info, 'owner') && ($account_info->owner != self::LANHAN_BM)) || property_exists($account_info, 'error'))
			$this->addError('account_id', sprintf('Account Id:%s, 很抱歉无法对该帐户进行操作，请联系Reseller!', $this->account_id));
	}
	

	/**
	 *	验证此account id的Agency与操作用户是否为匹配
	 */
	public function agencyValidate()
	{
		/* 如果是admin，则跳过验证 */
		if(Yii::$app->user->can('admin_group'))	return true;
		$bindingDetail	= $this->getBindingDetail($this->account_id);
		$businessIds = $this->getCompanyBusinessId();
		if($bindingDetail)	
		{
			$bindingList	= !empty($bindingDetail->data) ? $bindingDetail->data : [];
			$bindingBmList = [];
			foreach($bindingList as $binding) if($binding->id) array_push($bindingBmList, $binding->id);
			if($businessIds)
			{
				foreach($businessIds as $businessId)
				{
					if(in_array($businessId, $bindingBmList)) return true;
				}
			}
		}
		$this->addError('account_id', sprintf('Account Id:%s, 很抱歉无法对该帐户进行操作，请联系Reseller!', $this->account_id));
	}

	
	/**
	 *	account status 验证
	 *	只有ACTIVE状态下的帐户才可以进行操作
	 */
	public function statusValidate()
	{
		if($this->account_status != FacebookAccountStatus::ACTIVE)
			$this->addError('account_status', sprintf('Account Id:%s, 只有ACTIVE的帐户才可以进行操作！', $this->account_id));	
	}

	
	/**
	 *	通过account_id获取account信息
	 *	@params	str	account_id
	 *	@return obj
	 */
	private function getAccountInfo($account_id)
	{
		try {
			$url = sprintf(self::READ_ACCOUNT_INFO, $account_id);
			$params	= [
				'access_token'	=> Yii::$app->params['facebookApi']['access_token'],
				'fields'		=> 'account_id,account_status,name,owner,spend_cap,amount_spent,media_agency,created_time,is_personal,partner'	
			];
			$encode_url	= sprintf("%s?%s", $url, http_build_query($params));
			$response = RequestApi::requestGet($encode_url);
			Yii::info(sprintf("[getAccountInfo] account_id:%s, send_url:%s, response:%s", $account_id, $encode_url, $response));
			if($response) return $response;
			throw new Exception('Response Request Exception!');
		} catch(Exception $message) {
			Yii::error(sprintf("[getAccountInfo] Exception, account_id:%s, send_url:%s, reason:%s", 
				$account_id, $url, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	接收数组形式，以数组形式返回account信息
	 *	@params	str		upload_data
	 *	@return array	account_info_list
	 */
	public function getAccountInfoList($upload_data)
	{
		try {
			if(is_array($upload_data))
			{
				$account_info_list = [];
				foreach($upload_data as $account_infos)
				{
					$account_data = [];
					foreach($account_infos as $key => $value)
					{
						if($key == 'account_id')
						{
							$account_info = $this->getFormatResponse($value);
							$account_data['account_info'] = $account_info;
						}
						$account_data[$key] = $account_infos[$key];
					}
					array_push($account_info_list, $account_data);
				}
				return $account_info_list;
			}
			throw new Exception('Unknow upload_data!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getAccountInfoList] Exception, upload_data:%s, reason:%s',
				$upload_data, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	将spend_cpa转代为美元进行显示，默认接口返回为美分
	 *	@params	str account_id
	 *	@return array response
	 */
	public function getFormatResponse($account_id)
	{
		try {
			$response	= $this->getAccountInfo($account_id);
			if(!$response) throw new Exception('Response Error!');
			$account_info = json_decode($response, true);
			if(array_key_exists('spend_cap', $account_info))
			{
				$spend_cap_cent = $account_info['spend_cap'];
				$account_info['spend_cap'] = AmountConversion::centToDollar($spend_cap_cent);
			}

			if(array_key_exists('amount_spent', $account_info))
			{
				$amount_spent_cent	= $account_info['amount_spent'];
				$amount_spent		= AmountConversion::centToDollar($amount_spent_cent);
				/* 这里在花费的基础上再加10%是limit的限制，因为有些帐户可能已经在消耗 */
				$account_info['amount_spent']		= $amount_spent;
				$account_info['min_spend_cap']	= sprintf("%.2f", $amount_spent * 1.1);
			}
			
			Yii::info(sprintf("[getFormatResponse] Response:%s, getFormatResponse:%s", $response, json_encode($account_info)));
			return $account_info;
		} catch(Exception $message) {
			Yii::error(sprintf("[getFormatResponse] Exception, response:%s, reason:%s",
				$response, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	将表单数据与附件数据进行合并
	 *	@params	array	$accountFormData
	 *	@return array
	 */
	public function mergeAccountFormData($accountFormData)
	{
		if($this->action == AccountChangeType::ACTION_SINGLE)
		{
			$account['account_id']			= !empty($accountFormData['account_id'])	? $accountFormData['account_id'] : '';
			$account['account_name']		= !empty($accountFormData['account_name'])	? $accountFormData['account_name'] : '';
			$account['account_status']		= !empty($accountFormData['account_status'])	? $accountFormData['account_status'] : '';
			$account['amount_spent']		= !empty($accountFormData['amount_spent'])	? $accountFormData['amount_spent'] : '';
			$account['min_spend_cap']		= !empty($accountFormData['min_spend_cap'])	? $accountFormData['min_spend_cap'] : '';
			$account['spend_cap']			= !empty($accountFormData['spend_cap'])	? $accountFormData['spend_cap'] : '';
			$account['business_id']			= !empty($accountFormData['business_id'])	? $accountFormData['business_id'] : '';
			$account['action_type']			= !empty($accountFormData['action_type'])	? $accountFormData['action_type'] : '';
			$account['permitted_roles']		= !empty($accountFormData['permitted_roles'])	? $accountFormData['permitted_roles'] : '';
			$account['new_account_name']	= !empty($accountFormData['new_account_name'])	? $accountFormData['new_account_name'] : '';
			array_push($this->accounts, $account);
		}
		return $this->accounts;
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
