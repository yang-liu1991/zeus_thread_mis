<?php

namespace backend\models\record;

use Yii;
use backend\models\record\ThAccountInfo;

/**
 * This is the model class for table "th_adcreatives".
 *
 * @property integer $id
 * @property string $account_id
 * @property string $ad_id
 * @property string $ad_name
 * @property string $ad_message
 * @property string $creative_id
 * @property string $image_url
 * @property integer $audit_status
 * @property string $audit_message
 * @property integer $start_time
 * @property string $promoted_url
 * @property string $created_at
 * @property string $updated_at
 */

class ThAdCreatives extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_adcreatives';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
		return [
            [['account_id', 'ad_id', 'ad_name', 'ad_message', 'creative_id', 'image_url', 'audit_message', 'start_time', 'promoted_url', 'created_at', 'updated_at'], 'required'],
            [['ad_message'], 'string'],
            [['audit_status', 'start_time'], 'integer'],
            [['account_id', 'ad_id', 'ad_name', 'creative_id', 'promoted_url'], 'string', 'max' => 255],
            [['image_url'], 'string', 'max' => 1024],
            [['audit_message', 'created_at', 'updated_at'], 'string', 'max' => 45],
        ];
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
			'account_id' => 'Account ID',
			'ad_id'	=> 'Ad ID',
            'creative_id' => 'Creative ID',
            'ad_name' => 'Creative Name',
            'image_url' => 'Image Url',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

	
	/**
	 *	关联查询条件
	 */
	public function getAccountInfo()
	{
		return $this->hasOne(ThAccountInfo::className(), ['fbaccount_id' => 'account_id']);
	}
}
