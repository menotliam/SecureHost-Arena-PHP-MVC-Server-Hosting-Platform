<?php
if (!defined('APP_LOG_DIR')) {
    define('APP_LOG_DIR', APPROOT . '/../storage/logs');
}

function app_log(string $level, string $event, array $context = []): void {
    if (!is_dir(APP_LOG_DIR)) {
        @mkdir(APP_LOG_DIR, 0755, true);
    }
    $entry = [
        'time'    => date('Y-m-d H:i:s'),
        'level'   => strtoupper($level),
        'event'   => $event,
        'user_id' => $_SESSION['user_id'] ?? null,
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'cli',
        'context' => $context,
    ];
    @file_put_contents(
        APP_LOG_DIR . '/app.log',
        json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}