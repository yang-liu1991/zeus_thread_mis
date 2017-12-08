<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-07 16:02:47
 */
namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\Conversion;
use common\models\DownloadExcel;


class ExportModel extends Model
{

	/* 定义导出数据的headline */
	private $headlines = [
		'UPDATED_AT',
		'NAME_ZH',
		'NAME_EN',
		'OFFICIAL_WEBSITE_URL',
		'PROMOTABLE_URLS',
		'PROMOTABLE_PAGE_IDS',
		'FULL_NAME',
		'CITY',
		'STATE',
		'ZIP',
		'COUNTRY',
		'ADDRESS_ZH',
		'VERTICAL',
		'SUBVERTICAL',
		'COMPANY_ID',
		'FBACCOUNT_ID',
		'REQUEST_ID',
		'TIMEZONE_ID',
		'STATUS',
		'REASONS',
	];


	/**
	 *	返回状态
	 */
	private function getAccountStatus($status)
	{
		switch($status)
		{
			case 0:	return 'WAITING';
			case 1:	return 'PENDING';
			case 2:	return 'UNDERREVIEW';
			case 3:	return 'APPROVED';
			case 4:	return 'RE_CHANGE';
			case 5:	return 'DISAPPROVED';
			case 6:	return 'CANCELLED';
			case 7:	return 'ABNORMAL';
			case 8:	return 'FORCEOUT';
		}
	}


	/**
	 *	转换getPromotableUrls
	 *	@params	obj	getPromotableUrls
	 *	@return str
	 */
	private function getPromotableUrls($promotable_urls)
	{
		$websiteAll	= json_decode($promotable_urls, true);
		$websiteArray	= !empty($websiteAll['normal']) ? $websiteAll['normal'] : '';
		if($websiteArray)
			return implode(',', $websiteArray);
		return $websiteArray;
	}
	

	/**
	 *	重新构建数据，以二维数组形式返回
	 *	@params	obj	data
	 *	@return array
	 */
	private function buildArrayList($exportDatas)
	{
		try{
			$exportArrayData = [];
			foreach($exportDatas as $exportData)
			{
				$recordList	= [];
				$addressEn		= json_decode($exportData->entityInfo->address_en);
				$recordList[]	= !empty($exportData->updated_at) ? date('Ymd', $exportData->updated_at) : '';
				$recordList[]	= !empty($exportData->entityInfo->name_zh) ? $exportData->entityInfo->name_zh : '';
				$recordList[]	= !empty($exportData->entityInfo->name_en) ? $exportData->entityInfo->name_en : '';
				$recordList[]	= !empty($exportData->entityInfo->official_website_url) ? $exportData->entityInfo->official_website_url : '';
				$recordList[]	= !empty($exportData->entityInfo->promotable_urls) ? $this->getPromotableUrls(
					$exportData->entityInfo->promotable_urls) : '';
				$recordList[]	= !empty($exportData->entityInfo->promotable_page_ids) ? Conversion::getPromotablePageIds(
					$exportData->entityInfo->promotable_page_ids) : '';
				$recordList[]	= !empty($addressEn->full_name)	? $addressEn->full_name : '';
				$recordList[]	= !empty($addressEn->city)		? $addressEn->city		: '';
				$recordList[]	= !empty($addressEn->state)		? $addressEn->state		: '';
				$recordList[]	= !empty($addressEn->zip)		? $addressEn->zip		: '';
				$recordList[]	= !empty($addressEn->country)	? $addressEn->country	: '';
				$recordList[]	= !empty($exportData->entityInfo->address_zh)	? $exportData->entityInfo->address_zh	: '';
				$recordList[]	= !empty($exportData->entityInfo->vertical)		? $exportData->entityInfo->vertical		: '';
				$recordList[]	= !empty($exportData->entityInfo->subvertical)	? $exportData->entityInfo->subvertical	: '';
				$recordList[]	= !empty($exportData->company_id)	? Conversion::getCompany($exportData->company_id) : '';
				$recordList[]	= !empty($exportData->fbaccount_id) ? $exportData->fbaccount_id : '';
				$recordList[]	= !empty($exportData->request_id)	? $exportData->request_id	: '';
				$recordList[]	= !empty($exportData->timezone_id)	? $exportData->timezone_id	: '';
				$recordList[]	= !empty($exportData->status)		? $this->getAccountStatus($exportData->status) : '';
				$recordList[]	= !empty($exportData->reasons)		? $exportData->reasons : '';
				array_push($exportArrayData, $recordList);
			}

			return $exportArrayData;
		} catch(Exception $message) {
			Yii::error(sprintf("[buildArrayList] Exception, exportDatas:%s, reason:%s", 
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
	public function downloadExcelFile($objectPHPExcel, $excelName='帐户申请信息表')
	{
		return DownloadExcel::downloadExcelFile($objectPHPExcel, $excelName);
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
