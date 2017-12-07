<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "th_entity_info".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name_en
 * @property string $name_zh
 * @property string $address_en
 * @property string $address_zh
 * @property string $promotable_urls
 * @property string $official_website_url
 * @property string $promotable_page_ids
 * @property string $promotable_app_ids
 * @property string $promotable_page_urls
 * @property string $vertical
 * @property string $subvertical
 * @property integer $is_smb
 * @property string $payname
 * @property string $contact
 * @property string $business_registration
 * @property string $business_registration_id
 * @property string $advertiser_business_id
 * @property string $additional_comment
 * @property integer $audit_status
 * @property string $audit_message
 * @property string $comment
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThEntityInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_entity_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name_en', 'name_zh', 'address_en', 'address_zh', 'promotable_urls', 'official_website_url', 'vertical', 'subvertical', 'payname', 'contact', 'business_registration', 'business_registration_id', 'audit_status'], 'required'],
            [['user_id', 'is_smb', 'audit_status', 'created_at', 'updated_at'], 'integer'],
            [['promotable_urls', 'additional_comment', 'audit_message', 'comment'], 'string'],
            [['name_en', 'name_zh', 'address_zh', 'payname'], 'string', 'max' => 100],
            [['address_en'], 'string', 'max' => 2048],
            [['official_website_url', 'promotable_page_ids', 'promotable_app_ids', 'promotable_page_urls', 'business_registration', 'business_registration_id', 'advertiser_business_id'], 'string', 'max' => 255],
            [['vertical', 'subvertical', 'contact'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
		return [
            'id' => 'ID',
            'user_id' => '关联注册用户id',
            'name_zh' => '注册公司中文名称',
            'name_en' => '注册公司英文名称',
            'address_zh' => '注册公司中文地址',
            'address_en' => '注册公司英文地址',
            'promotable_urls' => '推广产品地址',
            'official_website_url' => '注册公司官网',
            'promotable_page_ids' => '推广Page Ids',
			'promotable_page_urls' => '推广Page Urls',
            'promotable_app_ids' => '推广App Ids',
            'extended_credit_id' => 'Extended Credit ID',
            'vertical' => '行业类型',
            'subvertical' => '子行业类型',
			'is_smb' => '是否SMB',	
            'payname' => '付款公司名称',
            'contact' => '联系人',
            'business_registration' => '注册公司营业执照',
            'business_registration_id' => '注册公司营业执照ID',
			'advertiser_business_id' => '授权BM ID',	
			'entity_note' => '备注',
            'audit_status' => '审核状态',
            'audit_message' => '审核信息',
			'comment'	=> '备注信息',
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
	public function getAccountInfo()
	{
		return $this->hasMany(ThAccountInfo::className(), ['entity_id' => 'id']);	
	}

	/**
	 *	支付信息关联
	 *	通过子表的entity_id关联主表的id	
	 */
	public function getPaymentInfo()
	{
		return $this->hasOne(ThPayment::className(), ['entity_id' => 'id']);	
	}

	/**
	 *	根据帐户状态，返回关联数据
	 *	@params int status
	 *	@return ActiveQuery
	 */
	public function getAccountInfoByStatus($status=0)
	{
		return $this->hasMany(ThAccountInfo::className(), ['entity_id' => 'id'])
			->where('status = :status', [':status' => $status])
			->orderBy('id');
	}

	/**
	 *	根据account id，返回关联数据
	 *	@params	str	fbaccount_id
	 *	@return
	 */
	public function getAccountInfoByAccountId($fbaccount_id)
	{
		return $this->hasMany(ThAccountInfo::className(), ['entity_id' => 'id'])
			->where('fbaccount_id = :fbaccount_id', [':fbaccount_id' => $fbaccount_id])
			->orderBy('id');
	}

	/**
	 *	根据公司中文名称，返回关联数据
	 *	@params	str	name_zh
	 *	@return
	 */
	public function getAccountInfoByName($name)
	{
		return $this->hasMany(ThAccountInfo::className(), ['entity_id' => 'id'])
			->where('name_zh = :name_zh', [':name_zh' => $name])
			->orderBy('id');
	}

	/**
	 *	根据id返回公司名
	 *	@params	int id
	 *	@return
	 */
	public static function getCompanyName($id)
	{
		return self::find(['select' => ['name_zh']])->where(['id' => $id])->one(); 
	}
}
