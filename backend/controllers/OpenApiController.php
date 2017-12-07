<?php

/**
 *开放API 目前主要对接鲁班 也可以为其他项目用 现在没有鉴权机制。
 *如果更多项目用，建议加上鉴权机制，
 * 1.目前APi 包含 获取账户列表，账户提交与更新 营业执照上传，账号详情等接口
 * 2.鲁班账户体系与 此项目账户体系通过传递user_id确定。如果user_id=0,那么新建此项目账户，
 * 如果不为空 那么确定为操作这个账户。user_id 经过des 加密以及 md5 校验。
 */
namespace backend\controllers;

use backend\models\record\ThMessage;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use backend\models\account\RemindModel;
use backend\models\account\EntityModel;
use backend\models\account\RequestModel;
use backend\models\account\FbVertical;
use backend\models\message\MessageModel;
use backend\models\user\UserModel;
use backend\models\record\ThEntityInfoSearch;
use backend\models\record\ThAccountInfoSearch;
use backend\controllers\ThreadBaseController;
use backend\models\record\OpenApiModel;
class OpenApiController extends ThreadBaseController
{
	public $enableCsrfValidation = false;

   
	/**
	 *	账户信息列表
	 */
	public function actionAccountList()
	{
		$model =new OpenApiModel();
		$user_id= $_GET['user_id'];
		$email=Yii::$app->request->get('email');  
		$result=$model->findFbAccountByUser($user_id,$email);
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$response->data =$result;
		$response->send();
	}
	/**
	 * 账户新建或者更新
	 */
	public function actionAccountSubmit()
	{
		$model =new OpenApiModel();
		$user_id= $_POST['user_id'];
		$email=Yii::$app->request->post('email');  
		$result=$model->accountSubmit($user_id,$email);
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$response->data =$result;
		$response->send();
	}
	/**
	 *
	 *
	 */
	public function actionUploadFile()
	{
			$model = new EntityModel();
			$imagePath = $model->uploadImage();
			Yii::$app->response->format =\yii\web\Response::FORMAT_JSON;
			if($imagePath)
			{
				Yii::$app->response->data=array('code' =>0, 'data' => $imagePath);
			} else {
				Yii::$app->response->data=array('code' =>-1, 'msg' => '失败');
			}
			Yii::$app->response->send();
	
	}

	public function actionAccountInfo(){
		$model =new OpenApiModel();
		$user_id= $_GET['user_id'];
		$email=Yii::$app->request->get('email');  
		$result=$model->findAccountByEntity($user_id,$email);
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$response->data =$result;
		$response->send();
	}
}
