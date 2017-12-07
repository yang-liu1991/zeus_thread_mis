<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-03-17 15:33:39
 */

namespace common\struct;

class AccountChangeStatus
{
	/* 帐户更新等待 */
	const	ACCOUNT_CHANGE_WAITING	= 0;
	/* 帐户更新成功 */
	const	ACCOUNT_CHANGE_SUCCESS	= 1;
	/* 帐户更新失败 */
	const	ACCOUNT_CHANGE_FAILED	= 2;

	
	/**
	 *	返回帐户更新状态的button
	 *	@params	int	status
	 *	@return string
	 */
	public static function getAccountChangeStatus($status)
	{
		switch($status)
		{
			case self::ACCOUNT_CHANGE_WAITING:
				return sprintf('<span style="width:60px;" class="btn btn-xs btn-warning">%s</span>', 'Waiting');
				break;
			case self::ACCOUNT_CHANGE_SUCCESS:
				return sprintf('<span style="width:60px;" class="btn btn-xs btn-success">%s</span>', 'Success');
				break;
			case self::ACCOUNT_CHANGE_FAILED:
				return sprintf('<span style="width:60px;" class="btn btn-xs btn-danger">%s</span>', 'Failed');
				break;
		}
	}
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
