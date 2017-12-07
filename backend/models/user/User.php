<?php

namespace backend\models\user;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


/**
 * This is the model class for table "zeus_user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property integer $company_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
	
	const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    public $realStatus;
    public $rbacRole;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zeus_user';
    }

	/**
     * @inheritdoc
     */
    public function behaviors()
    {
		return [
			[
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'create_time',
				'updatedAtAttribute' => 'update_time',
			],
		];
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email'], 'required'],
            [['role', 'status', 'company_id', 'create_time', 'update_time'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'role' => 'Role',
            'status' => 'Status',
            'company_id' => 'Company ID',
            'create_time' => 'Created Time',
            'update_time' => 'Updated Time',
        ];
    }

	public function afterFind()
	{
		$this->realStatus = $this->status == self::STATUS_ACTIVE ? 'Active' : 'Deleted';

		$auth = Yii::$app->authManager;
		$roles = $auth->getRolesByUser($this->id);
		$this->rbacRole = array_keys($roles);
	}

	public static function getAllRbacRoles()
	{
		$auth = Yii::$app->authManager;
		$roles = $auth->getRoles();

		$tmp = [];
		foreach($roles as $role){
			$tmp[$role->name] = $role->name;
		}
		return $tmp;
	}

	public function saveRole()
	{
		$auth = Yii::$app->authManager;
		$auth->revokeAll($this->id);

		foreach($this->rbacRole as $roleName){
			$role = $auth->getRole($roleName);
			if($role){
				$auth->assign($role, $this->id);
			}
		}
		return true;

	}

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
		$salt = Yii::$app->params['d3_pwd_salt'];
		$saltMd5 = md5($salt);
		if ($saltMd5 && $password) {
			$key = substr($password, 0, 16) . substr($saltMd5, 16, 16);
			$passwordStr = md5($key); 
		} else {
			$passwordStr = '';
		}	
        return Yii::$app->security->validatePassword($passwordStr, $this->password_hash);
	}

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
	{
		$salt = Yii::$app->params['d3_pwd_salt'];
		$saltMd5 = md5($salt);
		if ($saltMd5 && $password) {
			$key = substr($password, 0, 16) . substr($saltMd5, 16, 16);
			$passwordStr = md5($key);
		} else {
			$passwordStr = '';
		}
        $this->password_hash = Yii::$app->security->generatePasswordHash($passwordStr);
	}

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}
