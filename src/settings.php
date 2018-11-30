<?php

// so we don't miss any warnings...
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting, so ignore it
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});
date_default_timezone_set('Europe/Tallinn');
setlocale(LC_ALL, 'et_EE.utf-8');
setlocale(LC_NUMERIC, 'C');

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
            'template_cache_path' => __DIR__ . '/../manifest/templates/',
            'auto_reload' => true,
        ],
        'logger' => [
            'name' => '',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../manifest/logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'db' => [
            'host' => '127.0.0.1',
            'name' => 'piletilevi_requests',
            'user' => 'root',
            'pass' => 'test',
        ],
        'redis' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'pass' => 'test',
        ],
        'auth' => [
            // https://github.com/tuupola/slim-basic-auth
            'users' => [
                '' => 'just4us',
            ],
            'secure' => false, // ignore SSL requirement
        ],
        'palette' => [
            'info' => '#3584d9',
            'success' => '#28c46a',
            'danger' => '#ff3f68',
        ]
    ],
];
