<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2016-08-04 17:41:11
 */

namespace backend\models\user;

use Yii;
use yii\base\Model;


class UserModel extends Model
{
	/**
	 *	For information on the logged in user
	 */
	public static function getLoginInfo()
	{
		return Yii::$app->user->identity;
	}


	/**
	 *	get user info by username
	 */
	public static function getUserInfo($email)
	{
		return User::find()->where('email = :email', [':email' => $email])->one();
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
