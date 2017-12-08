<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-12-05 11:20:46
 */

namespace backend\models\message;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\data\ActiveDataProvider;
use backend\models\ThreadBaseModel;
use backend\models\account\FbTimezoneIds;
use common\models\AmountConversion;
use backend\models\record\ThMessage;
use backend\models\record\ThMessageSearch;
use backend\models\record\ThAgencyBusinessSearch;
use backend\models\record\ThAgencyCreditlimitSearch;
use backend\models\record\ThAgencyBindingSearch;


class MessageModel extends ThreadBaseModel
{
	
	public $send_id;
	public $read_id;
	public $message;
	public $status;

	
	/**
	 *	rules
	 */
	public function rules()
	{
		return [
			[['send_id', 'message', 'type', 'status'], 'required'],
			[['send_id', 'read_id', 'type', 'status'], 'integer']
		];
	}


	/**
	 *	@inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'send_id'	=> '发送者',
			'read_id'	=> '读取者',
			'message'	=> '任务内容',
            'type'      => '消息类型',
			'status'	=> '任务状态'
		];
	}


	/**
	 *	获取任务信息
	 */
	public static function getMessage($message_type = null)
	{
		try {
			$searchParams	= [];
			$searchParams['ThMessageSearch']['status']	= 0;
            if(in_array($message_type, [ThMessageSearch::CREATE_ACCOUNT, ThMessageSearch::CHANGE_CREDITLIMIT, ThMessageSearch::CHANGE_BINDING, ThMessageSearch::CHANGE_NAME]))
                $searchParams['ThMessageSearch']['type']    = $message_type;
			$searchModel	= new ThMessageSearch();
			return $searchModel->search($searchParams);	
		} catch(Exception $message) {
			Yii::error(sprintf("[getMessage] Exception, message_type:%d, reason:%s",  $message_type, $message->getMessage()));
			return false;
		}
	}


	/**
	 *	消息格式化
	 *  @params	int $message_type
     *  @params string  $message
	 *	@return	string
	 */
	public static function formatMessage($message_type, $message)
	{
		try {
			$message_obj	= json_decode($message);
            $message_form   = '';
            switch($message_type)
            {
                /* 开户信息 */
                case ThMessage::CREATE_ACCOUNT:
                    $message_form .= '<table class="table"><thead><tr><th>实体Id</th><th>Agency Name</th><th>时区</th><th>数量</th></tr></thead><tbody>';
                    if($message_obj->accounts)
                    {
                        $timezone_ids = FbTimezoneIds::getTimezoneIdName();
                        foreach($message_obj->accounts as $account)
                        {
                            $message_form .= sprintf('<tr><td>%d</td><td>%s</td><td>%s</td><td>%d</td></tr>', $message_obj->entity_id, $message_obj->agency_name, $timezone_ids[$account->timezone_id], $account->number);
                        }
                    }
                    $message_form .= '</tbody></table>';
                    break;
                /* 充值信息 */
                case ThMessage::CHANGE_CREDITLIMIT:
                    $message_form .= '<table class="table"><thead><tr><th>Account Id</th><th>Account Name</th><th>Spend Limit</th><th>Operation</th><th>Number</th></tr></thead>';
                    if($message_obj->accounts)
                    {
                        $action_type = ThAgencyCreditlimitSearch::getActionType();
                        foreach($message_obj->accounts as $account)
                        {
                            $message_form .= sprintf('<tr><td>%s</td><td>%s</td><td>%.2f$</td><td>%s</td><td>%.2f$</td></tr>',
                                $account->account_id, $account->account_name, $account->spend_cap, $action_type[$account->action_type], $account->number);
                        }
                    }
                    $message_form .= '</tbody></table>';
                    break;
                /* 关联信息 */
                case ThMessage::CHANGE_BINDING:
                    $message_form .= '<table class="table"><thead><tr><th>Account Id</th><th>Account Name</th><th>Business Id</th><th>Operation</th><th>Permitted Roles</th></th></tr></thead>';
                    if($message_obj->accounts)
                    {
                        $action_type = ThAgencyBindingSearch::getActionType();
                        foreach ($message_obj->accounts as $account)
                        {
                            $message_form .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                                $account->account_id, $account->account_name, $account->business_id, $action_type[$account->action_type], $account->permitted_roles);
                        }
                    }
                    $message_form .= '</tbody></table>';
                    break;
                /* 更名信息 */
                case ThMessage::CHANGE_NAME:
                    $message_form .= '<table class="table"><thead><tr><th>Account Id</th><th>Account Name</th><th>New Account Name</th></tr></thead>';
                    if($message_obj->accounts)
                    {
                        foreach ($message_obj->accounts as $account)
                        {
                            $message_form .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
                                $account->account_id, $account->account_name, $account->new_account_name);
                        }
                    }
                    $message_form .= '</tbody></table>';
                    break;

            }
            return $message_form;
		} catch(Exception $message) {
			Yii::error(sprintf("[formatMessage] Exception, message_type:%d, message:%s, reason:%s",
				$message_type, $message, $message->getMessage()));
			return '';
		}
	}


    /**
     * 整理消息属性,返回json
     * @params  int     $message_ype
     * @params  array   $attributes
     * @return  string
     */
    private static function buildingMessage($message_type, $attributes)
    {
        try {
            switch($message_type)
            {
                /* 开户信息 */
                case ThMessage::CREATE_ACCOUNT:
                    $business = ThAgencyBusinessSearch::getBusinessByReferral($attributes['referral']);
                    $message['agency_name'] = !empty($business->business_name) ? $business->business_name : '';
                    $message['entity_id']   = $attributes['entity_id'];
                    if($attributes['request_list'])
                    {
                        foreach($attributes['request_list'] as $index=>$request)
                        {
                            $message['accounts'][$index]['timezone_id'] = $request['timezone_id'];
                            $message['accounts'][$index]['number']      = $request['number'];
                        }
                    }
                    break;

                /* 帐户绑定信息 */
                case ThMessage::CHANGE_BINDING:
                    $message['accounts']    = $attributes['accounts'];
                    break;

                /* 帐户充值信息 */
                case ThMessage::CHANGE_CREDITLIMIT:
                    $message['accounts']    = $attributes['accounts'];
                    break;

                /* 帐户更名信息 */
                case ThMessage::CHANGE_NAME:
                    $message['accounts']    = $attributes['accounts'];
                    break;
            }
            return json_encode($message);
        } catch(Exception $message) {
            Yii::error(sprintf('[buildingMessage] Exception, message_type:%d, attributes:%s',
                $message_type, json_encode($attributes)));
            return '';
        }
    }


	/**
	 *  保存任务信息
     *  @params  int $message_type
     *  @params  obj $attributes
     *  return
	 */
	public static function saveMessage($message_type, $attributes)
	{
		try {

			$model = new ThMessage();
			$model->send_id	= !empty($attributes['user_id']) ? $attributes['user_id'] : 0;
            $model->type    = $message_type;
            $model->message	= self::buildingMessage($message_type, $attributes);
			/* status 0表示未读取，1表示已读取*/
			$model->status	= 0;	
			$model->created_at	= time();
			$model->updated_at	= time();

			if($model->validate() && $model->save())
			{
				Yii::info(sprintf("[saveMessage] Success, send_id:%d, data:%s", $attributes['user_id'], json_encode($model->attributes)));
				return true;
			}
			throw new Exception("saveMessage Error!");
		} catch(Exception $message) {
			Yii::error(sprintf("[saveMessage] Exception, send_id:%d, message:%s, reason:%s", 
				$attributes['user_id'], $message, $message->getMessage()));
			return false;
		} 	
	}


    /**
     *  按消息类型获取消息总量
     *  @params integer message_type
     *  @return integer
     */
    public static function getMessageTotal($message_type=null)
    {
        $message_obj = self::getMessage($message_type);
        if($message_obj) return $message_obj->getTotalCount();
        return false;
    }


	/**
	 *	更新消息状态
	 *	@params	int	$id
	 *	@return bool
	 */
	public static function updateMessageStatus($id, $read_id)
	{
		try {
			$sql	= sprintf("update th_message set read_id = %d, status = 1, updated_at = %d where id = %d", 
				$read_id, time(), $id);
			$connection = Yii::$app->db;
			$command	= $connection->createCommand($sql);
			if($command->execute()) return true;
		} catch(Exception $message) {
			Yii::error(sprintf("[updateMessageStatus] Exception, id:%s, reason:%s", $id, $message->getMessage()));
			return false;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
