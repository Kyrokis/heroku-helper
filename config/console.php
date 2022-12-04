<?php

$db = require __DIR__ . '/db.php';

$config = [
	'id' => 'Helper-console',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log', 'queue'],
	'language' => 'ru-RU',
	'timeZone' => 'Europe/Moscow',
	'controllerNamespace' => 'app\commands',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
		'@tests' => '@app/tests',
	],
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'log' => [
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
			]
		],
		'db' => $db,
		'queue' => [
			'class' => \yii\queue\db\Queue::class,
            'db' => 'db', // DB connection component or its config 
            'tableName' => '{{%queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => \yii\mutex\PgsqlMutex::class, // Mutex used to sync queries
			'as log' => \yii\queue\LogBehavior::class,
		],
		'telegram' => [
			'class' => '\app\components\telegram\TelegramBase',
			'botToken' => '',
		],
	],
	'params' => [
		'webhookPage' => '',
		'googleApiKey' => '',
		'vkApiKey' => '',
	],
	'modules' => [
		'gii' => [
			'class' => 'yii\gii\Module',
			'allowedIPs' => ['*'],
		],
		'debug' => [
			'class' => 'yii\debug\Module',
			'panels' => [
				'queue' => \yii\queue\debug\Panel::class,
			],
			'allowedIPs' => ['*'],
		],
	],
	/*
	'controllerMap' => [
		'fixture' => [ // Fixture generation command line.
			'class' => 'yii\faker\FixtureController',
		],
	],
	*/
];

return $config;
