<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-02-09 14:50:53
 */
namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ThreadBaseModel;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThRemindRecord;
use backend\models\record\ThAccountInfoSearch;
use backend\models\record\ThRemindRecordSearch;


class RemindModel extends ThreadBaseModel
{
	const GAMING_RECEIVER	= 'qian.zhang1@bluefocus.com';
	const NOGAMING_RECEIVER	= 'mengjuan.chen@bluefocus.com';

	public $id;
	public $status;
	/* 帐户提交的request_id */
	public $request_id;
	/* 帐户的申请时间 */
	public $created_at;
	public $updated_at;
	public $contact;
	public $name_zh;
	public $name_en;
	public $vertical;


	public function rules()
	{
		return [
			[['contact'], 'email'],
			[['id', 'status', 'created_at', 'updated_at'], 'integer'],
			[['created_at'], 'validateCreatedAt'],
			[['updated_at'], 'validateUpdatedAt'],
			[['id', 'status', 'request_id', 'created_at', 'updated_at', 'contact', 'name_zh', 'name_en', 'vertical'], 'safe']
		];
	}


	/**
	 *	验证申请开户的时间，不足24小时，不能催单
	 */
	public function validateCreatedAt()
	{
		if($this->status == ThAccountInfoSearch::getAccountStatus()['WAIT'])
		{
			if((time() - $this->created_at) < 3600 * 24)
				$this->addError('message', '提交开户时间不足一个工作日，不能进行催单！');
		}
		return true;
	}


	/**
	 *	验证提交到FB的开户时间，不足24小时*3，不能催单
	 */
	public function validateUpdatedAt()
	{
		if($this->status == ThAccountInfoSearch::getAccountStatus()['PENDING'])
		{
			if((time() - $this->updated_at) < 3600 * 24 * 3)
				$this->addError('message', '提交Facebook开户时间不足三个工作日，不能进行催单！');
		}
		return true;
	}


	/**
	 *	获取此开户id所对应实体信息的业务类型
	 *	@params	int	$id
	 *	@return obj
	 */
	public function getAccountInfo($id)
	{
		try {
			$sql = sprintf('select a.id, a.request_id, a.status, a.created_at, a.updated_at, e.name_zh, e.name_en, e.vertical, e.contact from 
				th_entity_info as e left join th_account_info as a on e.id = a.entity_id where a.id = %d', $id);
			$connections = Yii::$app->db;
			$command	= $connections->createCommand($sql);
			$results	= $command->queryOne();
			if($results)	return $results;
			throw new Exception('getAccountInfo error!');
		} catch (Exception $message) {
			Yii::error(sprintf('[getAccountInfo] Exception, id:%d, reason:%s',
				$id, $message->getMessage()));
			return false;
		}
	}

	
	/**
	 *	获取接收者的邮箱
	 */
	private function getEmailSendTo($vertical)
	{
		if($vertical == 'GAMING') return self::GAMING_RECEIVER;
		return self::NOGAMING_RECEIVER;
	}


	/**
	 *	格式化email content
	 *	@return str	content
	 */
	private function buildEmailContent()
	{
		$content = sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
			date('Ymd H:i:s', time()), $this->contact, $this->name_zh, $this->name_en, $this->vertical, $this->request_id);
		return $content;
	}


	/**
	 *	更新remind status的方法
	 *	@return 
	 */
	private function updateRemind()
	{
		try {
			if($this->status == ThAccountInfoSearch::getAccountStatus()['WAIT'])
			{
				if(!$this->updateRemindStatus($this->id, ThRemindRecordSearch::REMIND_BF))
					throw new Exception('updateRemindStatus Error!');
			} else {
				if(!$this->updateRemindStatus($this->id, ThRemindRecordSearch::REMIND_FB))
					throw new Exception('updateRemindStatus Error!');
			}
			return true;
		} catch(Exception $message) {
			Yii::error(sprintf('[updateRemind] Exception, account_id:%d, reason:%s',
				$this->id, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	发送提醒邮件
	 */
	public function sendingEmail()
	{
		try {
			if(!$this->updateRemind()) throw new Exception('updateRemind Error!');
			$content		= $this->buildEmailContent();
			$sendEmailTo	= $this->getEmailSendTo($this->vertical);
			return Yii::$app->mailer->compose(['html' => 'accountRemind-html'], ['content' => $content])
				->setFrom([Yii::$app->params['supportEmail'] => 'Thread API System'])
				->setTo($sendEmailTo)
				->setSubject('API催单提醒函')
				->send();
		} catch(Exception $message) {
			Yii::error(sprintf('[sendingEmail] Exception, request_id:%s, data:%s, reason:%s',
				$this->request_id, json_encode($this->attributes), $message->getMessage()));
			return false;
		}
	}
}


# vim: set noexpandtab ts=4 sts=4 sw=4 :
