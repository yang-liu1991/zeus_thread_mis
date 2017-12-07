<?php
namespace backend\models\user;

use Yii;
use yii\base\Model;
use backend\models\user\User;
use backend\models\record\ThAgencyBusinessSearch;

/**
 * Signup form
 */
class AddUserForm extends Model
{
    public $username;
    public $email;
    public $role;
    public $password;
    public $repassword;
    public $scenario = 'add';
    public $status = User::STATUS_ACTIVE;
	public $company_id = 0;

    public $rbacRole;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
			['company_id', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\backend\models\user\User', 'message' => 'This email has already been taken.'],
            ['email', 'string', 'min' => 2, 'max' => 255],
            ['status', 'required'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['repassword', 'required'],
            ['repassword', 'compare', 'compareAttribute' => 'password', 'message' => 'Repassword do not match the password.'],
            [['rbacRole', 'company_id'], 'safe'],
        ];
    }

	/**
	 *	attributes
	 */
	public function attributeLabels()
	{
		return [
			'email'			=> '邮箱',
			'username'		=> '用户名',
			'password'		=> '密码',
			'repassword'	=> '重复密码',
			'rbacRole'		=> '用户角色',
			'status'		=> '状态',
			'company_id'	=> '所属公司',
		];
	}

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
			/* username先用email了，预留字段 */
			$user->username	= $this->email;
            $user->email = $this->email;
            $user->status = $this->status;
			$user->company_id = $this->company_id;
            $user->role = 0;
            $user->setPassword($this->password);
            $user->generateAuthKey();
		    $user->rbacRole = is_array($this->rbacRole) ? $this->rbacRole : [];

            if ($user->save() && $user->saveRole($this->rbacRole)) {
                return $user;
            }
        }

        return null;
    }


    /**
     *  获取代理公司
     */
    public static function getCompanyName()
    {
		return ThAgencyBusinessSearch::getCompanyName();
    }
}
