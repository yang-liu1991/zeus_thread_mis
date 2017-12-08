<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-03-16 14:07:31
 */

namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\RequestApi;
use yii\data\ActiveDataProvider;
use common\struct\AccountChangeType;
use common\struct\AccountChangeStatus;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThChangeRecord;
use backend\models\record\ThChangeRecordSearch;
use backend\models\account\AccountBaseModel;


class ChangeNameModel extends AccountBaseModel
{
	/* 更新account name的接口*/
	const CHANGE_ACCOUNT_NAME_API	= 'https://graph.facebook.com/v2.9/act_%s';

	public $id;
	public $account_id;
	public $account_name;
	public $account_status;
	public $new_account_name;
	public $content;
	public $type;
	public $action;
	public $reason;
	public $error_message;
	public $upload_file;
	public $accounts = [];


	/**
	 *	@inheritdoc
	 */
	public function rules()
	{
		$rules = parent::rules();
		return array_merge($rules, [
			[['account_id'], 'required', 'on' => ['getAccountInfo', 'setAccountInfo'], 'message' => '{attribute}不能为空！'],
			[['new_account_name'], 'required', 'on' => ['setAccountInfo'], 'message' => '{attribute}不能为空！'],
			[['reason', 'new_account_name'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['user_id', 'company_id', 'type'], 'integer', 'on' => ['setAccountInfo']],
            [['reason', 'content'], 'string', 'on' => ['setAccountInfo']],
            [['account_id'], 'string', 'max' => 100],
			[['reason', 'account_name', 'new_account_name', 'content', 'upload_file', 'accounts', 'error_message'], 'safe', 'on' => ['setAccountInfo']]
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
			'new_account_name'	=> 'New Account Name',
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
			'setAccountInfo'	=> ['account_id', 'account_name', 'account_status', 'new_account_name', 'user_id', 'company_id', 'content', 'type', 'error_message', 'accounts', 'error_message'],
		]);
	}


	/**
	 *	请求Facebook接口更新Account Name操作
	 *	@return bool
	 */
	private function submitChangeAccount()
	{
		try {
			$url	= sprintf(self::CHANGE_ACCOUNT_NAME_API, $this->account_id);
			$params	= [
				'access_token'	=> Yii::$app->params['facebookApi']['access_token'],
				'name'			=> $this->new_account_name
			];
			$response = RequestApi::requestPost($url, http_build_query($params));
            Yii::info(sprintf("[submitChangeAccount] accountId:%s, send_url:%s, params:%s, response:%s",
				$this->account_id, $url, json_encode($params), $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'success') && $result->success == true) return true;
				return $result;
			}
			throw new Exception('submitChangeAccount response error!!');
		} catch(Exception $message) {
			Yii::error(sprintf('[submitChangeAccount] Exception, account_id:%s, send_url:%s, params:%s, response:%s, reason:%s',
				$this->account_id, $url, json_encode($params), $response, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	保存帐户更新的记录
	 *	@return bool
	 */
	public function saveChangeRecord()
	{
		$transaction = Yii::$app->db->beginTransaction();
		try {
			foreach($this->accounts as $account)
			{
				$this->setAttributes($account);
				if($this->validate())
				{
					$recordModel	= new ThChangeRecord();
					$recordModel->attributes	= $this->attributes;
					$recordModel->type			= AccountChangeType::ACCOUNT_NAME;
					$recordModel->content		= json_encode([
						'old_account_name' => $this->account_name, 
						'new_account_name' => $this->new_account_name]);
					if($recordModel->validate() && $recordModel->save())
					{
						Yii::info(sprintf('[saveChangeRecord] Success, account_id:%s, attributes:%s', 
							$this->account_id, json_encode($recordModel->attributes)));
					}
				} else {
					$this->error_message = $this->getErrors();
					throw new Exception('saveChangeRecord validate error!');	
				}
			}
			$transaction->commit();
			return true;
		} catch(Exception $message) {
			$transaction->rollBack();
			Yii::error(sprintf('[saveChangeRecord] Exception, account_id:%s, attributes:%s, reason:%s',
				$this->account_id, json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	更新account name 操作	
	 */
	public function changeAccountName()
	{
		try{
			$changeRecord	= $this->getChangeRecord($this->id);
			if($changeRecord)
			{
				$content	= json_decode($changeRecord->content);
				$this->account_id		= !empty($changeRecord->account_id) ? $changeRecord->account_id : '';
				$this->new_account_name	= !empty($content->new_account_name) ? $content->new_account_name : '';
				if(!$this->new_account_name) throw new Exception('Unknow new_account_name...');
				$requestResult = $this->submitChangeAccount();

				if(is_object($requestResult) && property_exists($requestResult, 'error'))
				{
					$this->error_message	= $requestResult->error;
					throw new Exception(sprintf('submitChangeAccount requestResult error, error_message : %s', json_encode($this->error_message)));
				} elseif($requestResult) {
					$transaction	= Yii::$app->db->beginTransaction();
					if($this->updateAccountInfo([
						'fbaccount_name' => $this->new_account_name], 'fbaccount_id=:fbaccount_id', 
						[':fbaccount_id' => $this->account_id]) && $this->updateChangeRecord([
						'status' => AccountChangeStatus::ACCOUNT_CHANGE_SUCCESS], 'id=:id', [':id' => $this->id]
					)) 
					{
						$transaction->commit();
						return true;
					}
                    $transaction->rollBack();
					throw new Exception('updateAccountInfo or updateChangeRecord Error!');
				}
			}
		} catch(Exception $message) {
			$this->new_account_name	= !empty($content->old_account_name) ? $content->old_account_name : '';
			$this->submitChangeAccount();
			Yii::error(sprintf('[changeAccountName] Exception, account_id:%s, new_account_name:%s, reason:%s',
				$this->account_id, $this->new_account_name, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	驳回更新name的方法
	 */
	public function rejectChange()
	{
		try {
			$this->reason = json_encode([trim($this->reason)]);
			if($this->updateChangeRecord([
				'status' => AccountChangeStatus::ACCOUNT_CHANGE_FAILED, 
				'reason' => $this->reason], 'id=:id', [':id' => $this->id]))
			{
				Yii::info('[rejectChange] Success, id:%d, status:%d, reject_reason:%s',
					$this->id, AccountChangeStatus::ACCOUNT_CHANGE_FAILED, $this->reason);
				return true;
			}
		} catch(Exception $message) {
			Yii::error('[rejectChange] Exception, id:%d, status:%d, reject_reason:%s',
				$this->id, AccountChangeStatus::ACCOUNT_CHANGE_FAILED, $this->reason);
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
		$record	= $this->getChangeRecord($id);
		return $record->reason;
	}


	/**
	 *	根据主键获取更新记录作为提交给Facebook的数据
	 *	@params	int	id
	 *	@return	
	 */
	private function getChangeRecord($id)
	{
		return ThChangeRecord::findOne($id);
	}


	/**
	 *	更新系统中的名称
	 *	params	array	attributes
	 *	params	array	condition
	 *	params	array	params
	 *	@return bool
	 */
	private function updateAccountInfo($attributes, $condition, $params)
	{
		if(ThAccountInfo::updateAll($attributes, $condition, $params)) return true;
		throw new Exception('updateAccountInfo Error, no record update!');
	}


	/**
	 *	更新提交记录的状态
	 */
	private function updateChangeRecord($attributes, $condition, $params)
	{
		if(ThChangeRecord::updateAll($attributes, $condition, $params))  return true;
		throw new Exception('updateChangeRecord Error, no record update!');
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
