<?php

namespace App\Services;

use App\Core\Database;
use Database\Seeders\DatabaseSeeder;
use PDO;

final class SchemaService
{
    /** @var list<string> */
    private array $tables = [
        'activity_logs',
        'follow_ups',
        'quotation_attachments',
        'quotation_items',
        'quotations',
        'products',
        'customer_addresses',
        'customers',
        'bank_details',
        'company_settings',
        'cities',
        'states',
        'countries',
        'address_types',
        'units',
        'taxes',
        'currencies',
        'quotation_statuses',
        'permission_role',
        'role_user',
        'permissions',
        'roles',
        'users',
    ];

    public function migrate(bool $fresh = false): void
    {
        $db = Database::connect();
        if ($fresh) {
            foreach ($this->tables as $table) {
                $db->exec('DROP TABLE IF EXISTS '.$table);
            }
        }

        foreach ($this->migrationFiles() as $file) {
            foreach ($this->statementsFrom($file, $db) as $sql) {
                $db->exec($sql);
            }
        }
    }

    public function seed(): void
    {
        (new DatabaseSeeder())->run();
    }

    /** @return list<string> */
    private function migrationFiles(): array
    {
        $files = glob(dirname(__DIR__, 2).'/database/migrations/*.sql') ?: [];
        sort($files);
        return array_values($files);
    }

    /** @return list<string> */
    private function statementsFrom(string $file, PDO $db): array
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new \RuntimeException("Unable to read migration file: {$file}");
        }

        if ($this->isMysql($db)) {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];
        return array_values(array_filter(array_map(static function (string $statement): string {
            $statement = preg_replace('/^\s*--.*$/m', '', $statement) ?? $statement;
            return trim($statement);
        }, $statements)));
    }

    private function isMysql(PDO $db): bool
    {
        return $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    }
}
