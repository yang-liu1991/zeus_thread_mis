<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-11-07 16:02:47
 */
namespace console\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\Conversion;
use common\models\DownloadExcel;
use console\models\ConsoleBaseModel;


class CreativesExportModel extends ConsoleBaseModel
{
    public $receiver;

    public function rules()
    {
        return [
            [['receiver'], 'safe']
        ];
    }

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
     * 保存excel文件
     */
    public function saveExcelFile($objectPHPExcel, $excelName='创意监测信息表')
    {
        try {
            $dataPath = sprintf('%s/%s', Yii::$app->basePath, 'runtime/data/');
            if(!file_exists($dataPath)) mkdir($dataPath);
            $fileName = sprintf('%s-%s.xlsx', $excelName, date("Y年m月d日"));
            $objWriter = new \PHPExcel_Writer_Excel2007($objectPHPExcel);
            $objWriter->save($dataPath.$fileName);
            return $dataPath.$fileName;
        } catch(Exception $message) {
            Yii::error(sprintf('[saveExcelFile] Exception, reason:%s', $message->getMessage()));
            return false;
        }
    }


    /**
     *	通过邮件发送
     */
    public function sendExcelFile($fileName, $excelName='创意监测信息表')
    {
        try {
            $mailrObj = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['supportEmail'] => '蓝瀚互动'])
                ->setTo($this->receiver)
                ->setSubject(sprintf('%s-%s', $excelName, date("Y年m月d日")))
                ->attach(sprintf('%s', $fileName));
            $mailrObj->send();
            return true;
        } catch(\Swift_TransportException $message) {
            Yii::error(sprintf('[sendExcelFile] Swift_TransportException, filename:%s, reason:%s', $fileName, $excelName));
            return false;
        } catch(\Swift_RfcComplianceException $message) {
            Yii::error(sprintf('[sendExcelFile] Swift_RfcComplianceException, filename:%s, reason:%s', $fileName, $excelName));
            return false;
        }
        Yii::$app->mailer->getTransport()->stop();
    }
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
