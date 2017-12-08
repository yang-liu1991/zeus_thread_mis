<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-01-11 10:34:16
 */
namespace backend\models\payment;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\DownloadExcel;
use common\models\AmountConversion;
use backend\models\payment\AmountModel;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThSpendReportSearch;


class DaysExportModel extends AmountModel
{
	/* 定义导出数据的headline */
	private $headlines = [
		'Account ID',
		'公司英文名称',
		'公司中文名称',
		'付款名称',
		'结算方式',
		'实际付款主体',
		'开始时间',
		'结束时间',
		'总消耗',
		'余额',
	];


	/**
	 *	获取时间段内总的花费
	 *	@return int
	 */
	protected static function getSpendTotal($spendReportDatas)
	{
		$spendTotal = 0;
		if($spendReportDatas)
			foreach($spendReportDatas as $spendReportData) { $spendTotal += $spendReportData->spend; }
		return $spendTotal;
	}

	/**
	 *	获取下载数据
	 *	@params	$array	accountDataList
	 *	@params	$array	queryParams
	 *	@return exportData
	 */
	private function buildArrayList($accountDataList, $queryParams)
	{
		try {
			if($accountDataList)
			{
				$exportArrayData = [];
				foreach($accountDataList as $accountData)
				{
					$recordList = [];
					$spendTotal = 0;
					$queryParams['ThSpendReportSearch']['account_id'] = $accountData->fbaccount_id;
					$spendReportData	= self::getSendReportData($queryParams);
					if(self::getSpendTotal($spendReportData))
					{
						$spendTotal = self::getSpendTotal($spendReportData);
					}
					
					if(!$queryParams['ThSpendReportSearch']['date_start'] && !$queryParams['ThSpendReportSearch']['date_stop']) 
					{
						$spendTotal = self::getAccountInfo($accountData->fbaccount_id)['amount_spent'];
					}

					$recordList[]	= !empty($accountData->fbaccount_id) ? $accountData->fbaccount_id : '';
					$recordList[]	= !empty($accountData->entityInfo->name_en) ? $accountData->entityInfo->name_en : '';
					$recordList[]	= !empty($accountData->entityInfo->name_zh) ? $accountData->entityInfo->name_zh : '';
					$recordList[]	= !empty($accountData->entityInfo->payname) ? $accountData->entityInfo->payname : '';
					$recordList[]	= !empty($accountData->pay_type) ? ThAccountInfoSearch::getPaymentType($accountData->pay_type) : '';
					$recordList[]	= !empty($accountData->pay_name_real) ? $accountData->pay_name_real : '';
					$recordList[]	= !empty($queryParams['ThSpendReportSearch']['date_start']) ? $queryParams['ThSpendReportSearch']['date_start'] : 0;
					$recordList[]	= !empty($queryParams['ThSpendReportSearch']['date_stop']) ? $queryParams['ThSpendReportSearch']['date_stop'] : 0;
					$recordList[]	= AmountConversion::centToDollar($spendTotal);
					$recordList[]	= AmountConversion::centToDollar($accountData->balance);
					array_push($exportArrayData, $recordList);
				}
				return $exportArrayData;
			}
		} catch(Exception $message) {
			Yii::error(sprintf('[buildArrayList] Exception, accountDataList:%s, queryParams:%s, reason:%s', 
				json_encode($accountDataList), json_encode($queryParams), $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	构建excel对象，设置属性
	 *	@params	obj	exportDatas
	 *	@return obj	objPHPExcel
	 */
	public function buildExcelObj($exportDatas, $queryParams)
	{
		try{
			$buildArrayDatas = $this->buildArrayList($exportDatas, $queryParams);
			if(!$buildArrayDatas) 
				throw new Exception('buildExcelObj Error, buildArrayDatas is Null!');
			$objectPHPExcel	= DownloadExcel::buildExcelObject($this->headlines, $buildArrayDatas);	
			if($objectPHPExcel)	return $objectPHPExcel;
			throw new Exception('buildExcelObj Error, buildExcelObj failed!');
		} catch(Exception $message) {
			Yii::error(sprintf("[buildExcelObj] Exception, exportDatas:%s, reason:%s", 
				json_encode($exportDatas), $message->getMessage()));
			return false;	
		}
	}


	/**
	 *	输出excel文件
	 */
	public function downloadExcelFile($objectPHPExcel, $excelFileName='广告主天级金额数据表')
	{
		return DownloadExcel::downloadExcelFile($objectPHPExcel, $excelFileName);	
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
