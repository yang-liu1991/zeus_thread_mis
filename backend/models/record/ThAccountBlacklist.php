<?php

namespace backend\models\record;

use Yii;

/**
 * This is the model class for table "th_account_blacklist".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $account_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class thAccountBlacklist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'th_account_blacklist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'account_id'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['account_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'account_id' => 'Account ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
