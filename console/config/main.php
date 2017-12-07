<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'app-console',
	'name' => 'Facebook开户信息更新通知',
    'basePath' => dirname(__DIR__),
	'runtimePath'	=> CONSOLE_RUNTIME,
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                'email' => [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error', 'warning'],
					'message' => [
						'from' => ['facebook-bluevision@bluefocus.com'],
						'to' => ['liuyang@domob.cn'],
						'subject' => 'Thread Get Account Status Exception Message!',
					]
                ],
				'file' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
					'logFile' => sprintf('%s/console/runtime/logs/console_%s.log', BASE_LOGDIR,  date('YmdH')),
               ]
            ],
        ],
    ],
    'params' => $params,
];
