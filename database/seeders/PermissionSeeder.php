<?php

namespace Database\Seeders;

final class PermissionSeeder extends AbstractSeeder
{
    public function run(): void
    {
        $db = $this->db();
        $stmt = $this->isMysql($db)
            ? $db->prepare('INSERT IGNORE INTO permissions (name, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)')
            : $db->prepare('INSERT OR IGNORE INTO permissions (name, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');

        foreach (PermissionList::all() as $permission) {
            $stmt->execute([$permission]);
        }
    }
}
