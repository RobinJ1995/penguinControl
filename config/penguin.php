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
	'server_ip' => env ('SERVER_IP', '127.0.0.1'),

	/*
	 * The domain a new user's default vHost is created under, as
	 * <username>.<domain> with a www. alias and <username>@<domain> as its
	 * ServerAdmin. Leave it empty and no default vHost is created -- staff add one
	 * by hand.
	 *
	 * This was hardcoded to the original deployment's domain, in two controllers
	 * and a view //
	 */
	'default_vhost_domain' => env ('DEFAULT_VHOST_DOMAIN', ''),

	/*
	 * Optional. A path that exists only when home directory storage is mounted, used
	 * by ProblemSolver before it creates a missing document root. Leave it unset and
	 * the user's own home directory answers the same question.
	 *
	 * Worth setting when home directories live on a network filesystem //
	 */
	'storage_check_path' => env ('STORAGE_CHECK_PATH', ''),

	/*
	 * Login shells offered to users. Both the validators and the dropdowns read
	 * this list.
	 *
	 * There used to be three copies that disagreed about whether fish and zsh live
	 * in /bin or /usr/bin, so picking "Fish" on the staff create screen failed that
	 * screen's own validation //
	 */
	'shells' => [
		'/bin/bash' => 'Bash',
		'/usr/bin/fish' => 'Fish',
		'/usr/bin/zsh' => 'Zsh',
		'/usr/bin/tmux' => 'tmux',
		'/bin/false' => 'None'
	],

	/*
	 * Usernames nobody may register. Everything in /etc/passwd is refused as well;
	 * see prohibited_usernames () in app/helpers.php.
	 *
	 * This too existed in three copies that disagreed, one of which reserved names
	 * specific to the original deployment //
	 */
	'reserved_usernames' => [
		'admin', 'administrator', 'root', 'control', 'penguincontrol',
		'ns', 'ns1', 'ns2', 'ns3', 'ns4', 'ns5',
		'db', 'database', 'mail', 'web', 'www', 'ftp', 'shell', 'ssh',
		'git', 'svn', 'srv', 'cloud', 'intern', 'extern'
	],

	/*
	 * Cosmetic extras that are off by default, because a deployment that is not the
	 * original one should not inherit its in-jokes. The holiday theme in particular
	 * used to fire unconditionally every December, snowfall and all -- which is a
	 * northern-hemisphere assumption as much as a branding one //
	 */
	'holiday_theme' => env ('HOLIDAY_THEME', false),
	'easter_eggs' => env ('EASTER_EGGS', false)
];
