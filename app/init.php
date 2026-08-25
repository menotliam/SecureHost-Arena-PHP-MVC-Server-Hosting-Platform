<?php
require_once __DIR__ . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        // PHP 7.0–7.2: append SameSite via path hack
        session_set_cookie_params(0, '/; SameSite=Lax', '', $isSecure, true);
    }
    session_start();
}

// Load Helpers
require_once 'helpers/session_helper.php';
require_once 'helpers/log_helper.php';

// Autoload Core Libraries
spl_autoload_register(function($className){
    require_once 'core/' . $className . '.php';
});