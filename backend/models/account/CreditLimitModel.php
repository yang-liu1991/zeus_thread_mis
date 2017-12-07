<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-11-23 15:53:34
 */

namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\RequestApi;
use common\models\AmountConversion;
use yii\data\ActiveDataProvider;
use common\struct\AccountChangeStatus;
use backend\models\account\AccountBaseModel;
use backend\models\record\ThAgencyCreditlimit;
use backend\models\record\ThAgencyCreditlimitSearch;


class CreditLimitModel extends AccountBaseModel
{
	/**
	 *	妹的，坑太多了，这个类一定要好好写，用心写
	 */

	/* 更新额度的接口 */
	const CHANGE_SPEND_CAP_API	= 'https://graph.facebook.com/v2.9/act_%s';
	const READ_ACCOUNT_INFO   = 'https://graph.facebook.com/v2.9/act_%s';


	/**
	 *	以下为增加额度或者减少额度的必要信息
	 */
	public $id;
	public $spend_cap;
	public $spend_cap_old;
	public $min_spend_cap;
	public $amount_spent;
	public $action_type;
	public $number;
	public $status;
	public $reason;
	public $error_message;
	public $accounts = [];
	public $action;
	public $upload_file;

	/**
	 *	rules
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['account_id'], 'required', 'on' => ['getAccountInfo'], 'message' => '{attribute}不能为空！'],	
			[['account_id', 'action_type'] , 'required', 'on' => ['setAccountInfo'], 'message' => '{attribute}不能为空！'],
			[['number'], 'limitValidate', 'on' => 'setAccountInfo', 'message' => '{attributes}-撤消额度不能小于最小额度限制!'],
			[['number'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
			[['id', 'company_id', 'account_name', 'account_status', 'spend_cap', 'spend_cap_old', 'min_spend_cap', 'amount_spent', 'action_type', 'number', 'status', 'reason', 'error_message', 'accounts', 'action'], 'safe', 'on' => ['setAccountInfo']]
		]);
	}


	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'account_id'		=> 'Account ID',
			'account_name'		=> 'Account Name',
			'account_status'	=> 'Account Status',
			'spend_cap'			=> 'Current Limit',
			'spend_cap_old'		=> 'Current Limit Old',
			'min_spend_cap'		=> 'Can\'t be lower than',
			'amount_spent'		=> 'Amount Spent',
			'number'			=> '调整额度',
			'action_type'		=> '操作类型',
			'error_message'		=> '错误信息',
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
			'setAccountInfo'	=> ['account_id', 'company_id', 'account_name', 'account_status', 'spend_cap', 'spend_cap_old', 'min_spend_cap', 'amount_spent', 'action_type', 'number', 'status', 'reason', 'error_message', 'accounts', 'action'],
		]);
	}


	/**
	 *	number limit
	 *	如果为撤消额度，则不能小于min_spend_cap，最小的limit; 如果是增加额度，则不能大于20W美金
	 */
	public function limitValidate()
	{
		if($this->action_type == ThAgencyCreditlimitSearch::ACTION_TYPE_DEL)
		{
			if(($this->spend_cap - $this->number) < $this->min_spend_cap)
				$this->addError('number', sprintf('Account Id:%s 撤消后的额度不能小于最小额度限制!', $this->account_id));
		} else if($this->action_type == ThAgencyCreditlimitSearch::ACTION_TYPE_ADD) {
			if($this->number > 200000)
				$this->addError('number', sprintf('Account Id:%s 增加额度不能大于%d', $this->account_id, 200000));
		}
	}

			
	/**
	 *	根据action_type设置spend_cap的值
	 *	@return int
	 */
	private function getSpendcapValue()
	{
		if($this->action_type == ThAgencyCreditlimitSearch::ACTION_TYPE_ADD)
		{
			$spend_cap = $this->spend_cap + $this->number;
		} elseif($this->action_type == ThAgencyCreditlimitSearch::ACTION_TYPE_DEL) {
			$spend_cap = $this->spend_cap - $this->number;
		} elseif($this->action_type == ThAgencyCreditlimitSearch::ACTION_TYPE_RESET) {
			/* 目前Facebook如果帐户清零的话，只能设置为0.01$ */
			$spend_cap = 0.01; 
		}
		return $spend_cap;
	}


	/**
	 *	进行增加或减少的操作
	 *	@params	int	spend_cap
	 *	@return
	 */
	private function changeSpendCap($spend_cap)
	{
		try {
			$url = sprintf(self::CHANGE_SPEND_CAP_API, $this->account_id);
			$params	= [
				'access_token'	=> Yii::$app->params['facebookApi']['access_token'],
				'spend_cap' => $spend_cap
			];
            $response = RequestApi::requestPost($url, http_build_query($params));
            Yii::info(sprintf("[changeSpendCap] accountId:%s, send_url:%s, params:%s, response:%s",
				$this->account_id, $url, json_encode($params), $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'success') && $result->success == true) return true;
				return $result;
			}
			throw new Exception('changeSpendCap Exception!');
		} catch(Exception $message) {
			Yii::error(sprintf("[changeSpendCap] Exception, changeSpendCap:%s, reason:%s", 
				$spend_cap, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	进行保存方法
	 *	@return bool
	 */
	public function saveSpendcapRecord()
	{
		$transaction = Yii::$app->db->beginTransaction();
		try {
			foreach($this->accounts as $account)
			{
				$this->setAttributes($account);
				$this->spend_cap_old = $this->spend_cap;
				$this->spend_cap = $this->getSpendcapValue();
				if($this->validate())
				{
					$model = new ThAgencyCreditlimit();
					$model->attributes = $this->attributes;
					$model->spend_cap		= AmountConversion::dollarToCent($this->spend_cap);
					$model->spend_cap_old	= AmountConversion::dollarToCent($this->spend_cap_old);
					$model->min_spend_cap	= AmountConversion::dollarToCent($this->min_spend_cap);
					$model->amount_spent	= AmountConversion::dollarToCent($this->amount_spent);
					$model->number			= AmountConversion::dollarToCent($this->number);
					if($model->validate() && $model->save())
					{
						Yii::info(sprintf("[saveSpendcapRecord] Success, data:%s", json_encode($this->attributes)));
					}
				} else {
					$this->error_message = $this->getErrors();
					throw new Exception('saveSpendcapRecord validate error!');
				}
			}
			$transaction->commit();
			return true;
		} catch(Exception $message) {
			$transaction->rollBack();
			Yii::error(sprintf('[saveSpendcapRecord] Exception, data:%s, reason:%s', 
				json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	提交额度变更
	 *	@return bool
	 */
	public function submitCreditLimit()
	{
		try {
			$creditLimitRecord = $this->getCreditLimitRecord($this->id);
			if($creditLimitRecord)
			{
				$this->attributes	= $creditLimitRecord->attributes;
				$this->spend_cap	= AmountConversion::centToDollar($this->spend_cap);
			} else {
				throw new Exception('Have no credit limit record!');
			}

			$changeSpendCapResult = $this->changeSpendCap($this->spend_cap);
			if(is_object($changeSpendCapResult) && property_exists($changeSpendCapResult, 'error'))
			{
				$this->error_message = $changeSpendCapResult->error;
				throw new Exception(sprintf("changeSpendCap error:%s", json_encode($this->error_message)));
			} elseif($changeSpendCapResult) {
				$this->updateCreditLimitRecord(['status' => AccountChangeStatus::ACCOUNT_CHANGE_SUCCESS], 'id=:id', [':id' => $this->id]);
				return true;
			}
		} catch(Exception $message) {
			Yii::error(sprintf('[submitCreditLimit] Exception, id:%d, reason:%s',
				$this->id, $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	驳回帐户额度调整
	 */
	public function rejectCreditLimit()
	{
		try {
			$this->reason	= json_encode([trim($this->reason)]);
			if($this->updateCreditLimitRecord([
				'status' => AccountChangeStatus::ACCOUNT_CHANGE_FAILED,
				'reason' => $this->reason], 'id=:id', [':id' => $this->id]))
			{
				Yii::info(sprintf('[rejectCreditLimit] Success, id:%s, status:%d, reject_reason:%s',
					$this->id, AccountChangeStatus::ACCOUNT_CHANGE_FAILED, $this->reason));
				return true;
			}
			throw new Exception('updateCreditLimitRecord Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[rejectCreditLimit] Exception, id:%d, status:%d, reject_reason:%s, reason:%s',
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
		$record	= $this->getCreditLimitRecord($id);
		return $record->reason;
	}


	/**
	 *	更新credit limit  record status
	 *	@params	int	status
	 *	@return
	 */
	private function updateCreditLimitRecord($attributes, $condition, $params)
	{
		if(ThAgencyCreditlimit::updateAll($attributes, $condition, $params)) return true;
		throw new Exception('updateCreditlimitRecord Error, no record update!');
	}


	/**
	 *	根据主键id获取相应信息
	 *	@params	int	id
	 *	@return obj
	 */
	private function getCreditLimitRecord($id)
	{
		return ThAgencyCreditlimit::findOne($id); 
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
