<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-08-31 11:10:00
 */
namespace common\models;

use Yii;
use yii\base\Model;
use linslin\yii2\curl;


class RequestApi extends Model
{
	/**
	 *	POST发送请求, 提交数组
	 *	@params	str	$url
	 *	@return response
	 */
	public static function requestPost($url, $params)
	{
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$response = curl_exec($ch);
			curl_close($ch);

			return $response;
		} catch(Exception $message) {
			Yii::error(sprintf("[requestPost] Exception, sendUrl:%s, params:%s, reason:%s",
				$url, json_encode($params), $message->getMessage()
			));
			return false;
		}
	}


	/**
	 *	DELETE发送请求
	 *	@params	str	$url
	 *	@return response
	 */
	public static function requestDelete($url, $params)
	{
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url );
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			$response = curl_exec($ch);
			curl_close($ch);

			return $response;
		} catch(Exception $message) {
			Yii::error(sprintf("[requestDelete] Exception, sendUrl:%s, params:%s, reason:%s",
				$url, json_encode($params), $message->getMessage()
			));
			return false;
		}
	}


	/**
	 *	GET获取远程图片并保存
	 *	@params	$url
	 *	@return response
	 */
	public static function requestGetImage($url, $imageName)
	{
		try {
			$ch = curl_init();
			$fp = fopen($imageName,'wb');
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$response = curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			return $response;	
		} catch(Exception $message) {
			Yii::error(sprintf("[requestGetImage] Exception, sendUrl:%s, params:%s, reason:%s",
				$url, json_encode($params), $message->getMessage()
			));
			return false;
		}
	}
	
	/**
	 *	GET发送请求
	 *	@params $url
	 *	@return response
	 */
	public static function requestGet($url)
	{
		try {
			$curlObj = new curl\Curl();
			$response = $curlObj->get($url);

			return $response;
		} catch(Exception $message) {
			Yii::error(sprintf("[requestGet] Exception, sendUrl:%s, reason:%s",
				$url, $message->getMessage()
			));
			return false;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
