<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-01-03 17:57:42
 */

namespace console\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\HttpException;
use common\models\RequestApi;
use common\models\AmountConversion;
use console\models\ConsoleBaseModel;
use backend\models\record\ThSpendReport;


class SpendReportModel extends ConsoleBaseModel
{
	/* 获取account spend的接口 */
	const READ_ACCOUNT_SPEND	= 'https://graph.facebook.com/v2.10/act_%s/insights';
	const BLUE_FOCUS_BM			= '511273569054473';

	public $account_id;
	public $spend;
	public $date_start;
	public $date_stop;
	public $business_id;
	public $access_token;


	public function rules()
	{
		return [];
	}


	/**
	 *	获取account spend信息
	 *	@params	str	$account_id
	 *	@return obj
	 */
	private function getSpendData($account_id, $access_token)
	{
		try {
			$url = sprintf(self::READ_ACCOUNT_SPEND, $account_id);
			$params	= ['access_token' => $access_token, 'date_preset' => 'yesterday'];
			$encode_url = sprintf('%s?%s', $url, http_build_query($params));
			$response = RequestApi::requestGet($encode_url);
			Yii::info(sprintf("[getSpendData] Success, account_id:%s, encode_url:%s, response:%s",
				$account_id, $encode_url, $response));
			if($response) return $response;
			throw new Exception('getSpendData Response Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getSpendData] Exception, account_id:%s, reason:%s',
				$account_id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	将获取到的spend数据赋值给类属性
	 *	@params	array	$data
	 *	return
	 */
	private function setSpendAttributes($data)
	{
		$this->account_id	= $data->account_id;
		$this->spend		= AmountConversion::dollarToCent($data->spend);
		$this->date_start	= $data->date_start;
		$this->date_stop	= $data->date_stop;
	}


	/**
	 *	当获取不到spend数据时，将赋于默认值
	 *	@params	str	account_id
	 *	@return
	 */
	private function setDefaultAttributes($account_id)
	{
		$this->account_id	= $account_id;
		$this->spend		= 0;
		$this->date_start	= date('Y-m-d', strtotime('-1 day'));
		$this->date_stop	= date('Y-m-d', strtotime('-1 day'));
	}


	/**
	 *	保存spend data
	 *	@return
	 */
	private function saveSpendData()
	{
		try {
			$spendModel = new ThSpendReport();
			$spendModel->attributes = $this->attributes;
			if($spendModel->validate() && $spendModel->save())
			{
				Yii::info(sprintf("[saveSpendData] Success, data:%s", json_encode($this->attributes)));
				return true;
			}
			throw new Exception(sprintf('saveSpendData save error:%s', json_encode($spendModel->getErrors())));
		} catch(Exception $message) {
			Yii::error(sprintf('[saveSpendData] Exception, data:%s, reason:%s',
				json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	查询是否已经存在相应记录
	 *	@return bool
	 */
	private function selectSpendData()
	{
		$result = ThSpendReport::find()->where([
			'account_id'	=> $this->account_id,
			'date_start'	=> $this->date_start,
			'date_stop'		=> $this->date_stop])->one();
		if($result) return true;
		return false;
	}


	/**
	 *	执行方法
	 *	如果没有获取到business_id，则默认通过kim access_token进行获取数据
	 *	@return
	 */
	public function run($account_id, $business_id)
	{
		try {
			if($business_id)
			{
				$access_token	= $this->getAccessTokenByBusinessId($business_id);
			} else {
				$access_token	= $this->getAccessTokenByBusinessId(self::BLUE_FOCUS_BM);
			}

			$spendData = $this->getSpendData($account_id, $access_token);
			if($spendData)
			{
				$result = json_decode($spendData);
				if(property_exists($result, 'data') && !empty($result->data))
				{
					foreach($result->data as $data)
					{
						$this->setSpendAttributes($data);
						if(!$this->selectSpendData())
							if($this->saveSpendData()) return true;
					}
				} else {
					$this->setDefaultAttributes($account_id);
					if(!$this->selectSpendData())
						if($this->saveSpendData()) return true;
					return $result;
				}
			}
		} catch(Exception $message) {
			Yii::error(sprintf('[SpendReportModel run] Exception, account_id:%s, business_id:%s, reason:%s',
				$account_id, $business_id, $message->getMessage()));
			return false;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
