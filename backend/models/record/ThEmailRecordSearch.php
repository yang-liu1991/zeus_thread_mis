<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThEmailRecord;

/**
 * ThEmailRecordSearch represents the model behind the search form about `backend\models\record\ThEmailRecord`.
 */
class ThEmailRecordSearch extends ThEmailRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'tid', 'status', 'created_at', 'updated_at'], 'integer'],
            [['receiver', 'reason', 'status', 'begin_time', 'end_time'], 'safe'],
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
        $query = ThEmailRecord::find();
		$query->joinWith('emailTemplate');

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
            'tid' => $this->tid,
            'th_email_record.status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'receiver', $this->receiver])
            ->andFilterWhere(['like', 'reason', $this->reason]);
		if($this->begin_time && $this->end_time)
			$query->andFilterWhere(['between', 'th_email_record.updated_at', strtotime($this->begin_time), strtotime($this->end_time)]);
		if($this->begin_time && !$this->end_time)
			$query->andFilterWhere(['between', 'th_email_record.updated_at', strtotime($this->begin_time), time()]);

        return $dataProvider;
    }
}
