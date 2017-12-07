<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\struct\FacebookAccountStatus;
use backend\models\record\ThAgencyCreditlimit;

/**
 * ThAgencyCreaditlimitSearch represents the model behind the search form about `backend\models\record\ThAgencyCreaditlimit`.
 */
class ThAgencyCreditlimitSearch extends ThAgencyCreditlimit
{
	
	/**
	 *	定义操作类型：1为增加，2为减少，3为清零
	 */
	const	ACTION_TYPE_ADD		= 1;
	const	ACTION_TYPE_DEL		= 2;
	const	ACTION_TYPE_RESET	= 3;

	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'account_status', 'company_id', 'min_spend_cap', 'spend_cap', 'spend_cap_old', 'amount_spent', 'number', 'action_type', 'created_at', 'updated_at'], 'integer'],
            [['account_id', 'account_name'], 'safe'],
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
        $query = ThAgencyCreditlimit::find();

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
			],
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
            'account_status' => $this->account_status,
            'company_id' => $this->company_id,
            'min_spend_cap' => $this->min_spend_cap,
            'spend_cap' => $this->spend_cap,
            'spend_cap_old' => $this->spend_cap_old,
            'amount_spent' => $this->amount_spent,
            'number' => $this->number,
            'action_type' => $this->action_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'account_id', $this->account_id])
            ->andFilterWhere(['like', 'account_name', $this->account_name]);

        return $dataProvider;
    }
	
	
	/**
	 *	操作类型
	 */
	public static function getActionType()
	{
		return [
			self::ACTION_TYPE_ADD	=> '增加',
			self::ACTION_TYPE_DEL	=> '减少',
			self::ACTION_TYPE_RESET	=> '清零'
		];
	}

	/**
	 *	客户状态
	 */
	public static function getAccountStatus()
	{
		return [
			FacebookAccountStatus::ACTIVE		=> '<div id="button-adaccount-status"><span class="btn btn-xs btn-success">ACTIVE</span></div>',
			FacebookAccountStatus::DISABLED		=> '<div id="button-adaccount-status"><span class="btn btn-xs btn-danger">DISABLED</span></div>',
			FacebookAccountStatus::UNSETTLED	=> '<div id="button-adaccount-status"><span class="btn btn-xs btn-success">UNSETTLED</span></div>'
		];
	}
}
