<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "Th_agency_creditlimit".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $account_id
 * @property string $account_name
 * @property integer $account_status
 * @property integer $company_id
 * @property integer $min_spend_cap
 * @property integer $spend_cap
 * @property integer $spend_cap_old
 * @property integer $amount_spent
 * @property integer $number
 * @property integer $action_type
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThAgencyCreditlimit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_agency_creditlimit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'account_id', 'account_name', 'account_status', 'company_id', 'min_spend_cap', 'spend_cap', 'spend_cap_old', 'amount_spent', 'number', 'action_type'], 'required'],
            [['user_id', 'account_status', 'company_id', 'min_spend_cap', 'spend_cap', 'spend_cap_old', 'amount_spent', 'number', 'action_type', 'created_at', 'updated_at'], 'integer'],
            [['account_id', 'account_name'], 'string', 'max' => 255],
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
            'account_id' => 'Account ID',
            'account_name' => 'Account Name',
            'account_status' => 'Account Status',
            'company_id' => 'Company ID',
            'min_spend_cap' => 'Min Spend Cap',
            'spend_cap' => 'Spend Cap',
            'spend_cap_old' => 'Spend Cap Old',
            'amount_spent' => 'Amount Spent',
            'number' => 'Number',
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
