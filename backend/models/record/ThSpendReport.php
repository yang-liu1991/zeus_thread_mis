<?php

namespace backend\models\record;

use Yii;
use yii\behaviors\TimestampBehavior;
use backend\models\record\ThAccountInfo;

/**
 * This is the model class for table "th_spend_report".
 *
 * @property integer $id
 * @property string $account_id
 * @property integer $spend
 * @property string $date_start
 * @property string $date_stop
 * @property integer $created_at
 * @property integer $updated_at
 */
class ThSpendReport extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_spend_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_id', 'spend', 'date_start', 'date_stop'], 'required'],
            [['spend', 'created_at', 'updated_at'], 'integer'],
            [['account_id'], 'string', 'max' => 255],
            [['date_start', 'date_stop'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => 'Account ID',
            'spend' => 'Spend',
            'date_start' => 'Date Start',
            'date_stop' => 'Date Stop',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

	
	/**
	 *
	 */
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'created_at',
				'updatedAtAttribute' => 'updated_at',
			],
		];
	}


	/**
	 *	进行关联查询
	 */
	public function getAccountInfo()
	{
		 return $this->hasOne(ThAccountInfo::className(), ['fbaccount_id' => 'account_id']);	
	}
}
