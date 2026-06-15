<?php
namespace App\Repositories;

use App\Core\Database;

final class TableRepository
{
    public function __construct(private string $table) {}
    public function paginate(string $search = '', array $columns = ['name'], int $limit = 25): array
    {
        $db = Database::connect();
        $where = ' WHERE deleted_at IS NULL';
        $params = [];
        if ($search !== '') {
            $likes = array_map(fn($c) => "$c LIKE ?", $columns);
            $where .= ' AND ('.implode(' OR ', $likes).')';
            $params = array_fill(0, count($columns), '%'.$search.'%');
        }
        $stmt = $db->prepare("SELECT * FROM {$this->table}{$where} ORDER BY id DESC LIMIT {$limit}");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function allActive(string $order = 'name'): array { return Database::connect()->query("SELECT * FROM {$this->table} WHERE status = 1 ORDER BY {$order}")->fetchAll(); }
    public function find(int $id): ?array { $stmt = Database::connect()->prepare("SELECT * FROM {$this->table} WHERE id=? LIMIT 1"); $stmt->execute([$id]); return $stmt->fetch() ?: null; }
    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s'); $data['updated_at'] = date('Y-m-d H:i:s');
        $cols = array_keys($data); $sql = "INSERT INTO {$this->table} (".implode(',', $cols).') VALUES ('.implode(',', array_fill(0,count($cols),'?')).')';
        Database::connect()->prepare($sql)->execute(array_values($data)); return (int)Database::connect()->lastInsertId();
    }
    public function update(int $id, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s'); $sets = implode(',', array_map(fn($c)=>"{$c}=?", array_keys($data))); $values = array_values($data); $values[]=$id;
        Database::connect()->prepare("UPDATE {$this->table} SET {$sets} WHERE id=?")->execute($values);
    }
    public function softDelete(int $id): void { Database::connect()->prepare("UPDATE {$this->table} SET deleted_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]); }
}
