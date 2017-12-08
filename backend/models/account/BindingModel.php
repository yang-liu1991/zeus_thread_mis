<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-24 10:20:54
 */

namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\HttpException;
use common\models\RequestApi;
use yii\data\ActiveDataProvider;
use common\struct\AccountChangeStatus;
use common\struct\AccountChangeType;
use backend\models\account\AccountBaseModel;
use backend\models\record\ThAgencyBinding;
use backend\models\record\ThAgencyBindingSearch;


class BindingModel extends AccountBaseModel
{
	/* 绑定接口, 需要传入被绑定的BM ID*/
	const	SET_BINDING_API		= 'https://graph.facebook.com/v2.9/%s/adaccounts';
	/* 解除绑定接口, 需要传入绑定的BM ID */
	const	REMOVE_BINDING_API	= 'https://graph.facebook.com/v2.9/act_%s/agencies';
	/* 读取business信息接口 */
	const	READ_BUSINESS_INFO	= 'https://graph.facebook.com/v2.9/%s';

	public $id;
	public $account_id;
	public $account_status;
	public $access_type = 'AGENCY';
	public $access_status;
	public $permitted_roles;
	public $action_type;
	public $status;
	public $reason;
	public $action;
	public $error_message;
	public $upload_file;
	public $accounts = [];


	/**
     * @inheritdoc
     */
    public function rules()
    {
		return array_merge(parent::rules(), [
			[['account_id'], 'required', 'on' => 'getAccountInfo', 'message' => '{attribute}不能为空！'],
			[['account_id', 'business_id'], 'integer', 'on' => 'setAccountInfo', 'message' => '{attribute}必须为数字！'],
			[['permitted_roles'], 'permittedRolesValidate', 'on' => 'setAccountInfo', 'skipOnEmpty' => false, 'message' => '{attribute}不能为空！'],
			[['business_id'], 'businessIdValidate', 'on' => 'setAccountInfo', 'message' => '{attribute}Account ID已经绑定过相应的BM了！'],
			[['account_id', 'business_id'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
			[['account_id', 'account_name', 'business_id', 'action_type'], 'required', 'on' => 'setAccountInfo', 'message' => '{attribute}不能为空！'],
			[['account_id', 'account_name', 'account_status', 'business_id', 'access_status', 'status', 'reason', 'business_name', 'action_type', 'permitted_roles', 'error_message', 'action', 'upload_file', 'accounts'], 'safe', 'on' => 'setAccountInfo']
		]);
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id'	=> 'Account ID',
			'account_name'	=> 'Account Name',
			'account_status'	=> 'Account Status',
            'business_id'	=> 'BM ID',
			'access_type'	=> 'Access Type',
            'access_status'	=> 'Access Status',
			'action_type'	=> '操作类型',
            'permitted_roles'	=> '分配角色',
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
			'setAccountInfo'	=> ['account_id', 'account_name', 'account_status', 'business_id', 'access_status', 'status', 'reason', 'business_name', 'permitted_roles', 'action_type', 'action', 'accounts', 'upload_file']
		]);
	}
	

	/**
	 *	permitted_roles 验证
	 *	当为绑定操作时，permitted_roles为必添
	 */
	public function permittedRolesValidate()
	{
		if($this->action_type == ThAgencyBindingSearch::ACTION_TYPE_BINDING && !$this->permitted_roles)
			$this->addError('permitted_roles', '绑定操作时，必须要分配角色！');
	}


	/**
	 *	验证business id
	 *	如果为绑定操作的话，会检查是否为重复绑定；如果是撤消绑定的话，会检查之前是否绑定过
	 */
	public function businessIdValidate()
	{
		if(Yii::$app->user->can('admin_group')) return true;

		$bindingDetail	= $this->getBindingDetail($this->account_id);
		$businessIds	= $this->getCompanyBusinessId();
		if($bindingDetail)
		{
			$bindingList	= !empty($bindingDetail->data) ? $bindingDetail->data : [];
			$bindingBmList = [];
			foreach($bindingList as $binding) if($binding->id) array_push($bindingBmList, $binding->id);
			if($this->action_type == ThAgencyBindingSearch::ACTION_TYPE_BINDING)
			{
				/* 如果为绑定操作，则判断是否为重复绑定 */
				if(in_array($this->business_id, $bindingBmList))
				{
					$this->addError('business_id', sprintf('Account Id:%s 已经绑定过BM ID:%s !', $this->account_id, $this->business_id));
					return false;
				}	
				/* 如果为绑定操作，则判断操作人输入的BM ID是否为所属Agency，具体查看th_agency_business */	
				if(!in_array($this->business_id, $businessIds))
				{
					$this->addError('business_id', sprintf('Account Id:%s, BM Id:%s 很抱歉无法对该帐户进行操作，请联系Reseller!', 
						$this->account_id, $this->business_id));
					return false;
				}

				/* 判断操作人所输入的BM和Account原有绑定的BM，是否为同一家Agency，如果不是则不能操作*/
				if($businessIds)
				{
					foreach($businessIds as $businessId)
					{
						if(in_array($businessId, $bindingBmList)) return true;
					}
					$this->addError('business_id', sprintf('Account Id:%s, BM Id:%s 很抱歉无法对该帐户进行操作，请联系Reseller!', 
						$this->account_id, $this->business_id));return false;
				} else {
					$this->addError('business_id', sprintf('Account Id:%s, BM Id:%s 很抱歉无法对该帐户进行操作，请联系Reseller!', 
						$this->account_id, $this->business_id));return false;
				}
			/* 如果是解绑操作，则需要判断BM是否为操作用户所属的Agency */
			} elseif($this->action_type == ThAgencyBindingSearch::ACTION_TYPE_REMOVING) {
				if(!in_array($this->business_id, $businessIds))
				{
					$this->addError('business_id', sprintf('Account Id:%s, BM Id:%s 很抱歉无法对该帐户进行操作，请联系Reseller!', 
						$this->account_id, $this->business_id));
					return false;
				}	
				if(!in_array($this->business_id, $bindingBmList))
				{
					$this->addError('business_id', sprintf('Account Id:%s 没有绑定过此BM ID:%s！', $this->account_id, $this->business_id));
					return false;
				}	
			}
		} else {
			/* 如果这个帐户之前没有绑定过任何BM，则判断操作人输入的BM ID是否为所属Agency，具体查看th_agency_business */
			if($this->action_type == ThAgencyBindingSearch::ACTION_TYPE_BINDING)
			{
				if(!in_array($this->business_id, $businessIds))
				{
					$this->addError('business_id', sprintf('Account Id:%s, BM Id:%s 很抱歉无法对该帐户进行操作，请联系Reseller!', 
						$this->account_id, $this->business_id));
					return false;
				}	
			}
			/* 如果这个帐户之前没有绑定过任何BM，则不能进行解绑操作*/
			if($this->action_type == ThAgencyBindingSearch::ACTION_TYPE_REMOVING)
			{
				$this->addError('business_id', sprintf('Account Id:%s 没有绑定过此BM ID:%s！', $this->account_id, $this->business_id));
				return false;
			}	
		}
	}


	/**
	 *	通过business_id 获取BM信息
	 *	@params	int	business_id
	 *	@return obj
	 */
	private function getBusinessInfo($business_id)
	{
		try {
			$url = sprintf(self::READ_BUSINESS_INFO, $business_id);
			$params	= [
				'access_token'	=> Yii::$app->params['facebookApi']['access_token'],
				'fields'		=> 'id,name'	
			];
			$encodeUrl	= sprintf("%s?%s", $url, http_build_query($params));
			$response = RequestApi::requestGet($encodeUrl);
			Yii::info(sprintf("[getBusinessInfo] business_id:%s, send_url:%s, response:%s", $business_id, $encodeUrl, $response));
			if($response)
			{
				$result	= json_decode($response);
				if($result) return $result;
				throw new Exception('Response Json_decode Exception!');
			}
			throw new Exception(sprintf('Response Request Exception, response:%s!', $response));
		} catch(Exception $message) {
			Yii::error(sprintf("[getBusinessInfo] Exception, business_id:%s, send_url:%s, reason:%s", 
				$business_id, $url, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	执行绑定操作
	 *	@params	str	$account_id
	 *	@params	str	$business_id
	 *	@return
	 */
	private function setBindingRequest($account_id, $business_id)
	{
		try {
			$url	= sprintf(self::SET_BINDING_API, $business_id);
			$params	= [
				'access_token'	=> Yii::$app->params['facebookApi']['access_token'],
				'adaccount_id'	=> sprintf("act_%s", $account_id), 
				'access_type'	=> $this->access_type,
				'permitted_roles[0]'	=> $this->permitted_roles
			];
            $response	= RequestApi::requestPost($url, http_build_query($params));
            Yii::info(sprintf("[setBindingRequest] account_id:%s, business_id:%s, send_url:%s, params:%s, response:%s",
				$account_id, $business_id, $url, json_encode($params), $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'access_status') && $result->access_status == 'CONFIRMED')
				{
					$this->access_status	= ThAgencyBindingSearch::CONFIRMED;
					return true;
				} elseif(property_exists($result, 'access_status') && $result->access_status == 'PENDING') {
					$this->access_status	= ThAgencyBindingSearch::PENDING;
					return true;
				}
				return $result;
			}
			throw new Exception(sprintf("setBindingRequest response error, response : %s!", $response));
		} catch(Exception $message) {
			Yii::error(sprintf("[setBindingRequest] Exception, account_id:%s, business_id:%s, send_url:%s, reason:%s",
				$account_id, $business_id, $url, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	执行解绑操作
	 *	@params	str	$account_id
	 *	@params	str	$business_id
	 *	@return
	 */
	private function removeBindingRequest($account_id, $business_id)
	{
		try {
			$url	= sprintf(self::REMOVE_BINDING_API, $account_id);
			$params	= ['access_token'  => Yii::$app->params['facebookApi']['access_token'], 'business' => $business_id];
            $response	= RequestApi::requestDelete($url, $params);
            Yii::info(sprintf("[removeBindingRequest] account_id:%s, business_id:%s, send_url:%s, params:%s, response:%s",
				$account_id, $business_id, $url, json_encode($params), $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'success') && $result->success == 'true')
				{
					$this->access_status	= ThAgencyBindingSearch::CONFIRMED;
					return true;
				}
				return $result;
			}
			throw new Exception("removeBindingRequest response error!");
		} catch(Exception $message) {
			Yii::error(sprintf("[removeBindingRequest] Exception, account_id:%s, business_id:%s, send_url:%s, reason:%s",
				$account_id, $business_id, $url, $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	获取请求接口的返回状态
	 */
	private function getRequestResult($account_id, $business_id)
	{
		if($this->action_type == ThAgencyBindingSearch::ACTION_TYPE_BINDING)
			return $this->setBindingRequest($account_id, $business_id);
		return $this->removeBindingRequest($account_id, $business_id);
	}


	/**
	 *	保存绑定信息
	 *	@return 
	 */
	public function saveBindingRecord()
	{
		$transaction = Yii::$app->db->beginTransaction();
		try {
			foreach($this->accounts as $account)
			{
				$this->setAttributes($account);
				$businessInfo = $this->getBusinessInfo($this->business_id);
				if(property_exists($businessInfo, 'name')) 
				{
					$this->business_name	= $businessInfo->name;
				} else {
					$this->error_message	= $businessInfo;
					throw new Exception('getBusinessInfo Exception!');
				}
				if($this->validate())
				{
					$model	= new ThAgencyBinding();
					$model->attributes	= $this->attributes;
					if($model->validate() && $model->save())
						Yii::info(sprintf("[saveBindingRecord] Success, data:%s", json_encode($this->attributes)));
				} else {
					$this->error_message = $this->getErrors();
					throw new Exception('saveBindingRecord validate error!');
				}
			}
			$transaction->commit();
			return true;
		} catch(Exception $message) {
			$transaction->rollBack();
			Yii::error(sprintf("[saveBindingRecord] Exception, data:%s, reason:%s", 
				json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	提交绑定或者解绑的请求
	 */
	public function submitBindingRecord()
	{
		try {
			$bindingRecord = $this->getBindingRecord($this->id);
			if($bindingRecord)
			{
				$this->attributes	= $bindingRecord->attributes;
			} else {
				throw new Exception('Have no binding record!');
			}
			$requestResult = $this->getRequestResult($this->account_id, $this->business_id);
			if(is_object($requestResult) && property_exists($requestResult, 'error')) 
			{
				$this->error_message	= $requestResult->error;
				throw new Exception(sprintf("getRequestResult error:%s", json_encode($this->error_message)));
			} else if($requestResult) {
				$this->updateBindingRecord(['status' => AccountChangeStatus::ACCOUNT_CHANGE_SUCCESS, 'access_status' => $this->access_status],
					'id=:id', [':id' => $this->id]);
				return true;
			}
		} catch(Exception $message) {
			Yii::error(sprintf('[submitBindingRecord] Exception, id:%d, reason:%s',
				$this->id, $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	驳回更新name的方法
	 */
	public function rejectBinding()
	{
		try {
			$this->reason = json_encode([trim($this->reason)]);
			if($this->updateBindingRecord([
				'status' => AccountChangeStatus::ACCOUNT_CHANGE_FAILED, 
				'reason' => $this->reason], 'id=:id', [':id' => $this->id]))
			{
				Yii::info(sprintf('[rejectBinding] Success, id:%d, status:%d, reject_reason:%s',
					$this->id, AccountChangeStatus::ACCOUNT_CHANGE_FAILED, $this->reason));
				return true;
			}
			throw new Exception('updateBindingRecord Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[rejectBinding] Exception, id:%d, status:%d, reject_reason:%s, reason:%s',
				$this->id, AccountChangeStatus::ACCOUNT_CHANGE_FAILED, $this->reason, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	获取驳回的原因
	 *	@params	int	$id
	 *	@return string
	 */
	public function getRejectReason($id)
	{
		$record	= $this->getBindingRecord($id);
		return $record->reason;
	}


	/**
	 *	更新binding record status
	 *	@params	int	status
	 *	@return
	 */
	private function updateBindingRecord($attributes, $condition, $params)
	{
		if(ThAgencyBinding::updateAll($attributes, $condition, $params)) return true;
		throw new Exception('updateBindingRecord Error, no record update!');
	}


	/**
	 *	根据主键id获取相应信息
	 *	@params	int	id
	 *	@return obj
	 */
	private function getBindingRecord($id)
	{
		return ThAgencyBinding::findOne($id); 
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
