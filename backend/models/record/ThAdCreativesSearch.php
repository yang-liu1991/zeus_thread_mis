<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThAdCreatives;

/**
 * AdCreativesSearch represents the model behind the search form about `backend\models\record\ThAdCreatives`.
 */
class ThAdCreativesSearch extends ThAdCreatives
{
    public $account_id_list;
	public $audit_status;
	public $begin_time;
	public $end_time;

	/**
	 *	定义创意审核状态
	 */
	const CREATIVES_STATUS_WAIT		= 1;
	const CREATIVES_STATUS_SUCCESS	= 2;
	const CREATIVES_STATUS_FAILED	= 3;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['account_id', 'creative_id', 'ad_id', 'ad_name', 'image_url', 'begin_time', 'end_time', 'created_at', 'updated_at', 'audit_status', 'account_id_list'], 'safe'],
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
        $query = ThAdCreatives::find();
		$query->joinWith('accountInfo');
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'pagination'=>[
				'pageSize'=>99,
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
			'audit_status' => $this->audit_status,
			]);

        if($this->account_id_list)
            $query->andFilterWhere(['in', 'account_id', explode(',', $this->account_id_list)]);
        $query->andFilterWhere(['like', 'ad_id', $this->creative_id])
            ->andFilterWhere(['like', 'creative_id', $this->creative_id])
            ->andFilterWhere(['like', 'ad_name', $this->ad_name])
            ->andFilterWhere(['like', 'image_url', $this->image_url])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at]);
        //andFilterWhere(['like', 'account_id', $this->account_id])
		if($this->begin_time && $this->end_time)
			$query->andFilterWhere(['between', 'start_time', strtotime($this->begin_time), strtotime($this->end_time)]);

        return $dataProvider;
    }
}
