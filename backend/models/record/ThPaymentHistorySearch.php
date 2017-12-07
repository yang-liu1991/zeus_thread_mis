<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThPaymentHistory;

/**
 * ThPaymentHistorySearch represents the model behind the search form about `backend\models\record\ThPaymentHistory`.
 */
class ThPaymentHistorySearch extends ThPaymentHistory
{
	const ACTION_CREATED	= 0;
	const ACTION_UPDATED	= 1;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'company_id', 'pay_type', 'action_type', 'created_at', 'updated_at'], 'integer'],
            [['account_id', 'pay_name_real'], 'safe'],
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
        $query = ThPaymentHistory::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'pay_type' => $this->pay_type,
            'action_type' => $this->action_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'account_id', $this->account_id])
            ->andFilterWhere(['like', 'pay_name_real', $this->pay_name_real]);

        return $dataProvider;
    }
}
