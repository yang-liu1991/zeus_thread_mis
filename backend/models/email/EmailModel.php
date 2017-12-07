<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-02-21 17:45:23
 */
namespace backend\models\email;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use common\models\UploadImage;
use yii\data\ActiveDataProvider;
use backend\models\ThreadBaseModel;
use backend\models\record\ThEmailTemplate;
use backend\models\record\ThEmailTemplateSearch;


class EmailModel extends ThreadBaseModel
{
	public $id;
	/* 邮件发送者 */
	public $sender;
	/* 邮件接收者 */
	public $receiver;
	/* 邮件接收者文件 */
	public $receiver_file;
	/* 邮件主题 */
	public $subject;
	/* 邮件内容 */
	public $content;
	/* 邮件发送状态 */
	public $status;
	public $created_at;
	public $updated_at;


	/**
	 *	rules
	 */
	public function rules()
	{
		return [
			[['sender', 'receiver', 'subject', 'content'], 'required', 'on' => ['create', 'update']],
			[['sender', 'status'], 'integer', 'on' => ['create', 'update']],
			[['receiver_file'], 'file', 'on' => ['create', 'update']],
			[['sender', 'receiver', 'subject', 'content'], 'safe']
		];
	}


	/**
	 *	@inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'sender'	=> '发送者',
			'receiver'	=> '接收者',
			'subject'	=> '邮件主题',
			'content'	=> '邮件内容',
			'status'	=> '状态'
		];
	}


	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		$scenarios = parent::scenarios();
		return array_merge($scenarios, [
			'create' => ['sender', 'receiver', 'subject', 'content'],
			'update' => ['sender', 'receiver', 'subject', 'content'],
		]);
	}


	/**
	 *	按id获取数据
	 *	@params	int	id
	 */
	public function getAttributeById($id)
	{
		$model = ThEmailTemplate::findOne($id);
		$this->attributes	= $model->attributes;
		$this->receiver		= unserialize($model->receiver);
		$this->content		= unserialize($model->content);
	}


	/**
	 *	邮件模板保存方法
	 *	@return
	 */
	public function emailTemplateCreate()
	{
		try {
			$model = new ThEmailTemplate();
			$model->attributes	= $this->attributes;
			$model->content		= serialize($this->content); 
			$model->receiver	= serialize($this->receiver);
			if($model->validate() && $model->save())
			{
				Yii::info(sprintf('[emailTemplateCreate] Success, user:%s, data:%s',
					$this->user_id, json_encode($this->attributes)));
				return true;
			}
			throw new Exception(json_encode($model->getErrors()));
		} catch(Exception $message) {
			Yii::error(sprintf('[emailTemplateCreate] Exception, user:%s, data:%s, reason:%s',
				$this->user_id, json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	邮件模板更新方法
	 *	@params	int	$id
	 *	@return
	 */
	public function emailTemplateUpdate($id)
	{
		try {
			$model = ThEmailTemplate::findOne($id);
			$model->attributes	= $this->attributes;
			$model->content		= serialize($this->content); 
			$model->receiver	= serialize($this->receiver);
			if($model->validate() && $model->save())
			{
				Yii::info(sprintf('[emailTemplateUpdate] Success, user:%s, data:%s',
					$this->user_id, json_encode($this->attributes)));
				return true;
			}
			throw new Exception(json_encode($model->getErrors()));
		} catch(Exception $message) {
			Yii::error(sprintf('[emailTemplateUpdate] Exception, user:%s, data:%s, reason:%s',
				$this->user_id, json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	邮件联系人文件保存方法
	 *	@return
	 */
	public function receiverFileUpload()
	{
		try {
			$receiverFile = $this->receiver_file;
			if($receiverFile)
			{
				if(!file_exists(Yii::getAlias('@tmpdir')))	{ mkdir(Yii::getAlias('@tmpdir')); }
				$receiverFileName = Yii::getAlias('@tmpdir').'/'.$receiverFile->name;
				$receiverFile->saveAs($receiverFileName);
				return $receiverFileName;
			}
			throw new Exception('receiverFileUpload Error, receiverFile is Null!');
		} catch(Exception $message) {
			Yii::error(sprintf('[receiverFileUpload] Exception, user:%s, filedata:%s, reason:%s',
				$this->user_id, json_encode($receiverFile), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	上传图片保存方法
	 *	@return
	 */
	public function contentImageUpload($imageUploadSrc)
	{
		try {
			if($imageUploadSrc)
			{
				if(!file_exists(Yii::getAlias('@tmpdir')))	{ mkdir(Yii::getAlias('@tmpdir')); }
				$imageUploadName = Yii::getAlias('@tmpdir').'/'.$imageUploadSrc['name'];
				if(move_uploaded_file($imageUploadSrc['tmp_name'], $imageUploadName))
				{
					$uploadImagePath = UploadImage::UploadImage($imageUploadName);
					if($uploadImagePath) return Yii::$app->params['ugcServer']['imgdir'].$uploadImagePath['key'];
				}
			}
			throw new Exception('contentImageUpload Error, upload image is Null!');
		} catch(Exception $message) {
			Yii::error(sprintf('[contentImageUpload] Exception, user:%s, filedata:%s, reason:%s',
				$this->user_id, json_encode($imageUploadSrc), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	读取csv文件中的邮箱地址，按以逗号分隔返回
	 *	@return
	 */
	public function readReceiverList()
	{
		try {
			$uploadFileName = $this->receiverFileUpload();
			$uploadFileSrc = fopen($uploadFileName, 'r');
			if($uploadFileSrc)
			{
				$receiverList = [];
				while($data = fgetcsv($uploadFileSrc))
				{
					if($data[0]) 
					{
						if(!filter_var($data[0], FILTER_VALIDATE_EMAIL)) continue;
						array_push($receiverList, $data[0]);
					}
				}
				return implode(', ', $receiverList);
			}
			throw new Exception('readUploadFile Error, uploadFileName is Null!');
		} catch(Exception $message) {
			Yii::error(sprintf('[readUploadFile] Exception, user:%s, uploadFileName:%s, reason:%s',
				$this->user_id, $uploadFileName, $message->getMessage()));
			return false;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
