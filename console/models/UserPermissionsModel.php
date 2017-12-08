<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-01-20 11:35:01
 */
namespace console\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\HttpException;
use common\models\RequestApi;
use console\models\ConsoleBaseModel;


class UserPermissionsModel extends ConsoleBaseModel
{
	/* 操作userpermissions的接口 */
	const PERMISSIONS_API	= 'https://graph.facebook.com/v2.10/act_%s/userpermissions';


	public $role		= 'ADMIN';
	/*Focus Blue*/
	public $user_ids	= ['128842597472231', '141151829642478', '120826295016284', '142422556181169', '103617920004994'];
	public $business_id	= '511273569054473';
	public $access_token;


	public function roles()
	{
		return [
			[['role', 'user_ids', 'business_id', 'access_token'], 'required']
		];
	}


	public function init()
	{
		$this->access_token = $this->getAccessTokenByBusinessId($this->business_id);
	}


	/**
	 *	判断UserPermissions列表中是否已经存在Blue Focus
	 */
	public function checkUserPermissions($account_id)
	{
		$userPermissionsList = $this->getUserPermissions($account_id);
		if($userPermissionsList)
		{
			$userIdList = [];
			foreach($userPermissionsList as $userPermissions)
			{
				if(property_exists($userPermissions, 'user'))
				{
					$user = $userPermissions->user;
					array_push($userIdList, $user->id);
				}
			}
			foreach($this->user_ids as $user_id)
			{
				if(!in_array($user_id, $userIdList)) return false;
			}
			return true;
		}
		return false;
	}


	/**
	 *	获取UserPermissions列表
	 *	@params	str	$account_id
	 *	@return
	 */
	private function getUserPermissions($account_id)
	{
		try {
			$url = sprintf(self::PERMISSIONS_API, $account_id);
			$params	= ['access_token' => $this->access_token];
			$encodeUrl = sprintf('%s?%s', $url, http_build_query($params));
			$response = RequestApi::requestGet($encodeUrl);
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'data'))
				{
					Yii::info(sprintf('[getUserPermissions] Success, account_id:%s, sendurl:%s, response:%s',
						$account_id, $encodeUrl, $response));
					return $result->data;
				}
			}
			throw new Exception(sprintf('getUserPermissions Error, account_id:%s, sendurl:%s, response:%s',
				$account_id, $encodeUrl, $response));
		} catch(Exception $message) {
			Yii::error(sprintf('[getUserPermissions] Exception, account_id:%s, reason:%s',
				$account_id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	添加UserPermissions
	 *	@params	str	$account_id
	 *	@return bool
	 */
	public function addUserPermissions($account_id)
	{
		$url = sprintf(self::PERMISSIONS_API, $account_id);
		foreach($this->user_ids as $user_id)
		{
			$params	= [
				'access_token'	=> $this->access_token,
				'business'		=> $this->business_id,
				'user'			=> $user_id,
				'role'			=> $this->role,
			];
			try {
				$response = RequestApi::requestPost($url, http_build_query($params));
				if($response)
				{
					$result = json_decode($response);
					if(property_exists($result, 'success'))
					{
						Yii::info(sprintf('[addUserPermissions] Success, account_id:%s, sendurl:%s, params:%s, response:%s',
							$account_id, $url, json_encode($params), $response));
						continue;
					}
					throw new Exception(sprintf('sendurl:%s, params:%s, response:%s', $url, json_encode($params), $response));
				}
			} catch(Exception $message) {
				Yii::error(sprintf('[addUserPermissions] Exception, account_id:%s, user_id:%s, why:%s',
					$account_id, $user_id, $message->getMessage()));
			}
		}
	}


	/**
	 *	更新UserPermissions
	 *	@params	str	$account_id
	 *	@return bool
	 */
	public function editUserPermissions($account_id)
	{
		try {
			$url = sprintf(self::PERMISSIONS_API, $account_id);
			$params	= [
				'access_token'	=> $this->access_token,
				'business'		=> $this->business_id,
				'user'			=> $this->user_id,
				'role'			=> $this->role,
			];
			$response = RequestApi::requestPost($url, http_build_query($params));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'success'))
				{
					Yii::info(sprintf('[editUserPermissions] Success, account_id:%s, sendurl:%s, params:%s, response:%s',
						$account_id, $url, json_encode($params), $response));
					return true;
				}
			}
			throw new Exception(sprintf('editUserPermissions Error, account_id:%s, sendurl:%s, params:%s, response:%s',
				$account_id, $url, json_encode($params), $response));
		} catch(Exception $message) {
			Yii::error(sprintf('[editUserPermissions] Exception, account_id:%s, reason:%s',
				$account_id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	移除UserPermissions
	 *	@params str $account_id
	 *	@return bool
	 */
	public function removeUserPermissions($account_id)
	{
		try {
			$url = sprintf(self::PERMISSIONS_API, $account_id);
			$params	= [
				'access_token'	=> $this->access_token,
				'business'		=> $this->business_id,
				'user'			=> $this->user_id,
			];
			$response = RequestApi::requestDelete($url, http_build_query($params));
			if($response)
			{
				$result = json_decode($response);
				if(property_exists($result, 'success'))
				{
					Yii::info(sprintf('[removeUserPermissions] Success, account_id:%s, sendurl:%s, params:%s, response:%s',
						$account_id, $url, json_encode($params), $response));
					return true;
				}
			}
			throw new Exception(sprintf('removeUserPermissions Error, account_id:%s, sendurl:%s, params:%s, response:%s',
				$account_id, $url, json_encode($params), $response));
		} catch(Exception $message) {
			Yii::error(sprintf('[removeUserPermissions] Exception, account_id:%s, reason:%s',
				$account_id, $message->getMessage()));
			return false;
		}
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
