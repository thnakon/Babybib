<?php

declare(strict_types=1);

namespace Babybib\Core;

use Babybib\Config\Env;

final class Session
{
    public static function configure(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $lifetime = Env::int('SESSION_LIFETIME', 600);

        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) $lifetime);

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function start(): void
    {
        self::configure();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isAuthenticated(): bool
    {
        self::start();

        return isset($_SESSION['user_id']);
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            Response::redirect(app_url('/login.php'));
        }
    }

    public static function isAdmin(): bool
    {
        self::start();

        return (($_SESSION['role'] ?? '') === 'admin') || !empty($_SESSION['is_admin']);
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (!self::isAdmin()) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}
