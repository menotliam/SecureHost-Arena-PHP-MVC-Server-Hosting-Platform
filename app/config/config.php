<?php

require_once __DIR__ . '/load_env.php';

$rootDir = dirname(__DIR__, 2);
loadEnv($rootDir . DIRECTORY_SEPARATOR . '.env');

// DB Params
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_PORT', (int) env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'securehost_arena'));

// App Root
define('APPROOT', dirname(dirname(__FILE__)));
// URL Root (no trailing slash)
define('URLROOT', rtrim(env('URLROOT', 'http://localhost/SecureHost-Arena-PHP-MVC-Server-Hosting-Platform'), '/'));
// Site Name
define('SITENAME', env('SITENAME', 'SecureHost Arena'));
