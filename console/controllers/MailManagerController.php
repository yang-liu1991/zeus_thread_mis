<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-03-05 16:33:19
 */

namespace console\controllers;

use Yii;
use yii\base\Exception;
use console\models\SendMailModel;
use console\controllers\ConsoleBaseController;


class MailManagerController extends ConsoleBaseController
{
	public $tid;
    
    public function options()
    {
        return ['tid'];
    }
    

    /**
	 *	发送邮件的方法
	 */
	public function actionSendMail()
	{
		$mailModel = new SendMailModel();
		$mailModel->tid = $this->tid;
		$emailTemplateObj = $mailModel->getEmailTemplate();
		if($emailTemplateObj)
		{
			$mailModel->sendMail($emailTemplateObj);
			return true;
		}
		throw New Exception('send mail Exception!');
	}
}	

# vim: set noexpandtab ts=4 sts=4 sw=4 :
