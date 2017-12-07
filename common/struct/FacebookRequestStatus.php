<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-03-16 15:42:35
 */
namespace common\struct;

class FacebookRequestStatus
{
	const	WAITING		= 0;
	const	PENDING		= 1;
	const	UNDERREVIEW	= 2;
	const	APPROVED	= 3;
	const	RE_CHANGE	= 4;
	const	DISAPPROVED	= 5;
	const	CANCELLED	= 6;
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
