<?php
namespace App\Support;

use App\Core\Database;

final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (! $user || ! password_verify($password, $user['password'])) return false;
        $_SESSION['user_id'] = (int)$user['id'];
        session_regenerate_id(true);
        Audit::log('login', 'User', (int)$user['id'], [], ['email' => $email]);
        return true;
    }

    public static function logout(): void
    {
        if (self::id()) Audit::log('logout', 'User', self::id());
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
    }

    public static function id(): ?int { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; }

    public static function user(): ?array
    {
        if (! self::id()) return null;
        static $user = null;
        if ($user && (int)$user['id'] === self::id()) return $user;
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([self::id()]);
        return $user = ($stmt->fetch() ?: null);
    }

    public static function requireLogin(): void { if (! self::id()) redirect('/login'); }

    public static function can(string $permission): bool
    {
        if (! self::id()) return false;
        $sql = 'SELECT COUNT(*) FROM users u JOIN role_user ru ON ru.user_id=u.id JOIN roles r ON r.id=ru.role_id JOIN permission_role pr ON pr.role_id=r.id JOIN permissions p ON p.id=pr.permission_id WHERE u.id=? AND (p.name=? OR r.name=?)';
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute([self::id(), $permission, 'Super Admin']);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function requirePermission(string $permission): void
    {
        self::requireLogin();
        if (! self::can($permission)) { http_response_code(403); exit(view('partials.error', ['title'=>'Forbidden', 'message'=>'You do not have permission to access this module.'])); }
    }
}
