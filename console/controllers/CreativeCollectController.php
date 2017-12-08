<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-08-07 18:22:39
 */
namespace console\controllers;

use Yii;
use yii\base\Exception;
use console\models\SendMailModel;
use console\models\CreativesExportModel;
use backend\models\record\ThAdCreativesSearch;
use console\controllers\ConsoleBaseController;

class CreativeCollectController extends ConsoleBaseController
{
	public $receiver;

	public function options()
	{
		return ['receiver'];
	}

	public function actionCreativesExport()
	{
		$searchModel = new ThAdCreativesSearch();
		$exportModel = new CreativesExportModel();
		$dataProvider = $searchModel->search([]);
		$dataProvider->setPagination(false);
		$exportModel->receiver = $this->receiver;
		$objectPHPExcel = $exportModel->buildExcelObj($dataProvider->getModels());
		if($objectPHPExcel)
		{
			$fileName = $exportModel->saveExcelFile($objectPHPExcel);
			if($fileName) $exportModel->sendExcelFile($fileName);
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
