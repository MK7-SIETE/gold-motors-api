<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Handle CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('#^https://.*\.vercel\.app$#', $origin) || 
    $origin === 'http://localhost:5173' || 
    $origin === 'http://127.0.0.1:5173') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());