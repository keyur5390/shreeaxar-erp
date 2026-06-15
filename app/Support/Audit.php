<?php
namespace App\Support;

use App\Core\Database;

final class Audit
{
    public static function log(string $event, ?string $subjectType = null, ?int $subjectId = null, array $old = [], array $new = []): void
    {
        try {
            $stmt = Database::connect()->prepare('INSERT INTO activity_logs (user_id,event,subject_type,subject_id,old_values,new_values,ip_address,user_agent,created_at) VALUES (?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)');
            $stmt->execute([Auth::id(), $event, $subjectType, $subjectId, json_encode($old), json_encode($new), $_SERVER['REMOTE_ADDR'] ?? 'cli', substr($_SERVER['HTTP_USER_AGENT'] ?? 'cli',0,500)]);
        } catch (\Throwable) {}
    }
}
