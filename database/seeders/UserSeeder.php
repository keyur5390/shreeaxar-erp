<?php

namespace Database\Seeders;

final class UserSeeder extends AbstractSeeder
{
    public function run(): void
    {
        $db = $this->db();
        if (! $this->exists('users', 'email', 'admin@example.com')) {
            $db->prepare('INSERT INTO users (name,email,password,email_verified_at,is_active,created_at,updated_at) VALUES (?,?,?,?,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)')
                ->execute(['Super Admin','admin@example.com',password_hash('Password@123', PASSWORD_DEFAULT),date('Y-m-d H:i:s')]);
            $this->assignRole((int) $db->lastInsertId(), 'Super Admin');
        }
    }

    private function assignRole(int $userId, string $role): void
    {
        $db = $this->db();
        $prefix = $this->isMysql($db) ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
        $db->prepare($prefix.' INTO role_user (role_id, user_id) VALUES (?, ?)')->execute([$this->idBy('roles', 'name', $role), $userId]);
    }
}
