<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-07-19 10:50:08
 */

namespace backend\models\account;

use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\web\UploadedFile;
use common\models\RequestApi;
use common\models\UploadImage;
use backend\models\user\UserModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use backend\models\ThreadBaseModel;
use backend\models\account\FbVertical;
use backend\models\record\ThEntityInfo;
use backend\models\record\ThAccountInfo;
use backend\models\record\ThEntityInfoSearch;


class EntityModel extends ThreadBaseModel
{
	/* 获取page id API */
	const PAGEID_API	= 'https://graph.facebook.com/v2.6/%s/pages';
	/* 获取app id API */
	const APPID_API		= 'https://graph.facebook.com/v2.6/%s/apps';


	/* 实体信息 */
	public $id;
	public $user_id;
	public $name_en;
	public $name_zh;
	public $address_en;
	public $address_zh;
	public $promotable_url;
	public $promotable_urls;
	public $official_website_url;
	public $promotable_page_ids;
	public $promotable_app_ids;
	public $promotable_page_urls;
	public $vertical;
	public $subvertical;
	public $is_smb = 0;
	public $payname;
	public $contact;
	public $business_registration;
	public $business_registration_id;
	public $business_registration_path;
	public $advertiser_business_id;
	public $audit_status = 0;
	public $audit_message;
	public $comment;
	public $created_at;
	public $updated_at;
	public $entity_note;

	/* 英文地址段信息 */
	public $full_name;
	public $address_line_1;
	public $address_line_2;
	public $city;
	public $state;
	public $zip;
	public $country;
	

	public function rules()
	{
		return [
            [['user_id', 'name_en', 'name_zh', 'address_en', 'address_zh', 'official_website_url', 'vertical', 'subvertical', 'is_smb', 'payname', 'contact', 'business_registration_id'], 'required', 'on' => ['create', 'update'], 'message' => '{attribute}不能为空！'],
            [['business_registration'], 'file', 'extensions' => ['jpg', 'jpeg', 'png'], 'on' => ['create', 'update'], 'message'=> '必须为jpg、jpeg、png文件！'],
			[['audit_status', 'audit_message'], 'required', 'on' => ['audit']],

			[['address_en'], 'string', 'max' => 2048],
			[['is_smb'], 'checkIsSmb', 'on' => ['create', 'update']],
            [['user_id', 'audit_status', 'is_smb', 'created_at', 'updated_at'], 'integer'],
            [['name_en', 'name_zh', 'address_zh', 'payname'], 'string', 'max' => 100],
            [['contact'], 'email', 'on' => ['create', 'update'], 'message' => '{attribute}非有效Email地址！'],
			[['official_website_url'], 'url', 'message' => '非合法Url'],
			[['promotable_page_ids', 'promotable_app_ids'], 'string'],
			[['promotable_page_ids', 'promotable_page_urls'], 'promotablePageValidate', 'skipOnEmpty' => false, 'on' => ['create', 'update']],
			[['promotable_url', 'promotable_urls', 'promotable_app_ids'], 'checkPromotableObject', 'on' => ['create', 'update'], 'message' => '非合法Url'],
			[['user_id', 'name_en', 'name_zh', 'address_en', 'address_zh', 'promotable_urls', 'official_website_url', 'vertical', 'subvertical', 'is_smb', 'promotable_page_ids', 'promotable_app_ids', 'payname', 'contact', 'business_registration', 'business_registration_id', 'business_registration_path', 'advertiser_business_id', 'promotable_page_urls', 'audit_status', 'audit_message', 'comment'], 'safe' ],
			[['full_name', 'address_line_1', 'address_line_2', 'city', 'state', 'zip', 'country'], 'safe']
        ];
	
	}


	/**
	 *	检测is_smb，当业务类型为gaming的时候，is_smb字段为非必须，否则为必须字段
	 */
	public function checkIsSmb()
	{
		if($this->vertical == 7) 
		{
			$this->is_smb = ThEntityInfoSearch::NO_SMB;
			return true;
		}
		if(in_array($this->is_smb, [ThEntityInfoSearch::IS_SMB, ThEntityInfoSearch::NO_SMB])) return true;
		$this->addError('is_smb', '请重新选择业务类型！');
	}


	/**
	 *	检测推广的属性
	 */
	public function checkPromotableObject()
	{
		if(!$this->promotable_url and !$this->promotable_app_ids)
        {
            $this->addError('promotable_url', '推广Appids和推广Url至少满足一项！');
        }
	}

	/**
	 *	Promotable Page Validate
	 *	Promotable Page Ids 和Promotable Page Urls必须有一项
	 */
	public function promotablePageValidate()
	{
		if(!$this->promotable_page_ids && !$this->promotable_page_urls)
		{
			$this->addError('promotable_page_ids', '推广Page Ids和推广Page Urls至少选一项！');
		}
	}

	/**
     * @inheritdoc
     */
    public function attributeLabels()
    {
		return [
            'id' => 'ID',
            'user_id' => '关联注册用户id',
            'name_zh' => '公司中文名称',
            'name_en' => '公司英文名称',
            'address_zh' => '公司中文地址',
            'address_en' => '公司英文地址',
            'promotable_urls' => '推广产品地址',
            'official_website_url' => '公司官网',
            'promotable_page_ids' => '推广Page Ids',
            'promotable_app_ids' => '推广App Ids',
			'promotable_page_urls' => '推广Page Urls',
			'promotable_url' => '推广链接',
            'vertical' => '业务类型',
            'subvertical' => '子业务类型',
			'is_smb' => '是否SMB',
            'payname' => '付款公司名称',
            'contact' => '联系人',
            'business_registration' => '公司营业执照',
			'business_registration_id' => '公司营业执照ID',
			'advertiser_business_id' => '公司授权BM ID',
            'audit_status' => '审核状态',
            'audit_message' => '审核信息',
			'comment'		=> '备注信息',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
	}


	/**
	 *	inheritdoc
	 */
	public function scenarios()
	{
		$scenarios = parent::scenarios();
		return array_merge($scenarios, [
			'create' => ['name_en', 'name_zh', 'address_en', 'address_zh', 'promotable_url', 'official_website_url', 'vertical', 'subvertical', 'is_smb', 'promotable_page_ids', 'promotable_app_ids', 'payname', 'contact', 'business_registration', 'business_registration_id', 'advertiser_business_id', 'business_registration_path', 'promotable_page_urls', 'full_name', 'address_line_1', 'address_line_2', 'city', 'state', 'zip', 'country', 'comment'],
			'update' => ['name_en', 'name_zh', 'address_en', 'address_zh', 'promotable_url', 'official_website_url', 'vertical', 'subvertical', 'is_smb', 'promotable_page_ids', 'promotable_app_ids', 'payname', 'contact', 'business_registration_id', 'advertiser_business_id', 'business_registration_path', 'promotable_page_urls', 'full_name', 'address_line_1', 'address_line_2', 'city', 'state', 'zip', 'country', 'comment'],
			'audit'	=> ['audit_status', 'audit_message'],
		]);
	}


	/**
	 *	get dataProvider
	 */
	public function getDataProvider($params=null)
	{
		$query = ThEntityInfo::find();
		if(is_array($params))
		{
			$query = ThEntityInfo::find()->andFilterWhere($params);
		}

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 30
			]
		]);
	}


	/**
	 *	create
	 */
	public function entityCreate()
	{
		try {
			$entityObj = new ThEntityInfo();

			$entityObj->attributes = $this->attributes;
			array_push($this->promotable_urls, $this->promotable_url);
			$entityObj->promotable_urls = json_encode(['normal' => array_values(array_filter(array_unique($this->promotable_urls))), 'abnormal' => []]);
			$entityObj->address_en		= json_encode([
				'full_name' => $this->full_name, 
				'address_line_1' => $this->address_line_1,
				'address_line_2' => $this->address_line_2,
				'city' => $this->city, 
				'state' => $this->state, 
				'zip' => $this->zip, 
				'country' => $this->country
			]);
			$entityObj->vertical		= $this->getVertical($this->vertical);
			$entityObj->subvertical		= $this->getSubvertical($entityObj->vertical, $this->subvertical);
			$entityObj->promotable_page_ids		= json_encode(explode(",", $this->promotable_page_ids));
			$entityObj->promotable_page_urls	= json_encode(explode(",", $this->promotable_page_urls));
			$entityObj->promotable_app_ids		= !empty($this->promotable_app_ids) ? json_encode(explode(",", $this->promotable_app_ids)) : Null;
			$entityObj->business_registration	= $this->business_registration_path;
			$entityObj->comment			= json_encode([$this->comment]);

			if($entityObj->validate() && $entityObj->save())
			{
				$this->id = $entityObj->id;
				Yii::info(sprintf('entityCreate [success] [User]:%s, [Data]:%s',
					UserModel::getLoginInfo()->email, json_encode($entityObj->attributes)
				));
				return true;
			}
			throw New Exception(sprintf('entityCreate Exception, reason:%s', json_encode($entityObj->getErrors())));
		} catch(Exception $message) {
			Yii::error(sprintf('[entityCreate] [error] [Reason]:%s, [User]:%s, [Data]:%s',
				json_encode($message->getMessage()), UserModel::getLoginInfo()->email, json_encode($this->attributes)
			));
			return false;
		}
	}

	/**
	 * update
	 */
	public function entityUpdate($id)
	{
		try {
			$entityObj = self::findOne($id);
			$entityObj->attributes = $this->attributes;
			array_push($this->promotable_urls, $this->promotable_url);
			$entityObj->promotable_urls = json_encode(['normal' => array_values(array_filter(array_unique($this->promotable_urls))), 'abnormal' => []]);
			$entityObj->address_en		= json_encode([
				'full_name' => $this->full_name, 
				'address_line_1' => $this->address_line_1,
				'address_line_2' => $this->address_line_2,
				'city' => $this->city, 
				'state' => $this->state, 
				'zip' => $this->zip, 
				'country' => $this->country
			]);
			$entityObj->vertical		= $this->getVertical($this->vertical);
			$entityObj->subvertical		= $this->getSubvertical($entityObj->vertical, $this->subvertical);
			$entityObj->promotable_page_ids	= json_encode(explode(",", $this->promotable_page_ids));
			$entityObj->promotable_page_urls	= json_encode(explode(",", $this->promotable_page_urls));
			$entityObj->promotable_app_ids	= !empty($this->promotable_app_ids) ? json_encode(explode(",", $this->promotable_app_ids)) : Null;
			$entityObj->business_registration = $this->business_registration_path;
			$entityObj->comment			= json_encode([$this->comment]);
			
			if($entityObj->validate() && $entityObj->save())
			{
				Yii::info(sprintf('entityUpdate [success] [Id]:%d, [User]:%s, [Data]:%s',
					$id, UserModel::getLoginInfo()->email, json_encode($entityObj->attributes)
				));
				return true;
			}
			throw New Exception(sprintf('entityUpdate Exception, reason:%s', json_encode($entityObj->getErrors())));
		} catch(Exception $message) {
			Yii::error(sprintf('[entityUpdate] [error] [Reason]:%s, [User]:%s, [Data]:%s',
				json_encode($message->getMessage()), UserModel::getLoginInfo()->email, json_encode($this->attributes)
			));
			return false;
		}	
	}


	/*
	 *	update audit status
	 */
	public function entityUpdateById($id, $updateParams, $extaction)
	{
		try{
			$result = ThEntityInfo::updateall($updateParams, 'id = :id', ['id' => $id]);
			$record	= ThEntityInfo::find()->where(array_merge($updateParams, ['id' => $id]))->one(); 

			if($result > 0 || $record)
			{
				Yii::info(sprintf('[entityUpdateById] [action]:%s, [success] [Id]:%d, [User]:%s, [Data]:%s',
					$extaction, $id, UserModel::getLoginInfo()->email, json_encode($updateParams)
				));
				return true;
			}
			return false;
		} catch(Exception $message) {
			Yii::error(sprintf('[entityUpdateById] [action]:%s [error] [Reason]:%s, [User]:%s, [Data]:%s',
				$extaction, json_encode($message->getMessage()), UserModel::getLoginInfo()->email,
				json_encode($updateParams)
			));
		}
	}


	/**
	 * initAttributes
	 * @return
	 */
	private function initAttributes($object)
	{
		$this->attributes	= $object->attributes;
		$this->business_registration	= Yii::$app->params['ugcServer']['imgdir'].$object->business_registration;
		$promotable_urlAll	= json_decode($this->promotable_urls, true);
		$promotable_urls	= array_merge($promotable_urlAll['normal'], $promotable_urlAll['abnormal']);
		$this->vertical		= $this->getVerticalIndex($object->vertical);
		$this->subvertical	= $this->getSubverticalIndex($object->vertical, $object->subvertical);
		$this->promotable_url	= !empty($promotable_urls) ? $promotable_urls[0] : '';
		$this->promotable_urls	= array_splice($promotable_urls, 1);
		$this->promotable_page_ids	= implode(",", json_decode($this->promotable_page_ids));
		$this->promotable_page_urls	= !empty($this->promotable_page_urls) ? implode(",", json_decode($this->promotable_page_urls)) : '';
		$this->promotable_app_ids	= !empty($this->promotable_app_ids) ? implode(",", json_decode($this->promotable_app_ids)) : '';
		$this->business_registration_path = $object->business_registration;
		$this->comment		= !empty($this->comment) ? implode('<br/>', json_decode($this->comment)) : '';
	}


	/**
	 * getAttributes
	 * @parmas int id
	 * @params bool byUser
	 */
	public function getAttributeByUser($id)
	{
		$entityObj = self::findWhere(['id' => $id, 'user_id' => UserModel::getLoginInfo()->id]);
		if(!$entityObj) 
			throw new NotFoundHttpException('The requested page does not exist.');
		$this->initAttributes($entityObj);
	}


	/**
	 * getAttributes
	 * @parmas int id
	 * @params bool byId
	 */
	public function getAttributeById($id)
	{
		$entityObj = self::findWhere(['id' => $id]);
		if(!$entityObj) 
			throw new NotFoundHttpException('The requested page does not exist.');
		$this->initAttributes($entityObj);
	}


	/**
	 *	getVertical
	 *	@params	int	id
	 */
	private function getVertical($verticalId)
	{
		return FbVertical::getVerticals()[$verticalId];
	}

	/**
	 *	getSubverticals
	 */
	private function getSubVertical($verticalId, $subverticalId)
	{
		return FbVertical::getSubVerticals($verticalId)[$subverticalId];
	}

	/**
	 *	getVertical index
	 */
	private function getVerticalIndex($vertical)
	{
		return FbVertical::getVerticalsIndex($vertical);
	}

	/**
	 *	getSubvertical index
	 */
	private function getSubverticalIndex($vertical, $subvertical)
	{
		return FbVertical::getSubVerticalIndex($vertical, $subvertical);
	}

	
	/**
	 *	upload file
	 *	@return
	 */
	public function uploadImage()
	{
		$uploadObjBusinessRegistration	= UploadedFile::getInstance($this, 'business_registration');
		if($uploadObjBusinessRegistration)
		{
			$businessRegistrationPath	= UploadImage::UploadImageToCloud($uploadObjBusinessRegistration);
			if($businessRegistrationPath)
			{
				$this->business_registration	= $businessRegistrationPath['key'];
			} else {
				$this->business_registration	= '';
			}
			return $this->business_registration;
		}
		return false;
	}

	/**
	 *	findOne
	 */
	public static function findOne($id)
	{
		return ThEntityInfo::findOne($id);
	}

	/**
	 *	findWhere
	 *	@params array
	 *	@return Obj
	 */
	public static function findWhere($params)
	{
		return ThEntityInfo::find()->where($params)->one();
	}
	
	/**
	 *	findWhere all
	 *	@params array
	 *	@return Obj
	 */
	public static function findWhereAll($params)
	{
		return ThEntityInfo::find()->where($params)->all();
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
