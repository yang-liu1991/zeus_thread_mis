<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
	'defaultRoute' => 'site',
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
	'vendorPath' => VENDOR,
	'runtimePath' => BACKEND_RUNTIME,
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
		'request' => [
			'enableCookieValidation' => true,
			'cookieValidationKey' => 'MUCS4QLtdFHxkX6PFsOgnWSH-AxEzy5y',
		],
        'user' => [
            'identityClass' => 'backend\models\user\User',
            'enableAutoLogin' => true,
        ],
		'log' => [
			'traceLevel' => YII_DEBUG? 3 : 0,
			'targets' => [
				'email' => [
					'class' =>'yii\log\EmailTarget',
					'levels' => ['error'],
					'message' => [
						'from' => ['facebook-xxx@bluefocus.com'],
						'to' => ['luoli@domob.cn'],
						'subject' => 'Thread Mis Exception Message',
					],  
				],
				'file' => [
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning', 'info'],
					'logFile' => sprintf('%s/backend/runtime/logs/zeus_thread_mis_%s.log', BASE_LOGDIR,  date('YmdH')),
				],
			],
		],
		'urlManager' => [
			'enablePrettyUrl' => true, //对url进行美化 
			'showScriptName' => false,//隐藏index.php   
			'enableStrictParsing'=>FALSE,//不要求网址严格匹配，则不需要输入rules
			'rules' => [
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				//'http://10.0.0.202:58846/thread/' => '<controller>/<action>',
			]//网址匹配规则
		],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
     ],
    'params' => $params,
];
