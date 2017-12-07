<?php


namespace backend\models\agency;

use Yii;
use yii\base\Model;
use yii\base\Exception;
use backend\models\user\User;
use backend\models\ThreadBaseModel;
use backend\models\record\ThAgencyBusiness;

class AgencyModel extends ThreadBaseModel
{
    public $id;
    public $business_id;
    public $business_name;
    public $company_id;
    public $access_token;
    public $referral;


    /**
     *inheritdoc
     */
    public function rules()
    {
        return [
            [['business_id', 'business_name', 'referral'], 'required', 'on' => ['create', 'update'], 'message' => '{attribute}不能为空!'],
            [['company_id', 'created_at', 'updated_at'], 'integer', 'on' => ['create', 'update']],
            [['business_id', 'business_name', 'access_token', 'referral'], 'string', 'max' => 255, 'on' => ['create', 'update']],
            [['referral'], 'email', 'message' => '{attribute}不是一个有效的电子邮箱地址!'],
            [['id', 'user_id', 'company_id', 'access_token'], 'safe']
        ];
    }


    /**
     * inheritdoc
     */
    public function scenarios()
    {
        return [
            'create' => ['id', 'user_id', 'business_id', 'business_name', 'company_id', 'referral', 'access_token'],
            'update' => ['id', 'user_id', 'business_id', 'business_name', 'company_id', 'referral', 'access_token'],
        ];
    }


    /**
     *  attributes
     */
    public function attributeLabels()
    {
        return [
            'business_id'       => 'BM ID',
            'business_name'     => 'BM Name',
            'referral'          => 'Referral Email',
            'access_token'      => 'Access Token'
        ];
    }


    /**
     *  Init
     */
    public function init()
    {
        parent::init();
        parent::setUserAttributes();
    }
    

    /**
     *  InitAttributes
     */
    public function initAttributes($id)
    {
        $model = self::findOne($id);
        $this->attributes = $model->attributes;
    }


    /**
     *  Create Agency
     *  return  bool
     */
    public function agencyCreate()
    {
        try {
            $model = new ThAgencyBusiness;
            $model->attributes  = $this->attributes;
            $model->company_id  = $this->getCompanyId();

            if($model->validate() && $model->save())
            {
                $this->id   = $model->id;
                Yii::info(sprintf('[agencyCreate] success, id:%d, user_id:%d, data:%s',
                    $this->id, $this->user_id, json_encode($this->attributes)));
                return true;
            }
            throw new Exception(sprintf('agencyCreate validate Error:%s', json_encode($model->getErrors())));
        } catch(Exception $message) {
            Yii::error(sprintf('[agencyCreate] Exception, id:%d, user_id:%d, data:%s, reason:%s',
                $this->id, $this->user_id, json_encode($this->attributes), $message->getMessage()));
            return false;
        }
    }


    /**
     *  Update Agency
     *  @params int $id
     *  @return bool
     */
    public function agencyUpdate($id)
    {
        try {
            $model = self::findOne($id);
            $model->attributes  = $this->attributes;
            if($model->validate() && $model->save())
            {
                Yii::info(sprintf('[agencyUpdate] success, id:%d, user_id:%d, data:%s',
                    $id, $this->user_id, json_encode($this->attributes)));
                return true;
            }
            throw new Exception(sprintf('agencyUpdate validate Error:%s', json_encode($model->getErrors())));
        } catch(Exception $message) {
            Yii::error(sprintf('[agencyUpdate] Exception, id:%d, user_id:%d, data:%s, reason:%s',
                $id, $this->user_id, json_encode($this->attributes), $message->getMessage()));
            return false;
        }

    }


    /**
     *  Delete Agency
     *  @params int $id
     *  @return bool
     */
    public function agencyDelete($id)
    {
        try {
            $result = ThAgencyBusiness::updateAll(['status' => ThAgencyBusiness::STATUS_DELETE], 'id=:id', [':id' => $id]);
            if($result >= 1)
            {
                Yii::info(sprintf('[agencyDelete] success id:%d, user_id:%d, result:%d',
                    $id, $this->user_id, $result));
                return true;
            }
            throw new Exception(sprintf('agencyDelete Error, result:%d', $result));
        } catch(Exception $message) {
            Yii::error(sprintf('[agencyDelete] Exception, id:%d, user_id:%d, reason:%s',
                $id, $this->user_id, $message->getMessage()));
            return false;
        }
    }


    /**
     *  Get company_id
     */
    private function getCompanyId()
    {
        try {
            $sql    = 'select max(company_id) as max_company_id from th_agency_business';
            $command    = Yii::$app->db->createCommand($sql);
            $result     = $command->queryOne();
            if($result)
            {
                $max_ompany_d   = !empty($result['max_company_id']) ? $result['max_company_id'] : 0;
                if(!$max_ompany_d) throw new Exception('Unknow max_company_id!');
                $company_id = $max_ompany_d + 1;
                Yii::info(sprintf('[getCompanyId] success, business_id:%s, business_name:%s, company_id:%d',
                    $this->business_id, $this->business_name, $company_id));
                return $company_id;
            }
            throw new Exception(sprintf('getCompanyId unknow result:%s', json_encode($result)));
        } catch(Exception $message) {
            Yii::error(sprintf('[getCompanyId] Exception, business_id:%s, business_name:%s, reason:%s',
                $this->business_id, $this->business_name, $message->getMessage()));
            return false;
        }
    }


    /**
     *  Select ThAgencyBusiness One
     *  @params int $id
     *  @return
     */
    private function findOne($id)
    {
        return ThAgencyBusiness::findOne($id);
    }
}