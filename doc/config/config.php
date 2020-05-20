<?php 
$config = [
	'application' => [
		'debug' => true,
		'charset' => 'UTF-8',
		'timezone' => 'America/Argentina/Buenos_Aires',
		'prefix' => '/',
	    'authHandler' => '',   //Tengo que mejorar esto
	    'CORS' => [],          //Acá hay que definir toda la info para que funcione la api
	],
	
	'database' => [
		'defaultDataSource' => 'myDatabaseSource',
		'dataSources' => [
			'myDatabaseSource' => [
				'adapter' => 'mysql',
				'server' => 'host',
				'port' => port,
				'user' => 'user',
				'password' => 'secret' ,
				'database' => 'myDatabase',
				'charset' => 'utf8'
			],
		],
	],
];

return $config;