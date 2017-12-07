<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_agency_business".
 *
 * @property integer $id
 * @property string $business_id
 * @property string $business_name
 * @property integer $company_id
 * @property string $access_token
 * @property string $referral
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThAgencyBusiness extends \yii\db\ActiveRecord
{
    const   STATUS_DELETE = 0;
    const   STATUS_CREATE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_agency_business';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['business_id', 'business_name', 'company_id'], 'required'],
            [['company_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['business_id', 'business_name', 'access_token', 'referral'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_id' => 'Business ID',
            'business_name' => 'Business Name',
            'company_id' => 'Company ID',
            'access_token' => 'Access Token',
            'referral' => 'Referral',
            'status' => 'Status',
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
