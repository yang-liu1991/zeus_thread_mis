<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-03-23 18:20:00
 */

namespace common\models;

use Yii;
use yii\base\Model;
use yii\base\Exception;
use PHPExcel;
use PHPExcel\IOFactory;

class UploadExcel extends  Model
{
	/**
	 *	获取上传文件的类型，CSV或者Excel，返回PHPExcel对象
	 *	@params	String	$uploadFile
	 *	@return	Obj	$objPHPExcel
	 */
	public static function getObjPHPExcel($uploadFile)
	{
		try {
			$tmpUploadFilePath = self::saveUploadFile($uploadFile);
			if(!$tmpUploadFilePath) throw new Exception('Unknow tmpUploadFilePath!');
			$fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
			if($fileType == 'xlsx' || $fileType == 'xls')
			{
				$objPHPExcel = \PHPExcel_IOFactory::load($tmpUploadFilePath);
				return $objPHPExcel;
			} else if($fileType == 'csv') {
				$objReader = \PHPExcel_IOFactory::createReader('CSV')
					->setDelimiter(',')
					->setInputEncoding('GBK') //不设置将导致中文列内容返回boolean(false)或乱码
					->setEnclosure('"')
					->setSheetIndex(0);
				$objPHPExcel = $objReader->load($tmpUploadFilePath);
				return $objPHPExcel;
			} else {
				throw new Exception('getUploadFileType Error, unknow file type!');
			}
		} catch(Exception $message) {
			Yii::error(sprintf('[getUploadFileType] Exception, uploadfile:%s, reason:%s',
				$uploadFile, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	读取上传的文件，以数组的形式返回数据
	 *	@params	obj		$objPHPExcel
	 *	@return array	$uploadFileData	
	 */
	public static function getUploadFileData($objPHPExcel)
	{
		try {
			$sheet = $objPHPExcel->getSheet(0);
			/* 获取行数和列数 */
			$highestRowNum	= $sheet->getHighestRow();
			$highestColumn	= $sheet->getHighestColumn();
			$highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
			
			/* 取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名 */
			$filed = [];
			for($i=0; $i<$highestColumnNum; $i++)
			{
				$cellName	= \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
				$cellVal	= $sheet->getCell($cellName)->getValue();
				if(!$cellVal) continue;
				$filed[]	= trim($cellVal);
			}

			/* 开始取出数据并存入数组 */
			$uploadFileData = [];
			for($i=2; $i<=$highestRowNum; $i++)
			{
				$row = [];
				for($j=0; $j<count($filed); $j++){
					$cellName	= \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
					$cellVal	= $sheet->getCell($cellName)->getValue();
					if(!$cellVal) continue;
					$row[$filed[$j]] = trim($cellVal);
				}
				if(!$row) continue;
				$uploadFileData[] = $row;
			}
			return $uploadFileData;
		} catch(Exception $message) {
			Yii::error(sprintf('[getUploadFileData] Exception, uploadfile:%s, reason:%s',
				$uploadFile, $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	将文件上传到服务器，返回文件路径
	 *	@params	String	$uploadFile
	 *	@return String	$filePath
	 */
	private static function saveUploadFile($uploadFile)
	{
		try {
			if(is_object($uploadFile) && get_class($uploadFile) == 'yii\web\UploadedFile')
			{
				$uploadFileDir = Yii::getAlias('@tmpdir');
				if(!file_exists($uploadFileDir)) { mkdir($uploadFileDir); }
				$tmpFileName	= sprintf('%s/%s', $uploadFileDir, $uploadFile->name);
				$uploadFile->saveAs($tmpFileName);
				return $tmpFileName;
			}
			throw new Exception('Unknow upload file!');
		} catch(Exception $message) {
			Yii::error(sprintf('[saveUploadFile] uploadfile:%s, reason:%s',
				$uploadFile, $message->getMessage()));
			return false;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
