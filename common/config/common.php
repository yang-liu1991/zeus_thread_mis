<?php
/**
 * Author: liuyang@xxx.cn
 * Created Time: 2016-10-14 15:14:56
 */

if(file_exists(__DIR__ . '/user.common.php'))
{
	require_once __DIR__ . '/user.common.php';
}
defined('VENDOR') or define('VENDOR', '/home/zeus/work/thread_vendor', true);
defined('BASE_LOGDIR') or define('BASE_LOGDIR', '/data/zeus/zeus_thread_mis', true);
defined('BACKEND_RUNTIME') or define('BACKEND_RUNTIME', '/data/zeus/zeus_thread_mis/backend/runtime', true);
defined('CONSOLE_RUNTIME') or define('CONSOLE_RUNTIME', '/data/zeus/zeus_thread_mis/console/runtime', true);

# vim: set noexpandtab ts=4 sts=4 sw=4 :
