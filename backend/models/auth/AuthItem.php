<?php

namespace backend\models\auth;

use Yii;
use yii\rbac\Permission;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 */
class AuthItem extends \yii\db\ActiveRecord
{
	
	public $permissions;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::className(), 'targetAttribute' => ['rule_name' => 'name']],
			[['permissions'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

	/**
	 *	saveRecord
	 */
	public function saveRecord()
	{
		if(!$this->validate()){
			return false;
		}

		if($this->type == Permission::TYPE_PERMISSION){
			if($this->getIsNewRecord()){
				return $this->createPermission();
			}else{
				return $this->updatePermission();
			}
		}elseif($this->type == Permission::TYPE_ROLE){
			if($this->getIsNewRecord()){
				return $this->createRole();
			}else{
				return $this->updateRole();
			}
		}else{
			return false;
		}
	}

	/**
	 *	create permission
	 */
	public function createPermission()
	{
		$auth = Yii::$app->authManager;
		
		$permission = $auth->createPermission($this->name);
		$permission->description = $this->description;
		
		return $auth->add($permission);
	}

	/**
	 * update permission
	 */
	public function updatePermission()
	{
		$auth = Yii::$app->authManager;

		if(!$permission = $auth->getPermission($this->name))
		{
			$this->addError('name', 'This name does not exists.');
			return false;
		}
		$permission->description = $this->description;

		return $auth->update($this->name, $permission);
	}

	/**
	 *	create role
	 */
	public function createRole()
	{
		$auth = Yii::$app->authManager;

		$role = $auth->createRole($this->name);
		$role->description = $this->description;
		
		return $auth->add($role) && $this->savePermissionByRole($role);
	}

	/**
	 *	create Permission by role
	 */
	private function savePermissionByRole($role)
	{
		$auth = Yii::$app->authManager;
		$auth->removeChildren($role);

		!is_array($this->permissions) ? $this->permissions = [] : null;

		foreach($this->permissions as $permissionName){
			$permission = $auth->getPermission($permissionName);
			if($permission){
				$auth->addChild($role, $permission);
			}
		}
		return true;
	}	

	/**
	 *	update role
	 */
	public function updateRole()
	{
		$auth = Yii::$app->authManager;

		if(!$role = $auth->getRole($this->name)){
			$this->addError('name', 'The name does not exist.');
			return false;
		}

		$role->description = $this->description;
		return $auth->update($this->name, $role) && $this->savePermissionByRole($role);
	}

	/**
	 *	getAllPermissions
	 */
	public function getAllPermissions()
	{
		$auth = Yii::$app->authManager;
		$permissions = $auth->getPermissions();

		$tmp = [];
		foreach($permissions as $permission){
			$tmp[$permission->name] = $permission->name;
		}
		return $tmp;
	}


	public function afterFind()
	{
		if($this->type == Permission::TYPE_ROLE){
			$auth = Yii::$app->authManager;
			$permissions = $auth->getPermissionsByRole($this->name);
			$this->permissions = array_keys($permissions);
		}
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::className(), ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren0()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
    }
}
