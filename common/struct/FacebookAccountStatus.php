<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-03-16 16:15:07
 */

namespace common\struct;

class FacebookAccountStatus
{
	const	ACTIVE				= 1;
	const	DISABLED			= 2;
	const	UNSETTLED			= 3;
	const	PENDING_RISK_REVIEW	= 7;
	const	IN_GRACE_PERIOD		= 9;
	const	PENDING_CLOSURE		= 100;
	const	CLOSED				= 101;
	const	PENDING_SETTLEMENT	= 102;
	const	ANY_ACTIVE			= 201;
	const	ANY_CLOSED			= 202;	
}	


# vim: set noexpandtab ts=4 sts=4 sw=4 :
