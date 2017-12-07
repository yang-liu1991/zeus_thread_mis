<?php

namespace backend\models\record;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\record\ThEntityInfo;
use backend\models\record\ThAccountInfo;

/**
 * ThAccountInfoSearch represents the model behind the search form about `backend\models\record\ThAccountInfo`.
 */
class ThAccountInfoSearch extends ThAccountInfo
{

	const CPA_AGENT		= 1;
	const COST_AGENT	= 2;
	const COST_CUSTOMER	= 3;
	const CPA_THIRD		= 4;
	
	public $name_zh;
	public $audit_status;
	public $begin_time;
	public $end_time;

	public $search;
		
	/**
	 *	定义帐户状态
	 */
	/*等待审核开户*/
	const ACCOUNT_STATUS_WAIT				= 'WAIT';
	/*异常帐户*/
	const ACCOUNT_STATUS_ABNORMAL			= 'ABNORMAL';
	/*封停帐户*/
	const ACCOUNT_STATUS_FORCEOUT			= 'FORCEOUT';

	const ACCOUNT_STATUS_PENDING			= 'PENDING';
	const ACCOUNT_STATUS_UNDER_REVIEW		= 'UNDER_REVIEW';
	const ACCOUNT_STATUS_APPROVED			= 'APPROVED';
	const ACCOUNT_STATUS_REQUESTED_CHANGE	= 'REQUESTED_CHANGE';
	const ACCOUNT_STATUS_DISAPPROVED		= 'DISAPPROVED';
	const ACCOUNT_STATUS_CANCELLED			= 'CANCELLED';

	/**
	 *	定义开户类别
	 */
	const PARTITIONED_CREDIT	= 1;
	const DIRECT_CREDIT			= 0;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'company_id', 'entity_id', 'spend_cap', 'amount_spent', 'balance', 'pay_type', 'timezone_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['act_id', 'fbaccount_id', 'company_id', 'fbaccount_name', 'status', 'business_id', 'timezone_id', 'referral', 'spend_cap', 'amount_spent', 'balance', 'pay_name_real', 'audit_status', 'type', 'name_zh', 'begin_time', 'end_time'], 'safe'],
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
        $query = ThAccountInfo::find();
		$query->joinWith('entityInfo');

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
            'th_entity_info.user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'entity_id' => $this->entity_id,
            'timezone_id' => $this->timezone_id,
            'status' => $this->status,
			'type'	=> $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
			'th_entity_info.audit_status' => $this->audit_status,
        ]);

        $query->andFilterWhere(['like', 'act_id', $this->act_id])
            ->andFilterWhere(['like', 'fbaccount_id', $this->fbaccount_id])
            ->andFilterWhere(['like', 'fbaccount_name', $this->fbaccount_name])
            ->andFilterWhere(['like', 'business_id', $this->business_id])
            ->andFilterWhere(['like', 'name_zh', $this->name_zh])
            ->andFilterWhere(['like', 'referral', $this->referral])
            ->andFilterWhere(['like', 'th_entity_info.name_zh', $this->name_zh]);
			/* 如果开户时间和结束时间都存在，则选取时间区间 */
			if($this->begin_time && $this->end_time)
				$query->andFilterWhere(['between', 'th_account_info.updated_at', strtotime($this->begin_time), strtotime($this->end_time)]);
			/* 如果只存在开户时间，则选取从开始时间到现在的时间区间 */
			if($this->begin_time && !$this->end_time)
				$query->andFilterWhere(['between', 'th_account_info.updated_at', strtotime($this->begin_time), time()]);


        return $dataProvider;
    }

	
	/**
	 *	根据实体id，获取account id
	 *	@params	int	entity_id
	 *	@return array account id
	 */
	public static function getAccountidStr($entityId)
	{
		$sql = sprintf('select fbaccount_id from th_account_info where entity_id=%d', $entityId);
		$query = ThAccountInfo::findBySql($sql)->all();
		$fbaccountIdStr = '';
		foreach($query as $obj)
		{
			if(!$obj->fbaccount_id) continue;
			$fbaccountIdStr .= $obj->fbaccount_id.', ';
		}
		$fbaccountIdStr = rtrim(trim($fbaccountIdStr), ',');

		return $fbaccountIdStr;
	}

	/**
	 *	获取代理公司
	 */
	public static function getCompanyName()
	{
	    return ThAgencyBusinessSearch::getCompanyName();
	}


	/**
	 *	获取开户状态
	 */
	public static function getAccountStatus()
	{
		return [
			self::ACCOUNT_STATUS_WAIT				=> 0,
			self::ACCOUNT_STATUS_PENDING			=> 1,
			self::ACCOUNT_STATUS_UNDER_REVIEW		=> 2,
			self::ACCOUNT_STATUS_APPROVED			=> 3,
			self::ACCOUNT_STATUS_REQUESTED_CHANGE	=> 4,
			self::ACCOUNT_STATUS_DISAPPROVED		=> 5,
			self::ACCOUNT_STATUS_CANCELLED			=> 6,
			self::ACCOUNT_STATUS_ABNORMAL			=> 7,
			self::ACCOUNT_STATUS_FORCEOUT			=> 8,
		];
	}
	
	/**
	 *	返回付款方式
	 */
	public static function getPaymentType($payment_type)
	{
		switch($payment_type)
		{
			case self::CPA_AGENT: return 'CPA-代投';break;
			case self::COST_AGENT: return 'Cost-代投';break;
			case self::COST_CUSTOMER: return 'Cost-客户自投';break;
			case self::CPA_THIRD: return 'CPA-外部代投';break;
		}
	}
}
