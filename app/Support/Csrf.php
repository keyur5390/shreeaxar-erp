<?php
namespace App\Support;
final class Csrf
{
    public static function token(): string { return $_SESSION['_token'] ??= bin2hex(random_bytes(32)); }
    public static function verify(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') return;
        if (! hash_equals($_SESSION['_token'] ?? '', $_POST['_token'] ?? '')) {
            http_response_code(419); exit('CSRF token mismatch');
        }
    }
}
