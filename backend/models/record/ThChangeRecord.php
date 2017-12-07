<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_change_record".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $company_id
 * @property string $account_id
 * @property string $content
 * @property integer $type
 * @property integer $status
 * @property string $reason
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThChangeRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_change_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'company_id', 'type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['content', 'reason'], 'string'],
            [['account_id'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'company_id' => 'Company ID',
            'account_id' => 'Account ID',
            'content' => 'Content',
            'type' => 'Type',
            'status' => 'Status',
            'reason' => 'Reason',
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
}
