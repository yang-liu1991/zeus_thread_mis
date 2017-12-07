<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_payment_history".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $company_id
 * @property string $account_id
 * @property string $pay_name_real
 * @property integer $pay_type
 * @property integer $action_type
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThPaymentHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_payment_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'company_id', 'account_id', 'pay_name_real', 'pay_type'], 'required'],
            [['user_id', 'company_id', 'pay_type', 'action_type', 'created_at', 'updated_at'], 'integer'],
            [['account_id'], 'string', 'max' => 45],
            [['pay_name_real'], 'string', 'max' => 255],
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
            'pay_name_real' => 'Pay Name Real',
            'pay_type' => 'Pay Type',
            'action_type' => 'Action Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

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
