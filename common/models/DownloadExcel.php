<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-11-06 19:12:21
 */

namespace common\models;

use Yii;
use yii\base\Model;
use PHPExcel;
use PHPExcel\Cell;
use PHPExcel\IOFactory;


class DownloadExcel extends Model
{

	/**
	 *	构建excelObject
	 *	@params	array	$headlines
	 *	@params	obj		$exportDatas
	 *	@return obj		$objectPHPExcel
	 */
	public static function buildExcelObject($headlines, $buildArrayDatas)
	{
		try {
			$objectPHPExcel = new PHPExcel();
			$objectPHPExcel->setActiveSheetIndex(0);
			$maxColumn	= count($headlines);
			$maxRow		= count($buildArrayDatas);

			/* 设置表头字段 */
			for($i=0; $i<$maxColumn; $i++)
			{
				$pCoordinate = \PHPExcel_Cell::stringFromColumnIndex($i).'1';
				$objectPHPExcel->getActiveSheet()->setCellValue($pCoordinate, $headlines[$i]);
			}
			
			/* 遍历二维数组，依次写入到objectPHPExcel对象中 */
			for($i=2; $i<$maxRow+2; $i++)
			{
				for($j=1; $j<$maxColumn+1; $j++)
				{
					/* 设置每列的值，A2、B2、C2 */
					$pCoordinate = \PHPExcel_Cell::stringFromColumnIndex($j -  1) . "$i";
					/* 获取每行的值 */
					$objectPHPExcel->getActiveSheet()->setCellValue($pCoordinate, $buildArrayDatas[$i - 2][$j - 1]);
					/* 设置每列宽度自适应 */
					$objectPHPExcel->getActiveSheet()->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($j -  1))
						->setAutoSize(true); 
					/* 设置格式为纯文本 */
					$objectPHPExcel->getActiveSheet()->setCellValueExplicit($pCoordinate, $buildArrayDatas[$i - 2][$j - 1], 
						\PHPExcel_Cell_DataType::TYPE_STRING);
				}
			}
			return $objectPHPExcel;
		} catch(Exception $message) {
			Yii::error(sprintf('[buildExcelObject] Exception, reason:%s', $message->getMessage()));
			return False;
		}
	}	


	/**
	 *	输出excel 文件
	 *	@params	obj	$objectPHPExcel
	 *	@return
	 */
	public static function downloadExcelFile($objectPHPExcel, $excelFileName)
	{
		header('Content-Type : application/vnd.ms-excel');
		header('Content-Disposition:attachment;filename="'.$excelFileName.'-'.date("Y年m月j日").'.xlsx"');
		$objWriter = new \PHPExcel_Writer_Excel2007($objectPHPExcel);
		$objWriter->save('php://output');
		return true;
	}
} 

# vim: set noexpandtab ts=4 sts=4 sw=4 :
