<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-01-18 17:51:47
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


class CompanyExportModel extends AmountModel
{
	/* 定义导出数据的headline */
	private $headlines = [
		'ID',
		'公司英文名称',
		'公司中文名称',
		'付款名称',
		'结算方式',
		'实际付款主体',
		'开始时间',
		'结束时间',
		'总消耗'
	];


	/**
	 *	获取下载数据
	 *	@params $array  entityDataList
	 *	@params $array  queryParams
	 *	@return exportData
	 */
	private function buildArrayList($entityDataList, $queryParams)
	{
		try {
			if($entityDataList)
			{
				$buildArrayDatas = [];
				foreach($entityDataList as $entityData)
				{
					$recordList = [];
					$queryParams['ThAccountInfoSearch']['entity_id'] = $entityData->id;
					$companyAmountTotal = self::getCompanyAmountTotal($queryParams);
					$recordList[]	= $entityData->id;
					$recordList[]	= $entityData->name_en;
					$recordList[]	= $entityData->name_zh;
					$recordList[]	= $entityData->payname;
					$recordList[]	= !empty($entityData->accountInfo) ? 
						ThAccountInfoSearch::getPaymentType($entityData->accountInfo[0]->pay_type) : '';
					$recordList[]	= !empty($entityData->accountInfo) ?
						$entityData->accountInfo[0]->pay_name_real : '';
					$recordList[]	= !empty($queryParams['ThSpendReportSearch']['date_start']) ? $queryParams['ThSpendReportSearch']['date_start'] : '';
					$recordList[]	= !empty($queryParams['ThSpendReportSearch']['date_stop']) ? $queryParams['ThSpendReportSearch']['date_stop'] : '';
					$recordList[]	= AmountConversion::centToDollar($companyAmountTotal);
					array_push($buildArrayDatas, $recordList);
				}
				return $buildArrayDatas;
			}
			throw new Exception('buildArrayList Error, exportDatas is Null!');
		} catch(Exception $message) {
			Yii::error(sprintf('[buildArrayList] Exception, entityDataList:%s. queryParams:%s, reason:%s',
				json_encode($entityDataList), json_encode($queryParams), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	构建excel对象，设置属性
	 *	@params	array	$entityDataList
	 *	@params array	$queryParams
	 *	@return obj		objectPHPExcel
	 */
	public function buildExcelObj($entityDataList, $queryParams)
	{
		try {
			$buildArrayDatas = $this->buildArrayList($entityDataList, $queryParams);
			if(!$buildArrayDatas)
				throw new Exception('buildExcelObj Error, buildArrayDatas is Null!');
			$objectPHPExcel	= DownloadExcel::buildExcelObject($this->headlines, $buildArrayDatas);	
			if($objectPHPExcel)	return $objectPHPExcel;
			throw new Exception('buildExcelObj Error, buildExcelObj failed!');
		} catch(Exception $message) {
			Yii::error(sprintf('[buildExcelObj] Exception, entityDataList:%s, queryParams:%s, reason:%s',
				json_encode($entityDataList), json_encode($queryParams), $message->getMessage()));
			return false;
		}	
	}


	/**
	 *	输出excel文件
	 */
	public function downloadExcelFile($objectPHPExcel, $excelFileName='广告主金额数据表')
	{
		return DownloadExcel::downloadExcelFile($objectPHPExcel, $excelFileName);	
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
