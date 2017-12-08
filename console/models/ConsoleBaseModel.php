<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-08-19 15:00:52
 */
namespace console\models;

use Yii;
use yii\base\Model;
use linslin\yii2\curl;
use yii\base\Exception;


class ConsoleBaseModel extends Model
{
	/**
	 *	获取数据库链接
	 */
	public function getDbConnection()
	{
		return Yii::$app->db;
	}


	/**
	 *	根据帐户创建时间获取accountId和accessToken
	 *	@params	int	beginTime
	 *	@params	int endTime
	 *	@ return result
	 */
	public function getAdAccount($beginTime, $endTime)
	{
		try {
			$sql = sprintf(" select a.id, u.access_token from zeus_fb_account as a 
				left join zeus_fb_user as u 
				on a.fb_user_id = u.id 
				where u.status = 1 
				and a.create_time > %s 
				and a.create_time < %s; ", $beginTime, $endTime);
			$command	= $this->getDbConnection()->createCommand($sql);
			$result		= $command->queryAll();

			return $result;
		} catch(Exception $message) {
			Yii::error(sprintf('[getAdAccount] Exception, beginTime:%s, endTime:%s, reason:%s',
				$beginTime, $endTime, $message->getMessage()
			));
			return False;
		}
	}

	
	/**
	 *	发送GET请求方法
	 *	@params	str	requestUrl
	 *	@return 
	 */
	public function sendGetRequest($requestUrl)
	{
		try{
			$curlObj	= new curl\Curl();
			$response	= $curlObj->get($requestUrl);

			return $response;
		} catch(Exception $message) {
			Yii::error(sprintf('[sendGetRequest] HttpException, RequestUrl:%s, reason:%s',
				$requestUrl, $message->getMessage()
			));
			return False;
		}
	}


	/**
	 *	发送POST请求方法
	 *	@params	str	requestUrl
	 *	@params	array params
	 *	@return
	 */
	public function sendPostRequest($requestUrl, $params=[])
	{
		try {
			$curlObj	= new curl\Curl();
			$response	= $curlObj->setOption(
				CURLOPT_POSTFIELDS,
				http_build_query($params)
			)->post($requestUrl);
			Yii::info(sprintf('[sendPostRequest] Success, RequestUrl:%s, params:%s, response:%s', 
				$requestUrl, json_encode($params), $response)
			);
			return $response;
		} catch(Exception $message) {
			Yii::error(sprintf('[sendPostRequest] HttpException, RequestUrl:%s, params:%s, reason:%s',
				$requestUrl, json_encode($params), $message->getMessage()
			));
			return False;
		}
	}


	/**
	 *	重组url
	 *	params	str	url
	 *	return	str rebuildUrl
	 */
	public function rebuildRequestUrl($requestUrl, $rebuildParams=Null)
	{
		try{
			$requestUrlArray = parse_url($requestUrl);
			$queryParts = explode('&', $requestUrlArray['query']);
			$params = [];
			foreach ($queryParts as $param) {
				$item = explode('=', $param);
				$params[$item[0]] = $item[1];
			}
			foreach($rebuildParams as $key => $value) $params[$key] = $value;
			$queryArray = [];
			foreach($params as $k => $param)
			{
				$queryArray[] = $k.'='.$param;
			}
			$query = implode('&', $queryArray);
			$rebuildUrl = sprintf('%s://%s%s?%s', $requestUrlArray["scheme"], $requestUrlArray["host"], $requestUrlArray["path"], $query);
			Yii::info(sprintf("[rebuildRequestUrl] Success, requestUrl:%s, params:%s, rebuildUrl:%s",
				$requestUrl, json_encode($params), $rebuildUrl
			));
			return $rebuildUrl;
		} catch(Exception $message) {
			Yii::error(sprintf('[rebuildRequestUrl] HttpException, RequestUrl:%s, params:%s, reason:%s',
				$requestUrl, json_encode($params), $message->getMessage()
			));
			return $requestUrl;
		}
	}

	/* 上面的东东几乎没什么用，改天心情好的时候干掉 */

	/**
	 *	获取所有正常的account_id和对应的business_id
	 *	@return
	 */
	public function getAccountData()
	{
		try {
			$sql = "select fbaccount_id, business_id from th_account_info where fbaccount_id is not null";
			$command	= $this->getDbConnection()->createCommand($sql);
			$result		= $command->queryAll();
			if($result) return $result;
			throw new Exception('getAccountData error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getAccountData] Exception, reason:%s', $message->getMessage()));
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
			$command    = $this->getDbConnection()->createCommand($sql);
			$result     = $command->queryOne();
			if($result) return $result['access_token'];
			throw new Exception('getAccessTokenByBusinessId error!');
		} catch(Exception $message) {
			Yii::error(sprintf('[getAccessTokenByBusinessId] Exception, business_id:%s, reason:%s', 
				$business_id, $message->getMessage()));
			return false;
		}
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
