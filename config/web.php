<?php

$db = require __DIR__ . '/db.php';

$config = [
	'id' => 'tghelper',
	'name' => 'Telegram helper',
	'basePath' => dirname(__DIR__),
	'language' => 'ru-RU',
	'timeZone' => 'Europe/Moscow',
	'bootstrap' => ['debug', 'log'],
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
	'defaultRoute' => 'helper',
	'components' => [
		'request' => [
			// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
			'cookieValidationKey' => 'elzzkBqIHtyfdf651E70RQsau4KSD0dz',
		],
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'user' => [
			'identityClass' => 'app\modules\user\models\User',
			'enableAutoLogin' => true,
			'loginUrl' => '/user/default/login',
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
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
		'telegram' => [
			'class' => '\app\components\telegram\TelegramBase',
			'botToken' => getenv("telegramBotToken"),
		],
		'db' => $db,
	],
	'modules' => [
		'helper' => 'app\modules\helper\HelperModule',
		'telegram' => 'app\modules\telegram\TelegramModule',
		'template' => 'app\modules\template\TemplateModule',
		'user' => 'app\modules\user\UserModule',

		'gridview' => '\kartik\grid\Module',
		'debug' => [
			'class' => 'yii\debug\Module',
			'allowedIPs' => ['*'],
		],
	],
	'params' => [
		'webhookPage' => getenv("webhookPage"),
		'googleApiKey' => getenv("googleApiKey"),
		'vkApiKey' => getenv("vkApiKey"),
	],
];

return $config;
