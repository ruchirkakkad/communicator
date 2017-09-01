<?php

return [
	'version'     => 'v1',
	'api_url'     => env('COMMUNICATOR_API_URL', 'http://picahooapi.test4you.in/api/v1'),
	'credential' => [
		'email'    => env('COMMUNICATOR_EMAIL', ''),
		'password' => env('COMMUNICATOR_PASSWORD', '')
	],
	'mail'        => [
		'mail_from_name' => env('COMMUNICATOR_MAIL_FROM_NAME', '')
	]
];