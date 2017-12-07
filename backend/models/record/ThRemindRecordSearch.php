<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThRemindRecord;

/**
 * ThRemindRecordSearch represents the model behind the search form about `backend\models\record\ThRemindRecord`.
 */
class ThRemindRecordSearch extends ThRemindRecord
{

	/**
	 *	status
	 */
	const	WAITING_BF	= 0;
	const	REMIND_BF	= 1;
	const	WAITING_FB	= 2;
	const	REMIND_FB	= 3;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'account_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['request_id'], 'safe'],
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
        $query = ThRemindRecord::find();

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
            'account_id' => $this->account_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'request_id', $this->request_id]);

        return $dataProvider;
    }
}
