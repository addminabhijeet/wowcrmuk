<?php

$basePath = realpath(__DIR__ . '/../../../../../../..'); // go up to project root
$publicPath = $basePath; // since index.php is in root now

// Parse URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

// Allow PHP’s built-in server to serve static files directly
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

// Logging (optional)
$formattedDateTime = date('D M j H:i:s Y');
$requestMethod     = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
$remoteAddress     = ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1').':'.
                     ($_SERVER['REMOTE_PORT'] ?? '0');

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

// Load index.php from project root
require_once $publicPath.'/index.php';

