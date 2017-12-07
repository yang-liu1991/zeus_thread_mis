<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-09-19 15:17:06
 */
namespace common\models;

use Yii;
use yii\base\Model;

class SendMail extends Model
{

	/**
	 *	发送纯文本邮件
	 *	@params	array	$Receiver
	 *	@params	str		$Object
	 *	@params	str		$content
	 */
	static public function sendTextEmail($receiver, $object, $content)
	{
		try {
			$mailer	= Yii::$app->mailer->compose();
			$mailer->setFrom(Yii::$app->params['supportEmail']);
			$mailer->setTo($receiver);
			$mailer->setSubject($object);
			$mailer->setTextBody($content);
			if($mailer->send()) 
			{
				Yii::Info(sprintf("[sendTextEmail] Success, receiver:%s, object:%s, content:%s",
					$receiver, $object, $content));
				return true;
			}
			throw New Exception('sendTextEmail Exception!');
		} catch(Exception $message) {
			Yii::error(sprintf("[sendTextEmail] error, receiver:%s, object:%s, content:%s, reason:%s",
				$receiver, $object, $content, $message->getMessage));
			return false;
		}
	}


	/**
	 *	发送HTML邮件
	 *	@params	array	$Receiver
	 *	@params	str		$Object
	 *	@params	str		$content
	 */
	static public function sendHtmlEmail($receiver, $object, $content)
	{
		try {
			$mailer	= Yii::$app->mailer->compose();
			$mailer->setFrom(Yii::$app->params['supportEmail']);
			$mailer->setTo($receiver);
			$mailer->setSubject($object);
			$mailer->setHtmlBody($content);
			if($mailer->send()) 
			{
				Yii::Info(sprintf("[sendHtmlEmail] Success, receiver:%s, object:%s, content:%s",
					$receiver, $object, $content));
				return true;
			}
			throw New Exception('sendHtmlEmail Exception!');
		} catch(Exception $message) {
			Yii::error(sprintf("[sendHtmlEmail] error, receiver:%s, object:%s, content:%s, reason:%s",
				$receiver, $object, $content, $message->getMessage));
			return false;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
