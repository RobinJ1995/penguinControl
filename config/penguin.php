<?php

return [
	'user_registration' => env ('USER_REGISTRATION', true),
	'admin_email' => env ('ADMIN_EMAIL', 'root@localhost'),
	'phpmyadmin_url' => env ('PHPMYADMIN_URL', 'http://localhost/phpmyadmin'),
	'vhost' => env ('VHOST', true),
	'ftp' => env ('FTP', true),
	'database' => env ('DATABASE', true),
	'mail' => env ('MAIL', true),
	'mail_user' => env ('MAIL_USER', true),
	'mail_forward' => env ('MAIL_FORWARD', true),
	'website' => env ('WEBSITE', false),
	'server_ip' => env ('SERVER_IP', '127.0.0.1')
];
