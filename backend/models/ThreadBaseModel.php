<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-11-04 17:38:37
 */

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\RequestApi;
use common\models\UploadExcel;
use backend\models\user\UserModel;


class ThreadBaseModel extends Model
{
	/* 查询绑定BM的信息 */
	const	READ_BM_API			= 'https://graph.facebook.com/v2.9/act_%s/agencies';	
	public $user_id;
	public $company_id;

	/**
	 *	获取登录用户的信息，进行赋值
	 *	@return
	 */
	public function setUserAttributes()
	{
		$this->user_id		= !empty(UserModel::getLoginInfo()->id) ? UserModel::getLoginInfo()->id : 0;
		$this->company_id	= !empty(UserModel::getLoginInfo()->company_id) ? UserModel::getLoginInfo()->company_id : 0;
	}


	/**
	 *	获取登录用户的business 信息，用于判断是否来自同一家agency
	 *
	 */
	protected function getCompanyBusinessId()
	{
		try {
			$this->setUserAttributes();
			$sql = sprintf("select business_id from th_agency_business where company_id = %d", $this->company_id);
			$connection = Yii::$app->db;
			$command	= $connection->createCommand($sql);
			$business_ids = $command->queryColumn();
			Yii::info(sprintf("[getCompanyBusinessId] Success, company_id:%d, business_ids:%s", $this->company_id, json_encode($business_ids)));
			return $business_ids;
		} catch(Exception $message) {
			Yii::error(sprintf("[getUsersBusinessId] Exception, user_id:%d, reason:%s", $this->user_id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	查询绑定详情
	 *	@params	str	$account_id
	 *	@return
	 */
	protected function getBindingDetail($account_id)
	{
		try {
			$url	= sprintf(self::READ_BM_API, $account_id);
			$params	= ['access_token' => Yii::$app->params['facebookApi']['access_token']];
			$encodeUrl	= sprintf("%s?%s", $url, http_build_query($params));
			$response	= RequestApi::requestGet($encodeUrl);
			Yii::info(sprintf("[getBindingDetail] account_id:%s, send_url:%s, response:%s", $account_id, $encodeUrl, $response));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'data'))	return $result;
			}
			throw new Exception("getBindingDetail response error!");
		} catch(Exception $message) {
			Yii::error(sprintf("[getBindingDetail] Exception, account_id:%s, reason:%s",
				$account_id, $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	根据business_id获取相应的access_token
	 *	@params	str	business_id
	 *	@return str	access_token
	 */
	public function getAccessTokenByBusinessId($business_id)
	{
		try {
			$sql = sprintf("select access_token from th_agency_business where business_id = '%s'", $business_id);
			$command    = Yii::$app->db->createCommand($sql);
			$result     = $command->queryOne();
			if($result) return $result['access_token'];
			throw new Exception('getAccessTokenByBusinessId error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getAccessTokenByBusinessId] Exception, business_id:%s, reason:%s', 
				$business_id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	更新remind 状态
	 *	@params	int	account_id
	 *	@params	int	status
	 *	@return bool
	 */
	protected function updateRemindStatus($account_id, $status)
	{
		try {
			$connections = Yii::$app->db;
			$command = $connections->createCommand()->update('th_remind_record', 
				['status' => $status, 'updated_at' => time()], 
				sprintf('account_id = %d', $account_id));
			$result = $command->execute();
			if($result) return $result;
			throw new Exception('updateRemindStatus error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[updateRemindStatus] Exception, account_id:%d, status:%d, reason:%s',
				$account_id, $status, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	上传文件的方法，只可以是Excel或者CSV
	 *	@params	obj		$uploadFile
	 *	@return array	$uploadData
	 */
	public function getUploadData($uploadFile)
	{
		try {
			$objPHPExcel = UploadExcel::getObjPHPExcel($uploadFile);
			if($objPHPExcel)
			{
				$uploadData = UploadExcel::getUploadFileData($objPHPExcel);
				if($uploadData) return $uploadData;
			}
			throw new Exception('objPHPExcel Error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getUploadData] Exception, uploadfile:%s, reason:%s',
				$uploadFile, $message->getMessage()));
			return false;
		}
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
