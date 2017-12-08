<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-01-19 15:01:16
 */
namespace console\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\HttpException;
use common\models\RequestApi;
use common\models\AmountConversion;
use console\models\ConsoleBaseModel;
use backend\models\record\ThAccountInfo;


class AccountSpendModel extends ConsoleBaseModel
{
	/* 获取account spend的接口 */
	const READ_ACCOUNT_SPEND	= 'https://graph.facebook.com/v2.10/act_%s';
	const BLUE_FOCUS_BM         = '511273569054473';

	public $account_id;
	public $spend_cap;
	public $amount_spent;
	public $balance;

	public function rules()
	{
		return [
			[['spend_cap', 'amount_spent', 'balance'], 'integer'],
			[['account_id', 'spend_cap', 'amount_spent', 'balance'], 'safe'],
		];
	}


	/**
	 *	获取account的消耗数据
	 *	@params	str	$account_id
	 *	@params	str	$access_token
	 *	@return
	 */
	private function getAccountAmountInfo($account_id, $access_token)
	{
		try {
			$url = sprintf(self::READ_ACCOUNT_SPEND, $account_id);
			$params	= ['access_token' => $access_token, 'fields' => 'account_id,spend_cap,amount_spent,balance'];
			$encode_url = sprintf('%s?%s', $url, http_build_query($params));
			$response = RequestApi::requestGet($encode_url);
			Yii::info(sprintf("[getAccountAmountInfo] Success, account_id:%s, encode_url:%s, response:%s",
				$account_id, $encode_url, $response));
			if($response) return $response;
			throw new Exception('getAccountAmountInfo Response Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getAccountAmountInfo] Exception, account_id:%s, reason:%s',
				$account_id, $message->getMessage()));
			return false;
		}	
	}


	/**
	 *	将请求到的额度、消耗、余额等数据进行保存
	 *	@params	str	$account_id
	 *	@return bool
	 */
	private function saveAccountAmountInfo($account_id)
	{
		try {
			$command = $this->getDbConnection()->createCommand();
			$command->update('th_account_info', [
				'spend_cap' => $this->spend_cap,
				'amount_spent' => $this->amount_spent,
				'balance' => $this->balance], 'fbaccount_id=:fbaccount_id', [':fbaccount_id' => $account_id]
			);
			$result = $command->execute();
			Yii::info(sprintf('[saveAccountAmountInfo] Success, account_id:%s, attributes:%s',
				$account_id, json_encode($this->attributes)));
			return true;
		} catch(Exception $message) {
			Yii::error(sprintf('[saveAccountAmountInfo] Exception, account_id:%s, attributes:%s, reason:%s',
				$account_id, json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	这个是主要的控制方法
	 */
	public function run($account_id, $business_id)
	{
		try {
			if($business_id) 
			{
				$access_token   = $this->getAccessTokenByBusinessId($business_id);
			} else {
				$access_token   = $this->getAccessTokenByBusinessId(self::BLUE_FOCUS_BM);
			}
			$accountAmountInfo = $this->getAccountAmountInfo($account_id, $access_token);
			if($accountAmountInfo)
			{
				$result = json_decode($accountAmountInfo, true);
				if(!array_key_exists('error', $result))
				{
					$this->setAttributes($result);	
					if($this->saveAccountAmountInfo($account_id)) return true;
				}
				return false;
			}
			throw new Exception('run Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[run] Falied account_id:%s, business_id:%s, reason:%s',
				$account_id, $business_id, $message->getMessage()));
			return false;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
