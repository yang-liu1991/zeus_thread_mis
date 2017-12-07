<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThEntityInfo;

/**
 * ThEntityInfoSearch represents the model behind the search form about `backend\models\record\ThEntityInfo`.
 */
class ThEntityInfoSearch extends ThEntityInfo
{
	
	public $company_id;
	public $search;
	public $entity_ids;

	/**
	 *	定义审核的状态
	 */
	const AUDIT_STATUS_WAIT		= 0;
	const AUDIT_STATUS_SUCCESS	= 1;
	const AUDIT_STATUS_FAILED	= 2;
	const AUDIT_STATUS_ALL		= 10;


	const IS_SMB		= 1;
	const NO_SMB		= 0;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'audit_status', 'created_at', 'updated_at'], 'integer'],
            [['name_en', 'name_zh', 'address_en', 'address_zh', 'promotable_urls', 'official_website_url', 'promotable_page_ids', 'promotable_app_ids', 'vertical', 'subvertical', 'payname', 'contact', 'business_registration', 'audit_message', 'entity_ids'], 'safe'],
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
    public function search($params=null, $audit_status)
    {
		$query = ThEntityInfo::find();
		$query->where(['in', 'audit_status', $audit_status]);
		$query->joinWith('accountInfo');

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 100,
			],
			'sort' => [
				'defaultOrder' => [
					'id' => SORT_DESC,
				],
			],
        ]);
		
		/* 根据entity_ids，检索数据 */
		if($this->entity_ids)
			$query->andFilterWhere(['in', 'th_entity_info.id', $this->entity_ids]);

		if(!$params)
		{
			return $dataProvider;
		}

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'th_entity_info.user_id' => $this->user_id,
            'audit_status' => $this->audit_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
			'th_account_info.company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'name_en', $this->name_en])
            ->andFilterWhere(['like', 'name_zh', $this->name_zh])
            ->andFilterWhere(['like', 'address_en', $this->address_en])
            ->andFilterWhere(['like', 'address_zh', $this->address_zh])
            ->andFilterWhere(['like', 'promotable_urls', $this->promotable_urls])
            ->andFilterWhere(['like', 'official_website_url', $this->official_website_url])
            ->andFilterWhere(['like', 'promotable_page_ids', $this->promotable_page_ids])
            ->andFilterWhere(['like', 'promotable_app_ids', $this->promotable_app_ids])
            ->andFilterWhere(['like', 'vertical', $this->vertical])
            ->andFilterWhere(['like', 'subvertical', $this->subvertical])
            ->andFilterWhere(['like', 'payname', $this->payname])
            ->andFilterWhere(['like', 'contact', $this->contact])
            ->andFilterWhere(['like', 'business_registration', $this->business_registration])
            ->andFilterWhere(['like', 'business_registration_id', $this->business_registration_id])
            ->andFilterWhere(['like', 'audit_message', $this->audit_message]);

        return $dataProvider;
    }


	/**
	 *	返回实体的审核状态
	 */
	public function getAuditStatus()
	{
		return [
			self::AUDIT_STATUS_UNSTEADY	=> '等待提交',
			self::AUDIT_STATUS_WAIT		=> '等待审核',
			self::AUDIT_STATUS_SUCCESS	=> '审核成功',
			self::AUDIT_STATUS_FAILED	=> '审核失败',
		];
	}

}
