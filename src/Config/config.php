<?php

declare(strict_types=1);

use Babybib\Config\Env;
use Babybib\Core\Security;
use Babybib\Core\Session;

$rootPath = dirname(__DIR__, 2);
$autoloadPath = $rootPath . '/vendor/autoload.php';

if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

Env::load($rootPath . '/.env');

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Bangkok') ?? 'Asia/Bangkok');

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $rootPath);
}

if (!defined('APP_NAME')) {
    define('APP_NAME', Env::get('APP_NAME', 'Babybib') ?? 'Babybib');
}

if (!defined('APP_URL')) {
    define('APP_URL', rtrim(Env::get('APP_URL', 'http://localhost/babybib-v3') ?? 'http://localhost/babybib-v3', '/'));
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', Env::bool('APP_DEBUG', false));
}

Session::configure();
Security::sendHeaders();

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return Security::escape($value);
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return APP_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return app_url('/assets/' . ltrim($path, '/'));
    }
}
