<?php
namespace backend\models\user;

use backend\models\user\User;
use yii\base\Model;
use Yii;

/**
 *  form
 */
class ModifyForm extends Model
{
    public $id;
    public $username;
    public $email;
    public $role;
    public $status;
	public $company_id;
    public $password;
    public $repassword;
    public $scenario = 'mod';

    public $rbacRole;

    public $userModel;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'min' => 2, 'max' => 255],
            ['status', 'required'],
			['company_id', 'required'],
            ['role', 'integer'],
            ['password', 'string', 'min' => 6],
            ['repassword', 'compare', 'compareAttribute' => 'password', 'message' => 'Repassword do not match the password.'],
            ['rbacRole', 'safe'],
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
    public function save()
    {
        if ($this->validate()) {
	    if(!empty($this->password)){
		$this->userModel->setPassword($this->password);
		$this->userModel->generateAuthKey();
	    }
	    $this->userModel->status = $this->status;
		$this->userModel->company_id = $this->company_id;
	    $this->userModel->rbacRole = is_array($this->rbacRole) ? $this->rbacRole : [];
            if ($this->userModel->save() && $this->userModel->saveRole()) {
                return $this->userModel;
            }
        }

        return null;
    }

    public function findModel($id)
    {
        if (($this->userModel = User::findOne($id)) !== null) {
	    $this->id = $this->userModel->id;
	    $this->email = $this->userModel->email;
	    $this->status = $this->userModel->status;
		$this->company_id = $this->userModel->company_id;
	    $this->rbacRole = $this->userModel->rbacRole; 
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
