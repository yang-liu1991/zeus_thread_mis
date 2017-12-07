<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThSpendReport;

/**
 * ThSpendReportSearch represents the model behind the search form about `backend\models\record\ThSpendReport`.
 */
class ThSpendReportSearch extends ThSpendReport
{
	public $account_id;
	public $company_id;
	public $status;
	public $date_start;
	public $date_stop;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'spend', 'created_at', 'updated_at'], 'integer'],
            [['account_id', 'date_start', 'date_stop', 'company_id', 'status'], 'safe'],
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
        $query = ThSpendReport::find();
		$query->joinWith('accountInfo');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'pagination' => [
				'pageSize' => 31,
			],
			'sort' => [
				'defaultOrder' => [
					'date_start' => SORT_DESC,
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
			'spend' => $this->spend,
			'account_id' => $this->account_id,
			'accountInfo.status' => $this->status,
			'accountInfo.company_id' => $this->company_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

		/* 如果开户时间和结束时间都存在，则选取时间区间 */
		if($this->date_start && $this->date_stop)
			$query->andFilterWhere(['between', 'date_start', $this->date_start, $this->date_stop]);
		/* 如果只存在开户时间，则选取从开始时间到现在的时间区间 */
		if($this->date_start && !$this->date_stop)
			$query->andFilterWhere(['between', 'date_start', $this->date_start, date('Y-m-d')]);


        return $dataProvider;
    }
}
