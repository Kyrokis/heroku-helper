<?php

$db =  [
	'class' => 'yii\db\Connection',
	'dsn' => 'pgsql:host=127.0.0.1;port=5432;dbname=postgres',
	'username' => 'postgres',
	'password' => '',
	'charset' => 'utf8',
	'enableSchemaCache' => false,
	'enableQueryCache' => true,
];
if (getenv("YII_ENV") == 'prod') {
	$url = parse_url(getenv("DATABASE_URL"));
	$db['dsn'] = "pgsql:host=$url[host];port=$url[port];dbname=" . substr($url["path"], 1);
	$db['username'] = $url["user"];
	$db['password'] = $url["pass"];
}

return $db;