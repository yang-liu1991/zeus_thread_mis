<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_email_template".
 *
 * @property integer $id
 * @property integer $sender
 * @property string $receiver
 * @property string $subject
 * @property string $content
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThEmailTemplate extends \yii\db\ActiveRecord
{
	/**
	 *	定义邮件的状态
	 */
	const	WAITING	= 0;
	const	RUNNING = 1;
	const	SUCCESS	= 2;
	const	FAILED	= 3;

	public $search;
	public $begin_time;
	public $end_time;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_email_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sender', 'receiver', 'subject', 'content'], 'required'],
            [['sender', 'status', 'created_at', 'updated_at'], 'integer'],
			[['status'], 'in', 'range' => [self::WAITING, self::RUNNING, self::SUCCESS, self::FAILED]],
            [['receiver', 'content'], 'string'],
            [['subject'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sender' => 'Sender',
            'receiver' => 'Receiver',
            'subject' => 'Subject',
            'content' => 'Content',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


	/**
	 *
	 */
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'created_at',
				'updatedAtAttribute' => 'updated_at',
			],
		];
	}


	/**
	 *	获取邮件发送状态
	 */
	public static function getEmailStatus($status)
	{
		switch($status)
		{
			case self::WAITING: return sprintf('<span class="btn btn-warning btn-xs">%s</span>', '等待发送');break;
			case self::RUNNING: return sprintf('<span class="btn btn-info btn-xs">%s</span>', '正在发送');break;
			case self::SUCCESS: return sprintf('<span class="btn btn-success btn-xs">%s</span>', '发送成功');break;
			case self::FAILED: return sprintf('<span class="btn btn-danger btn-xs">%s</span>', '发送失败');break;
		}
	}
}
