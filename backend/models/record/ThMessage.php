<?php

namespace backend\models\record;

use Yii;

/**
 * This is the model class for table "th_message".
 *
 * @property integer $id
 * @property integer $send_id
 * @property integer $read_id
 * @property string $message
 * @property integer $type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThMessage extends \yii\db\ActiveRecord
{

    /**
     *  定义消息类型的常量
     */
    const CREATE_ACCOUNT        = 0;
    const CHANGE_CREDITLIMIT    = 1;
    const CHANGE_BINDING        = 2;
    const CHANGE_NAME           = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['send_id', 'message'], 'required'],
            [['send_id', 'read_id', 'type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['message'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'send_id' => 'Send ID',
            'read_id' => 'Read ID',
            'message' => 'Message',
            'type'    => 'Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 返回message title
     */
    public static function getMessageTitle($messageType)
    {
        switch($messageType)
        {
            case self::CREATE_ACCOUNT:
                return 'Create Account Message';break;
            case self::CHANGE_CREDITLIMIT:
                return 'Change Credit Limit Message';break;
            case self::CHANGE_BINDING:
                return 'Change Binding Message';break;
            case self::CHANGE_NAME:
                return 'Change Account Name Message';break;
            default:
                return $messageType;
        }
    }
}
