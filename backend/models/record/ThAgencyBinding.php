<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_agency_binding".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $account_id
 * @property string $account_name
 * @property integer $company_id
 * @property string $business_id
 * @property string $business_name
 * @property string $access_type
 * @property integer $access_status
 * @property string $permitted_roles
 * @property integer $action_type
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThAgencyBinding extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_agency_binding';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'account_id', 'account_name', 'company_id', 'business_id', 'business_name', 'access_type', 'action_type'], 'required'],
            [['user_id', 'company_id', 'access_status', 'action_type', 'created_at', 'updated_at'], 'integer'],
            [['account_id', 'account_name', 'business_id', 'business_name', 'permitted_roles'], 'string', 'max' => 255],
            [['access_type'], 'string', 'max' => 45],
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
            'company_id' => 'Company ID',
            'business_id' => 'Business ID',
            'business_name' => 'Business Name',
            'access_type' => 'Access Type',
            'access_status' => 'Access Status',
            'permitted_roles' => 'Permitted Roles',
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
