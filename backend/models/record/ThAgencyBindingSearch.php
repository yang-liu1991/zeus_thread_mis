<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\struct\FacebookAccountStatus;
use backend\models\record\ThAgencyBinding;

/**
 * ThAgencyBindingSearch represents the model behind the search form about `backend\models\record\ThAgencyBinding`.
 */
class ThAgencyBindingSearch extends ThAgencyBinding
{
	/**
	 *	定义操作类型：1为绑定, 2为解绑
	 */
	const ACTION_TYPE_BINDING	= 1;
	const ACTION_TYPE_REMOVING	= 2;
	
	/**
	 *	绑定权限
	 */
	const	ADVERTISER	= 'GENERAL_USER';
	const	ANALYST		= 'REPORTS_ONLY';

	/**
	 *	定义access status
	 */
	const	PENDING		= 1;
	const	CONFIRMED	= 2;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'company_id', 'access_status', 'action_type', 'created_at', 'updated_at'], 'integer'],
            [['account_id', 'account_name', 'business_id', 'business_name', 'access_type', 'permitted_roles'], 'safe'],
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
        $query = ThAgencyBinding::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'pagination' => [
				'pageSize' => 100,
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
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'access_status' => $this->access_status,
            'action_type' => $this->action_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'account_id', $this->account_id])
            ->andFilterWhere(['like', 'account_name', $this->account_name])
            ->andFilterWhere(['like', 'business_id', $this->business_id])
            ->andFilterWhere(['like', 'business_name', $this->business_name])
            ->andFilterWhere(['like', 'access_type', $this->access_type])
            ->andFilterWhere(['like', 'permitted_roles', $this->permitted_roles]);

        return $dataProvider;
    }


	/**
	 *	操作类型
	 */
	public static function getActionType()
	{
		return [
			self::ACTION_TYPE_BINDING	=> '绑定',	
			self::ACTION_TYPE_REMOVING	=> '解绑'
		];
	}

	/**
	 *	权限列表（角色）
	 */
	public static function getPermittedRoles()
	{
		return [
			self::ADVERTISER	=> 'GENERAL_USER',
			self::ANALYST		=> 'REPORTS_ONLY'
		];
	}
}
