<?php

$db = require __DIR__ . '/db.php';

$config = [
	'id' => 'Helper-console',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
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
		'telegram' => [
			'class' => '\app\components\telegram\TelegramBase',
			'botToken' => getenv("telegramBotToken"),
		],
	],
	'params' => [],
	'modules' => [
		'gii' => [
			'class' => 'yii\gii\Module',
			'allowedIPs' => ['*'],
		],
		'debug' => [
			'class' => 'yii\debug\Module',
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
