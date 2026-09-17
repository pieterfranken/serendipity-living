<?php

// Mirror the layout storage restrictions when using `php artisan serve`.
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$uri = str_replace('\\', '/', $uri);
if (preg_match('#^/storage/app/(uploads/protected|media/layouts)(/|$)#i', $uri)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

if ($uri !== '/' && file_exists(__DIR__.$uri)) {
    return false;
}

require_once __DIR__.'/index.php';
