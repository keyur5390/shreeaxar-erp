<?php

namespace App\Services;

use App\Core\Database;
use Database\Seeders\DatabaseSeeder;

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
            $migration = require $file;
            if (! is_object($migration) || ! method_exists($migration, 'up')) {
                throw new \RuntimeException("Migration must return an object with an up method: {$file}");
            }

            $migration->up($db);
        }
    }

    public function seed(): void
    {
        (new DatabaseSeeder())->run();
    }

    /** @return list<string> */
    private function migrationFiles(): array
    {
        $files = glob(dirname(__DIR__, 2).'/database/migrations/*.php') ?: [];
        sort($files);
        return array_values($files);
    }

}
