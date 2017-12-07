<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_email_record".
 *
 * @property integer $id
 * @property integer $tid
 * @property string $receiver
 * @property integer $status
 * @property string $reason
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThEmailRecord extends \yii\db\ActiveRecord
{

	const	WAITING	= 0;
	const	RUNNING	= 1;
	const	SUCCESS	= 2;
	const	FAILED	= 3;

	public $begin_time;
	public $end_time;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_email_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tid', 'receiver'], 'required'],
            [['tid', 'status', 'created_at', 'updated_at'], 'integer'],
            [['receiver'], 'string', 'max' => 50],
            [['reason'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tid' => 'Tid',
            'receiver' => 'Receiver',
            'status' => 'Status',
            'reason' => 'Reason',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

	/**
	 *	返回邮件发送状态
	 */
	public static function getRecordStatus($status)
	{
		switch($status)
		{
		case self::WAITING : return sprintf('<span style="background-color:#ec971f;width:72px;" class="badge">%s</span>', 'WAITING');
		case self::RUNNING : return sprintf('<span style="background-color:#337ab7;width:72px;" class="badge">%s</span>', 'RUNNING');
		case self::SUCCESS : return sprintf('<span style="background-color:#449d44;width:72px;" class="badge">%s</span>', 'SUCCESS');
		case self::FAILED  : return sprintf('<span style="background-color:#c9302c;width:72px;" class="badge">%s</span>', 'FAILED');
		}
	}

	public static function getSendStatus()
	{
		return [
			'WAITING'	=> self::WAITING,
			'RUNNING'	=> self::RUNNING,
			'SUCCESS'	=> self::SUCCESS,
			'FAILED'	=> self::FAILED
		];
	}

	/**
	 *	关联template表
	 */
	public function getEmailTemplate()
	{
		return $this->hasOne(ThEmailTemplate::className(), ['id' => 'tid']);
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
}
