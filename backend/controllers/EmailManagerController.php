<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-02-21 17:27:02
 */

namespace backend\controllers;

use Yii;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\email\EmailModel;
use backend\models\record\ThEmailTemplate;
use backend\models\record\ThEmailTemplateSearch;
use backend\models\record\ThEmailRecordSearch;
use backend\controllers\ThreadBaseController;


class EmailManagerController extends ThreadBaseController
{
	public $enableCsrfValidation = false;


	public function init()
	{
		parent::init();
	}


	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
			'access' => [
                'class' => AccessControl::className(),
                'rules' => [
					[
                        'actions' => ['index', 'create-email', 'update-email', 'send-email', 'email-list', 'email-view', 'email-record', 'upload-image', 'upload-file', 'get-email-subject'],
                        'allow' => true,
                        'roles' => ['admin_group'],
                    ],
				],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
	}


	/**
	 *	首页
	 */
	public function actionIndex()
	{
		return $this->redirect(['email-list']);
	}


	/**
	 *	增加批量发送邮件方法
	 */
	public function actionCreateEmail()
	{
		$model = new EmailModel();
		$model->scenario	= 'create';
		if($model->load(Yii::$app->request->post()))
		{
			$model->sender	= $this->user_id;
			$model->status	= ThEmailTemplate::WAITING;
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($model->validate() && $model->emailTemplateCreate())
			{
				Yii::$app->session->setFlash('email-create-success');
				return ['status' => true, 'message' => ''];
			}
			$errors = $model->getErrors();
			return ['status' => false, 'message' => json_encode($errors)];
		}

		return $this->render('create-email',[
			'model' => $model
		]);
	}


	/**
	 *	更新批量发送邮件方法
	 */
	public function actionUpdateEmail($id)
	{
		$model = new EmailModel();
		$model->scenario	= 'update';
		$model->id			= $id;
		$model->getAttributeById($id);
		if($model->load(Yii::$app->request->post()))
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($model->validate() && $model->emailTemplateUpdate($id))
			{
				Yii::$app->session->setFlash('email-update-success');
				return ['status' => true, 'message' => ''];
			}
			$errors = $model->getErrors();
			return ['status' => false, 'message' => json_encode($errors)];
		}

		return $this->render('update-email',[
			'model' => $model
		]);
	}


	/**
	 *	历史发送邮件的列表
	 */
	public function actionEmailList()
	{
		$model = new ThEmailTemplateSearch();
		$model->sender	= $this->user_id;
		$queryParams    = Yii::$app->request->queryParams;
		$dataProvider = $model->search($queryParams);
		
		return $this->render('email-list', [
			'model'	=> $model,
			'dataProvider' => $dataProvider,
		]);
	}


	/**
	 *	邮件发送详情
	 */
	public function actionEmailView($id)
	{
		return $this->render('email-view', [
			'model' => $this->findModel($id),	
		]);
	}


	/**
	 *	邮件发送记录
	 */
	public function actionEmailRecord($id)
	{
		$model = new ThEmailRecordSearch();
		$queryParams    = Yii::$app->request->queryParams;
		$model->tid		= $id;
		$dataProvider = $model->search($queryParams);
		
		return $this->render('email-record', [
			'model'	=> $model,
			'dataProvider' => $dataProvider,
		]);
	}


	/**
	 *	文件上传方法，返回文件中的邮件联系人
	 */
	public function actionUploadFile()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new EmailModel();
			$model->receiver_file = UploadedFile::getInstance($model, 'receiver_file');
			$receiverList = $model->readReceiverList();
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($receiverList) return ['status' => true, 'receiver_list' => $receiverList];
			return ['status' => false, 'receiver_list' => '', 'err_msg' => ''];
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');	
		}
	}


	/**
	 *	图片上传方法
	 */
	public function actionUploadImage()
	{
		if(Yii::$app->request->isAjax)
		{
			$model = new EmailModel();
			$uploadImage = !empty($_FILES['ajaxTaskFile']) ? $_FILES['ajaxTaskFile'] : [];
			Yii::$app->response->format = Response::FORMAT_JSON;
			if($uploadImage)
			{
				$uploadImagePath = $model->contentImageUpload($uploadImage);
				if($uploadImagePath) return ['status' => true, 'image_path' => $uploadImagePath];
			}
			return ['status' => false, 'image_path' => '', 'err_msg' => ''];
		}
	}

	
	/**
	 *	执行发送邮件
	 */
	public function actionSendEmail()
	{
		if(Yii::$app->request->isAjax)
		{
			$tid	= !empty($_POST['tid']) ? $_POST['tid'] : 0;
			Yii::$app->response->format = Response::FORMAT_JSON;
			$applicationDir		= realpath(dirname(__FILE__) . '/../../');
			$yiiExecFile	= sprintf('%s/yii', $applicationDir);
			if(file_exists($yiiExecFile))
			{
				$command = sprintf('cd %s && nohup /usr/local/domob/current/php/bin/php yii mail-manager/send-mail --tid=%d >> /dev/null 2>&1 &', 
					$applicationDir, $tid);
				$process = proc_open($command, [], $pipes);
				if(proc_get_status($process)) return ['status' => true, 'err_msg' => ''];
			}
			return ['status' => false, 'err_msg' => 'system error!'];
		}
	}


	/**
	 *	获取邮件主题列表
	 */
	public function actionGetEmailSubject($subject=Null, $id=Null)
	{
		if(Yii::$app->request->isAjax)
		{
			Yii::$app->response->format = Response::FORMAT_JSON;
			$out = ['results' => ['id' => '', 'text' => '']];
		    if (!is_null($subject)) {
				$query = new Query;
				$query->select('id, subject as text')
				->from('th_email_template')
				->where(['like', 'subject', $subject])
				->limit(20);
				$command = $query->createCommand();
				$data = $command->queryAll();
				$out['results'] = array_values($data);
			}
    		return $out;
		}	
	}


	/**
     * Finds the ThEmailTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ThEmailTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ThEmailTemplate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
