<?php

namespace backend\models\record;

use Yii;
use yii\data\ActiveDataProvider;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_account_info".
 *
 * @property integer $id
 * @property string $act_id
 * @property string $fbaccount_id
 * @property string $fbaccount_name
 * @property string $business_id
 * @property string $request_id
 * @property string $business_agency_id
 * @property integer $user_id
 * @property integer $company_id
 * @property integer $entity_id
 * @property string $timezone
 * @property string $timezone_id
 * @property string $referral
 * @property integer $spend_cap
 * @property integer $amount_spent
 * @property integer $balance
 * @property string $pay_name_real
 * @property integer $pay_type
 * @property string $pay_comment
 * @property string $extended_credit_id
 * @property integer $status
 * @property string $additional_comment
 * @property string $reasons
 * @property string $type
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThAccountInfo extends \yii\db\ActiveRecord
{
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_account_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'entity_id', 'timezone_id', 'status'], 'required'],
            [['user_id', 'company_id', 'entity_id', 'spend_cap', 'amount_spent', 'balance', 'pay_type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['pay_comment', 'additional_comment', 'reasons'], 'string'],
            [['act_id', 'fbaccount_id', 'business_id', 'request_id', 'business_agency_id'], 'string', 'max' => 45],
			[['fbaccount_name'], 'string', 'max' => 100],
            [['timezone', 'timezone_id', 'referral'], 'string', 'max' => 50],
            [['pay_name_real'], 'string', 'max' => 100],
            [['extended_credit_id'], 'string', 'max' => 255],
			[['pay_comment'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'act_id' => 'Act ID',
            'fbaccount_id' => 'Fbaccount ID',
            'fbaccount_name' => 'Fbaccount Name',
            'business_id' => 'Business ID',
            'request_id' => 'Request ID',
            'business_agency_id' => 'Business Agency ID',
            'user_id' => 'User ID',
            'company_id' => 'Company ID',
            'entity_id' => 'Entity ID',
            'timezone' => 'Timezone',
            'timezone_id' => 'Timezone ID',
            'referral' => 'Referral',
			'spend_cap'	=> 'Spend Cap',
			'amount_spent' => 'Amount Spent',
			'balance'	=> 'Balance',
            'pay_name_real' => 'Pay Name Real',
            'pay_type' => 'Pay Type',
            'pay_comment' => 'Pay Comment',
            'extended_credit_id' => 'Extended Credit ID',
            'status' => 'Status',
            'additional_comment' => 'Additional Comment',
            'reasons' => 'Reasons',
            'type' => 'Type',
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
	 *	进行多表关联，一个实体可以会有多个帐号
	 *	通过子表的entity_id关联主表的id
	 */
	public function getEntityInfo()
	{
		return $this->hasOne(ThEntityInfo::className(), ['id' => 'entity_id']);	
	}
}
