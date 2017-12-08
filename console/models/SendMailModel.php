<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-03-05 16:35:16
 */
namespace console\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\HttpException;
use console\models\ConsoleBaseModel;
use backend\models\record\ThEmailRecord;
use backend\models\record\ThEmailTemplate;


class SendMailModel extends ConsoleBaseModel
{
	/* 需要发送邮件的模板id */
	public $tid;


	public function rules()
	{
		return [
			[['id'], 'safe'],
		];	
	}


	/**
	 * 根据tid获取相应的邮件信息
	 *	@return obj
	 */
	public function getEmailTemplate()
	{
		try {
			$emailTemplateObj = ThEmailTemplate::findOne($this->tid);
			if($emailTemplateObj) return $emailTemplateObj;
			throw New Exception('No such record!');
		} catch(Exception $message) {
			Yii::error(sprintf('getEmailTemplate Error, tid:%d,  reason:%s', $this->tid, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	查找相应record，如果记录存在则不创建
	 *	@params	int	$tid
	 *	@params	string	$receiver
	 *	@return
	 */
	private function findEmailRecord($tid, $receiver)
	{
		$result = ThEmailRecord::find()->where(['tid' => $tid, 'receiver' => $receiver])->one();
		if($result) return true;
		return false;
	}


	/**
	 *	创建邮件发送记录
	 *	@params	$obj	emailTemplateObj
	 *	@return bool
	 */
	private function createEmailRecord($emailTemplateObj)
	{
		try {
			if($emailTemplateObj)
			{
				$receivers		= $emailTemplateObj->receiver;
				$receiverList	= explode(',', unserialize($receivers));
				if($receiverList)
				{
					foreach($receiverList as $receiver)  
					{
						if($this->findEmailRecord($this->tid, $receiver)) continue;
						$emailRecordModel = new ThEmailRecord();
						$emailRecordModel->tid		= $this->tid;
						$emailRecordModel->receiver	= $receiver;
						$emailRecordModel->status	= ThEmailRecord::WAITING;
						if(!$emailRecordModel->save())
						{
							throw New Exception(sprintf('emailRecordModel save error, tid:%d, receiver:%s, reason:%s',
								$this->tid, $receiver, $json_encode($emailRecordModel->getErrors())));
						}	
					}
					return true;
				}
			}
			throw New Exception('emailTemplateObj error!');
		} catch(Exception $message) {
			Yii::error(sprintf('createEmailRecord Error, emailTemplateObj:%s,  reason:%s',
				json_encode($emailTemplateObj), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	获取需要发送的邮件记录
	 *	@params	int	$tid;
	 *	@return $obj
	 */
	private function getEmailRecordByTid($tid)
	{
		try {
			$emailRecordObj	= ThEmailRecord::find()->joinWith('emailTemplate')->where([
				'tid' => $tid, 'th_email_record.status' => ThEmailRecord::WAITING])->all();
			if($emailRecordObj) return $emailRecordObj;
			throw New Exception('No such record!');
		} catch(Exception $message) {
			Yii::error(sprintf('getEmailRecordByTid Error, tid:%d, reason:%s', $tid, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	更新发送邮件记录状态
	 *	@params	int	$id
	 *	@return	bool
	 */
	private function updateEmailRecord($id, $attributes)
	{
		try {
			$emailRecordResult = ThEmailRecord::updateAll($attributes, ['id' => $id]);
			if($emailRecordResult) return true;
			throw New Exception('No such record!');
		} catch(Exception $message) {
			Yii::error(sprintf('updateEmailRecord Error, id:%d, attributes:%s, reason:%s', 
				$id, json_encode($attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	更新发送邮件模板状态
	 *	@params	int	$id
	 *	@return bool
	 */
	private function updateEmailTemplate($id, $attributes)
	{
		try {
			$emailTemplateResult = ThEmailTemplate::updateAll($attributes, ['id' => $id]);
			if($emailTemplateResult) return true;
			throw New Exception('No such record!');
		} catch(Exception $message) {
			Yii::error(sprintf('emailTemplateResult Error, id:%d, attributes:%s, reason:%s', 
				$id, json_encode($attributes), $message->getMessage()));
			return false;
		}
	}


	/**
	 *	发送邮件方法
	 *	@params	obj	$emailTemplateObj
	 *	return
	 */
	public function sendMail($emailTemplateObj)
	{
		try {
			$createEmailRecord = $this->createEmailRecord($emailTemplateObj);
			if($createEmailRecord)
			{
				$emailRecordObjs	= $this->getEmailRecordByTid($this->tid);
				if($emailRecordObjs)
				{
					$this->updateEmailTemplate($this->tid, ['status' => ThEmailTemplate::RUNNING]);
					/* 由于邮件服务器的原因，每半小时之内只能发送100封邮件 */
					$counter = 0;
					foreach($emailRecordObjs as $emailRecordObj)
					{
						if($counter >= 99) { $counter = 0; sleep(1800); }

						$this->updateEmailRecord($emailRecordObj->id, ['status' => ThEmailRecord::RUNNING]);
						try {
							$mailrObj = Yii::$app->mailer->compose()
								->setFrom([Yii::$app->params['supportEmail'] => '蓝瀚互动'])
								->setTo($emailRecordObj->receiver)
								->setHtmlBody(unserialize($emailRecordObj->emailTemplate->content))
								->setSubject($emailRecordObj->emailTemplate->subject);
							$mailsResult = $mailrObj->send();
							$this->updateEmailRecord($emailRecordObj->id, ['status' => ThEmailRecord::SUCCESS]);
						} catch(\Swift_TransportException $error) {
							$this->updateEmailRecord($emailRecordObj->id, ['status' => ThEmailRecord::FAILED, 'reason' => $error]);
						} catch(\Swift_RfcComplianceException $error) {
							$this->updateEmailRecord($emailRecordObj->id, ['status' => ThEmailRecord::FAILED, 'reason' => $error]);
						}
						/*每次发送完之后，关闭链接*/
						Yii::$app->mailer->getTransport()->stop();
						sleep(2);
						$counter += 1;
					}
					$this->updateEmailTemplate($this->tid, ['status' => ThEmailTemplate::SUCCESS]);
				}
			}
		} catch(Exception $message) {
			Yii::error(sprintf('sendMail Error, emailTemplateObj:%s, inittables:%s, reason:%s',
				json_encode($emailTemplateObj), json_encode($this->attributes), json_encode($message->getMessage())));
			$this->updateEmailTemplate($this->tid, ['status' => ThEmailTemplate::FAILED]);
			return false;
		}
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
