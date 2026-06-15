<?php

namespace Database\Seeders;

use App\Core\Database;
use PDO;

abstract class AbstractSeeder
{
    protected function db(): PDO
    {
        return Database::connect();
    }

    protected function isMysql(PDO $db): bool
    {
        return $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    }

    protected function insertIgnore(string $table, array $data): void
    {
        $db = $this->db();
        $columns = array_keys($data);
        $prefix = $this->isMysql($db) ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
        $sql = $prefix.' INTO '.$table.' ('.implode(',', $columns).',created_at,updated_at) VALUES ('.implode(',', array_fill(0, count($columns), '?')).',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)';
        $db->prepare($sql)->execute(array_values($data));
    }

    protected function exists(string $table, string $column, mixed $value): bool
    {
        $stmt = $this->db()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return (int) $stmt->fetchColumn() > 0;
    }

    protected function idBy(string $table, string $column, mixed $value): int
    {
        $stmt = $this->db()->prepare("SELECT id FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        return (int) $stmt->fetchColumn();
    }
}
