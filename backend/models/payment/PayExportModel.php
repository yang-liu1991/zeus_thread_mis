<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-12-29 10:29:17
 */
namespace backend\models\payment;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\DownloadExcel;
use backend\models\payment\PaymentModel;
use backend\models\record\ThAccountInfoSearch;


class PayExportModel extends PaymentModel
{
	/* 定义导出数据的headline */
	private $headlines = [
		'Account ID',
		'公司英文名称',
		'公司中文名称',
		'广告商产业类型',
		'结算方式',
		'实际付款主体',
		'付款备注信息'
		];


	/**
	 *	获取结算方式
	 *	$params	int	pay_type
	 *	@return
	 */
	public static function getPaymentType($pay_type)
	{
		return  ThAccountInfoSearch::getPaymentType($pay_type);
	}


	/**
	 *	重新构建数据，以二维数组形式返回
	 *	@params	obj data
	 *	@return array
	 */
	private function buildArrayList($exportDatas)
	{
		try {
			if($exportDatas)
			{
				$exportArrayData = [];
				foreach($exportDatas as $exportData)
				{
					$recordList = [];
					$recordList[] = !empty($exportData->fbaccount_id) ? $exportData->fbaccount_id : '';
					$recordList[] = !empty($exportData->entityInfo->name_en) ? $exportData->entityInfo->name_en : '';
					$recordList[] = !empty($exportData->entityInfo->name_zh) ? $exportData->entityInfo->name_zh : '';
					$recordList[] = !empty($exportData->entityInfo->vertical) ? $exportData->entityInfo->vertical : '';
					$recordList[] = !empty($exportData->pay_type) ? self::getPaymentType($exportData->pay_type) : '';
					$recordList[] = !empty($exportData->pay_name_real) ? $exportData->pay_name_real : '';
					$recordList[] = !empty($exportData->pay_comment) ? $exportData->pay_comment : '';
					array_push($exportArrayData, $recordList);
				}
				return $exportArrayData;
			}
			throw new Exception('buildArrayList Error, exportDatas is Null!');
		} catch(Exception $message) {
			Yii::error(sprintf('[buildArrayList] Exception, exportDatas:%s, reason:%s',
				json_encode($exportDatas), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	构建excel对象，设置属性
	 *	@params	obj	exportDatas
	 *	@return obj	objPHPExcel
	 */
	public function buildExcelObj($exportDatas)
	{
		try{
			$buildArrayDatas = $this->buildArrayList($exportDatas);
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
	public function downloadExcelFile($objectPHPExcel, $excelFileName='帐户付款信息表')
	{
		return DownloadExcel::downloadExcelFile($objectPHPExcel, $excelFileName);	
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
