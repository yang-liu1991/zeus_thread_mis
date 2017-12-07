<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-01-11 16:43:48
 */
namespace common\models;

use Yii;
use yii\base\Model;

class AmountConversion extends Model
{
	/**
	 *	进行单位换算，将美分转化为美元
	 *	@params	int	$spend_cap_cent
	 *	@return 
	 */
	public static function centToDollar($cent)
	{
		return sprintf("%.2f", $cent/100);
	}


	/**
	 *	进行单位换算，将美元转化为美分
	 */
	public static function dollarToCent($dollar)
	{
		return sprintf("%d", $dollar*100);
	}
}
# vim: set noexpandtab ts=4 sts=4 sw=4 :
