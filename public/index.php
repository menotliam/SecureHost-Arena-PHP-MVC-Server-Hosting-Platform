<?php
require_once '../app/init.php';
require_once '../app/helpers/log_helper.php';
// Set error handler
set_error_handler(function ($level, $message, $file, $line) {
    app_log('error', 'php_error', ['message' => $message, 'file' => $file, 'line' => $line]);
    return false;
});

// Set exception handler
set_exception_handler(function (Throwable $e) {
    app_log('error', 'uncaught_exception', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);
    http_response_code(500);
    echo 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
});
// Init Core Library
$init = new App;
