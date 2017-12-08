<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-11-07 16:02:47
 */
namespace backend\models\creatives;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\Conversion;
use common\models\DownloadExcel;
use backend\models\ThreadBaseModel;


class CreativesExportModel extends ThreadBaseModel
{

	/* 定义导出数据的headline */
	private $headlines = [
		'Ad Account Id',
		'Ad Id',
		'Promoted Url',
        'Ad Message',
        'App Name',
        'Category',
        'Developer',
        'Install Number',
        'Last Update',
	];


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
				$recordList[]	= !empty($exportData->account_id) ? $exportData->account_id : '';
				$recordList[]	= !empty($exportData->ad_id) ? $exportData->ad_id : '';
				$recordList[]	= !empty($exportData->promoted_url) ? $exportData->promoted_url : '';
                $recordList[]   = !empty($exportData->ad_message) ? hex2bin($exportData->ad_message) : '';
                if($exportData->app_details)
                {
                    $app_details_obj = json_decode($exportData->app_details);
                } else {
                    $app_details_obj = (object)[];
                }
                $recordList[]   = property_exists($app_details_obj, 'app_name') ? $app_details_obj->app_name : '';
                $company_category = '';
                if(property_exists($app_details_obj, 'category'))
                {
                    $category = $app_details_obj->category;
                    if(property_exists($category, 'primary_category')) $company_category = $category->primary_category.' ';
                    if(property_exists($category, 'subtitle_category')) $company_category .= $category->subtitle_category;
                }
                $recordList[]   = $company_category;
                $recordList[]   = property_exists($app_details_obj, 'developer') ? $app_details_obj->developer : '';
                $recordList[]   = property_exists($app_details_obj, 'install_number') ? $app_details_obj->install_number : '';
                $recordList[]   = property_exists($app_details_obj, 'update_time') ? $app_details_obj->update_time : '';
				array_push($exportArrayData, $recordList);
			}

			return $exportArrayData;
		} catch(Exception $message) {
			Yii::error(sprintf("[buildArrayList] Exception, exportDatas:%s, reason:%s", 
				json_encode($exportDatas), $message->getMessage()));
			return [];
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
			return False;
		}
	}


	/**
	 *	输出excel文件
	 */
	public function downloadExcelFile($objectPHPExcel, $excelName='创意监测信息表')
	{
		return DownloadExcel::downloadExcelFile($objectPHPExcel, $excelName);
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
