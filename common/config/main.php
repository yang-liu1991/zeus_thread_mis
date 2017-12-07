<?php
return [
    'vendorPath' => VENDOR,
    'components' => [
		'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=dbm-ad.domob.cn;dbname=zeus',
            'username' => 'zeus',
            'password' => 'dmsuez315',
            'charset' => 'utf8',
		],
		'session' => [
        	'class' => 'yii\web\Session',
            'handler' => [
				'class' => 'common\models\RedisSession',
                'host' => 'redism-zeus.domob.cn',
                'port' => 5901,
                'timeOut' => 0,
                'keyPrefix' => 'zeus_thread_session_',
                'log_file' => '/data/zeus/zeus_thread/logs/session.log',
                'log_level' => 2,
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
		'authManager' => [
			'class' => 'yii\rbac\DbManager',
		],
		'assetManager' => [
			'class' => 'common\models\ThreadAssetManager',
		],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			'viewPath' => '@common/mail',
			'useFileTransport' => false,
			'transport' => [
				'class'		=> 'Swift_SmtpTransport',
				'host'		=> 'mail.bluefocus.com',
				'username'	=> 'facebook-bluevision@bluefocus.com',
				'password'	=> 'BlueVision2017',
				'port'		=> '25',
				'encryption'	=> 'tls',
			],
		],
    ],
	'aliases' => [
		'@mdm/admin' => VENDOR . '/mdmsoft/yii2-admin',
		'@qiniucloud' => VENDOR . '/qiniu/php-sdk',
		'@tmpdir' => BACKEND_RUNTIME . '/tmpdir'
	],
	'modules' => [
		'rbac' =>  [
			'class' => 'johnitvn\rbacplus\Module'
		],
		'gridview' =>  [
			'class' => '\kartik\grid\Module'    //此扩展使用于 kartik-v/yii2-grid ，故在此之前必须使用 gridview module
		],
		'admin' => [  
    	    'class' => 'mdm\admin\Module',  
     	    'layout' => 'left-menu', // it can be '@path/to/your/layout'.  
      		/**/  
        	'controllerMap' => [  
            	'assignment' => [  
                	'class' => 'mdm\admin\controllers\AssignmentController',  
             	    'userClassName' => 'app\models\User',  
                	'idField' => 'id'  
            	]  
        	],  
        	'menus' => [  
            	'assignment' => [  
                	'label' => 'Grand Access' // change label  
            	],  
            	//'route' => null, // disable menu route  
        	]  
    	],  
   		'debug' => [  
        	'class' => 'yii\debug\Module',  
    	],  
	]	
];
