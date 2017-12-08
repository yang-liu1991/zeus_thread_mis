<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-12-23 10:58:46
 */
namespace backend\models\payment;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use backend\models\user\User;
use yii\web\NotFoundHttpException;
use backend\models\ThreadBaseModel;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThPaymentHistory;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThPaymentHistorySearch;

class PaymentModel extends ThreadBaseModel
{
	public $account_id;
	public $pay_name_real;
	public $pay_type;
	public $pay_comment;


	public function rules()
	{
		return [
			[['account_id', 'pay_name_real', 'pay_type'], 'required', 'on' => ['update_payment']],
			[['account_id', 'pay_type'], 'integer', 'on' => ['update_payment']],
			[['pay_type'], 'in', 'range' => [ThAccountInfoSearch::CPA_AGENT, ThAccountInfoSearch::COST_AGENT, ThAccountInfoSearch::COST_CUSTOMER, ThAccountInfoSearch::CPA_THIRD], 'on' => ['update_payment']],
			[['pay_name_real'], 'string', 'max' => 255, 'on' => ['update_payment']],
			[['pay_name_real', 'pay_comment'], 'filter', 'filter' => 'trim'],
			[['account_id', 'pay_name_real', 'pay_type', 'pay_comment'], 'safe']
		];
	}


	/**
	 *	inheritdoc
	 */
	public function scenarios()
	{
		 $scenarios = parent::scenarios();
		 return array_merge($scenarios, [
			 'update_payment' => ['account_id', 'pay_name_real', 'pay_type', 'pay_comment'],
		 ]);
	}

	
	/**
	 *	更新付款信息
	 *	@params	int	$account_id
	 *	@return bool
	 */
	public function paymentUpdate()
	{
		try {
			$transaction = Yii::$app->db->beginTransaction();
			$paymentModel = ThAccountInfo::find()->where(['fbaccount_id' => $this->account_id])->one();
			$paymentModel->pay_name_real	= $this->pay_name_real;
			$paymentModel->pay_type			= $this->pay_type;
			$paymentModel->pay_comment		= $this->pay_comment;
			if($paymentModel->save() && $this->savePaymentHistory())
			{
				Yii::info(sprintf("[paymentUpdate] Success, user_id:%s, account_id:%s, data:%s",
					$this->user_id, $this->account_id, json_encode($this->attributes)));
				$transaction->commit();
				return true;
			}
			throw new Exception('paymentUpdate Error!');
		} catch(Exception $message) {
			$transaction->rollBack();
			Yii::error(sprintf("[paymentUpdate] Exception, user_id:%d, account_id:%d, data:%s, reason:%s",
				$this->user_id, $this->account_id, json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	获取付款信息
	 *	@params	int	$account_id
	 */
	public function getPaymentAttributes()
	{
		try {
			$paymentModel = ThAccountInfo::find()->where(['fbaccount_id' => $this->account_id])->one();
			if($paymentModel) 
			{
				$this->attributes	= $paymentModel->attributes;
				Yii::info(sprintf("[getPaymentAttributes] Success, user_id:%s, account_id:%s, data:%s",
					$this->user_id, $this->account_id, json_encode($paymentModel->attributes)));
				return true;
			}
			throw new Exception('getPaymentAttributes Exception!');	
		} catch(Exception $message) {
			Yii::error(sprintf("[getPaymentAttributes] Exception, user_id:%d, account_id:%s, reason:%s",
				$this->user_id, $this->account_id, $message->getMessage()));
			return false;
		}
	}

	/**
	 *	获取操作记录
	 *	@return array
	 */
	private function getPaymentHistory()
	{
		try {
			$selectResult = ThPaymentHistory::find()->where(['account_id' => $this->account_id, 
				'company_id' => $this->company_id])->orderBy('id desc')->all();
			return $selectResult;
		} catch(Exception $message) {
			Yii::error(sprintf("[getPaymentHistory] Exception, user_id:%d, account_id:%s, reason:%s",
				$this->user_id, $this->account_id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	保存操作记录
	 */
	private function savePaymentHistory()
	{
		try {
			$paymentHistoryModel = new ThPaymentHistory();
			$paymentHistoryModel->attributes = $this->attributes;
			$record = ThPaymentHistory::find()->where(['account_id' => $this->account_id])->one();
			/* 如果有此account的记录，则为更新 */
			if($record) $paymentHistoryModel->action_type = ThPaymentHistorySearch::ACTION_UPDATED;
			if($paymentHistoryModel->validate() && $paymentHistoryModel->save())
			{
				Yii::info(sprintf("[savePaymentHistory] Success, user_id:%s, account_id:%s, data:%s",
					$this->user_id, $this->account_id, json_encode($paymentHistoryModel->attributes)));
				return true;
			}
			throw new Exception('savePaymentHistory Exception!');
		} catch(Exception $message) {
			Yii::error(sprintf("[savePaymentHistory] Exception, user_id:%d, account_id:%s, reason:%s",
				$this->user_id, $this->account_id, $message->getMessage()));
			return false;
		}
	}

	/**
	 *	整理返回给前端的数据
	 */
	public function formatPaymentHistory()
	{
		try {
			$paymentHistorys = $this->getPaymentHistory();
			$paymentHistoryList = [];
			if($paymentHistorys)
			{
				foreach($paymentHistorys as $paymentHistory)
				{
					$paymentRecord = [];
					$paymentRecord['username']		= User::findIdentity($paymentHistory->user_id)->email;
					$paymentRecord['action_type']	= ($paymentHistory->action_type == ThPaymentHistorySearch::ACTION_CREATED) ? '添加' : '更新';
					$paymentRecord['pay_name_real']	= $paymentHistory->pay_name_real;
					$paymentRecord['pay_type']		= ThAccountInfoSearch::getPaymentType($paymentHistory->pay_type);
					$paymentRecord['created_at']	= date('Y-m-d H:i:s', $paymentHistory->created_at);
					array_push($paymentHistoryList, $paymentRecord);
				}
			}
			return $paymentHistoryList;
		} catch(Exception $message) {
			Yii::error(sprintf("[formatPaymentHistory] Exception, user_id:%d, account_id:%s, reason:%s",
				$this->user_id, $this->account_id, $message->getMessage()));
			return false;
		}
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
