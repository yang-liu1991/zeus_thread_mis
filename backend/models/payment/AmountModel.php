<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-01-09 10:58:28
 */
namespace backend\models\payment;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\RequestApi;
use common\models\AmountConversion;
use yii\web\NotFoundHttpException;
use backend\models\ThreadBaseModel;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThSpendReportSearch;


class AmountModel extends ThreadBaseModel
{
	const READ_ACCOUNT_API = 'https://graph.facebook.com/v2.9/act_%s';

	public $account_id;
	public $business_id;
	public $entity_id;
	public $date_start;
	public $date_stop;

	public $account_spend_info;


	public function rules()
	{
		return [
			[['account_id', 'business_id', 'entity_id', 'date_start', 'date_stop'], 'safe']
		];
	}


	/**
	 *	获取时间段内总的花费
	 *	@return int
	 */
	protected static function getSpendTotal($amountInfoList)
	{
		$spendTotal = 0;
		if($amountInfoList)
			foreach($amountInfoList as $amountInfo) $spendTotal += $amountInfo['spend'];
		return $spendTotal;
	}


	/**
	 *	根据account id和时间获取消耗数据
	 *	@params	$queryParams
	 *	return obj
	 */
	protected static function getSendReportData($queryParams)
	{
		$searchModel = new ThSpendReportSearch();
		$dataPrivader = $searchModel->search($queryParams);
		
		return $dataPrivader->getModels();	
	}


	/**
	 *	获取access_token
	 *	@params	str	business_id
	 *	@return str
	 */
	private function getAccessToken($business_id)
	{
		return $this->getAccessTokenByBusinessId($business_id);
	}


	/**
	 *	根据entity_id获取所有的account_id
	 *	@params	int	$entity_id
	 *	@return array
	 */
	public static function getAccountList($entity_id)
	{
		return ThAccountInfo::find()->where(['entity_id' => $entity_id, 
			'status' => ThAccountInfoSearch::getAccountStatus()['APPROVED']])->all(); 
	}

	
	/**
	 *	根据account_id获取amount_spent, balance
	 *	@params	str	$account_id
	 *	@return array
	 */
	protected static function getAccountInfo($account_id)
	{
		$accountObj = ThAccountInfo::find()->where(['fbaccount_id' => $account_id])->one();
		return ['amount_spent' => $accountObj->amount_spent, 'balance' => $accountObj->balance];
	}


	/**
	 *	将获取到的数据进行格式化返回
	 *	@params	obj	dataProvader
	 *	@return obj json
	 */
	public function formatAmountInfo($dataPrivader, $queryParams)
	{
		try {
			$datas = $dataPrivader->getModels();
			if($datas)
			{
				$amountInfoList = ['spendlist_info' => [], 'cost_info' => []];
				foreach($datas as $data)
				{
					$amountInfo = [];
					$amountInfo['account_id']	= $data->account_id;
					$amountInfo['spend']		= AmountConversion::centToDollar($data->spend);
					$amountInfo['date_start']	= $data->date_start;
					$amountInfo['date_stop']	= $data->date_stop;
					$this->business_id			= $data->accountInfo->business_id;
					$this->account_id			= $data->accountInfo->fbaccount_id;
					array_push($amountInfoList['spendlist_info'], $amountInfo);
				}

				$spend_total	= self::getSpendTotal($amountInfoList['spendlist_info']);
				$balance		= self::getAccountInfo($this->account_id)['balance'];
				/* 因为之前就是美分相加，所以这里不用再转换了 */
				$amountInfoList['cost_info']['spend_total']	= $spend_total;
				$amountInfoList['cost_info']['balance']		= AmountConversion::centToDollar($balance);

				/* 如果没有选择时间的话，这里返回默认数据 */
				if(!$queryParams['ThSpendReportSearch']['date_start'] && !$queryParams['ThSpendReportSearch']['date_stop'])
				{
					$spend_total = self::getAccountInfo($this->account_id)['amount_spent'];
					$amountInfoList['cost_info']['spend_total'] = AmountConversion::centToDollar($spend_total);
				}
				return $amountInfoList;
			}
			return Null;
		} catch(Exception $message) {
			Yii::error(sprintf('[formatAmountInfo] dataPrivader:%s', json_encode($dataPrivader)));
			return false;
		}
	}


	/**
	 *	根据account dataPrivader获取report 数据
	 *	@params	obj		data
	 *	@params	array	queryParams
	 *	@return
	 */
	public static function getSpendTotalData($queryParams)
	{
		try {
			if(array_key_exists('date_start', $queryParams['ThSpendReportSearch']) || 
				array_key_exists('date_stop', $queryParams['ThSpendReportSearch']))
			{
				$sendReportData = self::getSendReportData($queryParams);
				$spendTotal = self::getSpendTotal($sendReportData);
				return $spendTotal;
			} else {
				$spendTotal = self::getAccountInfo($queryParams['ThSpendReportSearch']['account_id'])['amount_spent'];
				return $spendTotal;
			}
			throw new Exception('getSpendTotalData Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getSpendTotalData] Exception, queryParams:%s, reason:%s',
				json_encode($queryParams), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	获取公司级别的消耗数据
	 */
	public static function getCompanyAmountTotal($queryParams)
	{
		$entity_id = $queryParams['ThAccountInfoSearch']['entity_id'];
		if(!array_key_exists('ThSpendReportSearch', $queryParams)) $queryParams['ThSpendReportSearch'] = [];
		$accountInfoList = self::getAccountList($entity_id);
		$companyAmountTotal = 0;	
		if(array_key_exists('date_start', $queryParams['ThSpendReportSearch']) ||
			array_key_exists('date_stop', $queryParams['ThSpendReportSearch']))
		{
			foreach($accountInfoList as $accountInfo)
			{
				$queryParams['ThSpendReportSearch']['account_id']	= $accountInfo->fbaccount_id;
				$sendReportData = self::getSendReportData($queryParams);
				$companyAmountTotal += self::getSpendTotal($sendReportData);
			}
			return $companyAmountTotal;
		} else {
			foreach($accountInfoList as $accountInfo)
			{
				$companyAmountTotal += self::getAccountInfo($accountInfo->fbaccount_id)['amount_spent'];
			}
			return $companyAmountTotal;
		}
		return 0;
	}


	/**
	 *	获取公司级别的report数据
	 */
	public function formatCompanyAmountInfo()
	{
		if($this->entity_id)
		{
			$accountInfoList = self::getAccountList($this->entity_id);
			if($accountInfoList)
			{
				$companyAmountInfo = [];
				foreach($accountInfoList as $accountInfo)
				{
					$accountAmountInfo = [];
					$this->account_id	= $accountInfo->fbaccount_id;
					$this->business_id	= $accountInfo->business_id;
					$queryParams = ['ThSpendReportSearch' => []];
					$queryParams['ThSpendReportSearch']['account_id']   = $this->account_id;
					$queryParams['ThSpendReportSearch']['date_start']   = $this->date_start;
					$queryParams['ThSpendReportSearch']['date_stop']	= $this->date_stop;
					$accountAmountInfo['account_id']	= $this->account_id;
					$spend_total	= self::getSpendTotalData($queryParams);
					$balance        = self::getAccountInfo($this->account_id)['balance'];
					if(!$this->date_start && !$this->date_stop)
						$spend_total = self::getAccountInfo($this->account_id)['amount_spent']; 
					$accountAmountInfo['spend_total']	= AmountConversion::centToDollar($spend_total);
					$accountAmountInfo['balance']		= AmountConversion::centToDollar($balance);
					array_push($companyAmountInfo, $accountAmountInfo);
				}
				return $companyAmountInfo;
			}
		}
		return false;
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
