<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-07-15 16:11:53
 */

namespace common\models;

use Yii;
use yii\base\Model;
use yii\base\Exception;
use yii\web\UploadFile;

/* 上传到网盘 */
require_once(Yii::getAlias('@qiniucloud'). '/autoload.php');
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;


class UploadImage extends Model
{
	const ACCESS_KEY	= '9Y5UJQMgydezU7eDVmGsrjTmeTm5pvBEymaEjxys';
	const SECRET_KEY	= 'p4eICSUX6mC6yF6gdFqAcUdRgHBHC-2NBD6-4nVa';
	const BUCKET		= 'usw-file';

	public $uploadFile;
	public function rules()
	{
		return [['file'], 'file'];
	}

	
	/**
	 *	上传到网络硬盘
	 *	@params	src
	 *	@return
	 */
	static public function UploadImageToCloud($uploadFile)
	{
		try {
			$auth = new Auth(self::ACCESS_KEY, self::SECRET_KEY);
			$bucket = self::BUCKET;
			$token = $auth->uploadToken($bucket);
			$uploadMgr = new UploadManager();
			if(is_object($uploadFile) && get_class($uploadFile)==='yii\web\UploadedFile')
			{
				if(!file_exists(Yii::getAlias('@tmpdir')))	{ mkdir(Yii::getAlias('@tmpdir')); }
                $imageTmpName = Yii::getAlias('@tmpdir').'/'.$uploadFile->name;
				$uploadFile->saveAs($imageTmpName);
				/* 上传后的文件名 */
				$key = md5($imageTmpName);
				list($ret, $err) = $uploadMgr->putFile($token, $key, $imageTmpName);
				if ($err !== null) {
					throw new Exception(sprintf('UploadImageToCloud Exception, reason:%s', json_encode($err)));
				} else {
					return $ret;
				}
			}
		} catch(Exception $e) {
			Yii::error('抱歉，图片上传失败，原因：'.$e->getMessage());
			throw new Exception('[UploadImageToCloud] Exception');
		}	
	}


	static public function UploadImage($imageTmpName)
	{
		try {
			$auth = new Auth(self::ACCESS_KEY, self::SECRET_KEY);
			$bucket = self::BUCKET;
			$token = $auth->uploadToken($bucket);
			$uploadMgr = new UploadManager();
			/* 上传后的文件名 */
			$key = md5($imageTmpName);
			list($ret, $err) = $uploadMgr->putFile($token, $key, $imageTmpName);
			if ($err !== null) {
				throw new Exception(sprintf('UploadImage Exception, reason:%s', json_encode($err)));
			} else {
				return $ret;
			}
		} catch(Exception $e) {
			Yii::error('抱歉，图片上传失败，原因：'.$e->getMessage());
			throw new Exception('[UploadImage] Exception');
		}	
	}
}


# vim: set noexpandtab ts=4 sts=4 sw=4 :
