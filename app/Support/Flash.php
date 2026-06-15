<?php
namespace App\Support;
final class Flash
{
    public static function set(string $key, string $message): void { $_SESSION['_flash'][$key] = $message; }
    public static function get(string $key): ?string { $message = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $message; }
}
