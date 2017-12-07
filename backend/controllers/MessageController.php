<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-12-05 15:03:29
 */

namespace backend\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\user\User;
use backend\models\user\UserModel;
use backend\models\record\ThMessage;
use backend\models\message\MessageModel;
use backend\models\record\ThMessageSearch;
use backend\controllers\ThreadBaseController;

class MessageController extends ThreadBaseController
{
	public $enableCsrfValidation = false;

	public function init()
	{
		parent::init();
		$this->layout = '@app/views/layouts/admin-manager.php';
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
						'actions' => ['index', 'view', 'list', 'get-new-message'],
						'allow'	=> true,
						'roles'	=> ['admin_group'],
					]
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
	 *	list all message
	 */
	public function actionIndex()
	{
		return $this->redirect(['list']);	
	}


	/**
	 *	list
	 */
	public function actionList()
	{
        $queryParams    = Yii::$app->request->queryParams;
		$model = new ThMessageSearch();
        $model->type = !empty($queryParams['type']) ? $queryParams['type'] : 0;
		$dataProvider = $model->search([]);
		
		return $this->render('list', [
			'model'	=> $model,
			'dataProvider' => $dataProvider,
		]);
	}


	/**
	 *	view
	 */
	public function actionView($id)
	{
		$user_id	= UserModel::getLoginInfo()->id;
		if(!$user_id) throw new NotFoundHttpException('Login error!');
		MessageModel::updateMessageStatus($id, $user_id);
		return $this->render('view', [
			'model' => $this->findModel($id),	
		]);
	}


    /**
     *	获取消息列表数据
     *	@return
     */
    public function actionGetNewMessage()
    {
        if(Yii::$app->request->isAjax)
        {
            $messageTotalList['all_total']                  = MessageModel::getMessageTotal();
            $messageTotalList['create_account_total']       = MessageModel::getMessageTotal(ThMessageSearch::CREATE_ACCOUNT);
            $messageTotalList['change_creditlimit_total']   = MessageModel::getMessageTotal(ThMessageSearch::CHANGE_CREDITLIMIT);
            $messageTotalList['change_binding_total']       = MessageModel::getMessageTotal(ThMessageSearch::CHANGE_BINDING);
            $messageTotalList['change_account_name']        = MessageModel::getMessageTotal(ThMessage::CHANGE_NAME);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => true, 'message_total_list' => $messageTotalList];
        }
    }


	/**
     * Finds the AdAccountInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AdAccountInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ThMessage::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
