<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThAgencyBusiness;

/**
 * ThAgencyBusinessSearch represents the model behind the search form about `backend\models\record\ThAgencyBusiness`.
 */
class ThAgencyBusinessSearch extends ThAgencyBusiness
{


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['business_id', 'business_name', 'access_token', 'referral'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ThAgencyBusiness::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'status' => ThAgencyBusiness::STATUS_CREATE,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'business_id', $this->business_id])
            ->andFilterWhere(['like', 'business_name', $this->business_name])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'referral', $this->referral]);

        return $dataProvider;
    }


    /**
     *	根据company_id 返回所有business信息
     *	@params	int	companyId
     *	@return
     */
    public static function getAllBusinessByCompanyId($companyId=Null)
    {
        if($companyId) return ThAgencyBusiness::find()->where(['company_id' => $companyId])->all();
        return ThAgencyBusiness::find()->all();
    }


    /**
     *	根据company_id 返回一条business信息
     *	@params	int	companyId
     *	@return
     */
    public static function getOneBusinessByCompanyId($companyId=Null)
    {
        if($companyId) return ThAgencyBusiness::find()->where(['company_id' => $companyId])->one();
        return false;
    }


    /**
     *	根据referral返回business 信息
     *	@params	string	$referral
     *	@return
     */
    public static function getBusinessByReferral($referral=Null)
    {
        if($referral) return ThAgencyBusiness::find()->where(['referral' => $referral])->one();
        return false;
    }


    /**
     *	获取所有business信息
     */
    public static function getAllBusiness()
    {
        return ThAgencyBusiness::find()->all();
    }

    /**
     *  获取代理公司
     */
    public static function getCompanyName()
    {
        $company_name_list = [];
        $businessinfo_list = self::getAllBusiness();
        foreach($businessinfo_list as $businessinfo)
        {
            if($businessinfo->business_name)
                $company_name_list[$businessinfo->company_id] = $businessinfo->business_name;
        }
        return $company_name_list;
    }
}
