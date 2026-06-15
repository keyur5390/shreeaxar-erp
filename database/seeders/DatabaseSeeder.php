<?php

namespace Database\Seeders;

use App\Core\Database;
use PDO;

final class DatabaseSeeder
{
    public function run(): void
    {
        $db = Database::connect();
        $permissions = ['dashboard.view','roles.view','roles.create','roles.edit','roles.delete','users.view','users.create','users.edit','users.delete','company-settings.view','company-settings.edit','masters.view','masters.create','masters.edit','masters.delete','masters.export','customers.view','customers.create','customers.edit','customers.delete','customers.export','products.view','products.create','products.edit','products.delete','products.export','quotations.view','quotations.create','quotations.edit','quotations.delete','quotations.export','quotations.approve','reports.view','reports.export','activity-logs.view','activity-logs.export'];
        $stmt = $this->isMysql($db)
            ? $db->prepare('INSERT IGNORE INTO permissions (name, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)')
            : $db->prepare('INSERT OR IGNORE INTO permissions (name, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        foreach ($permissions as $permission) {
            $stmt->execute([$permission]);
        }

        foreach (['Super Admin','Admin','Sales Manager','Sales Executive','Finance'] as $role) {
            $this->insertIgnore('roles', ['name' => $role]);
        }

        $this->syncRole('Super Admin', $permissions);
        $this->syncRole('Admin', array_values(array_filter($permissions, fn ($permission) => ! str_starts_with($permission, 'activity-logs.export'))));
        $this->syncRole('Sales Manager', ['dashboard.view','customers.view','customers.create','customers.edit','products.view','quotations.view','quotations.create','quotations.edit','quotations.export','quotations.approve','reports.view','reports.export']);
        $this->syncRole('Sales Executive', ['dashboard.view','customers.view','customers.create','customers.edit','products.view','quotations.view','quotations.create','quotations.edit','reports.view']);
        $this->syncRole('Finance', ['dashboard.view','quotations.view','quotations.export','reports.view','reports.export']);

        if (! $this->exists('users', 'email', 'admin@example.com')) {
            $db->prepare('INSERT INTO users (name,email,password,email_verified_at,is_active,created_at,updated_at) VALUES (?,?,?,?,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)')
                ->execute(['Super Admin','admin@example.com',password_hash('Password@123', PASSWORD_DEFAULT),date('Y-m-d H:i:s')]);
            $this->assignRole((int) $db->lastInsertId(), 'Super Admin');
        }

        foreach ([['India','IND'],['United States','USA']] as $row) {
            $this->insertIgnore('countries', ['name' => $row[0], 'iso_code' => $row[1], 'status' => 1]);
        }
        $india = $this->idBy('countries', 'iso_code', 'IND');
        foreach (['Gujarat','Maharashtra','Rajasthan'] as $state) {
            $this->insertIgnore('states', ['country_id' => $india, 'name' => $state, 'status' => 1]);
        }
        $gujarat = $this->idBy('states', 'name', 'Gujarat');
        foreach (['Ahmedabad','Surat','Vadodara','Rajkot'] as $city) {
            $this->insertIgnore('cities', ['country_id' => $india, 'state_id' => $gujarat, 'name' => $city, 'status' => 1]);
        }
        foreach (['Billing','Shipping','Office','Factory'] as $name) {
            $this->insertIgnore('address_types', ['name' => $name, 'status' => 1]);
        }
        foreach ([['Piece','PCS'],['Square Feet','SQFT'],['Running Feet','RFT'],['Set','SET']] as $row) {
            $this->insertIgnore('units', ['name' => $row[0], 'code' => $row[1], 'status' => 1]);
        }
        foreach ([['GST 0%',0],['GST 5%',5],['GST 12%',12],['GST 18%',18],['GST 28%',28]] as $row) {
            $this->insertIgnore('taxes', ['name' => $row[0], 'rate' => $row[1], 'status' => 1]);
        }
        foreach ([['Indian Rupee','INR','₹',1],['US Dollar','USD','$',83.5]] as $row) {
            $this->insertIgnore('currencies', ['name' => $row[0], 'code' => $row[1], 'symbol' => $row[2], 'exchange_rate' => $row[3], 'status' => 1]);
        }
        foreach ([['Draft','draft','#6c757d',1,0],['Sent','sent','#0d6efd',2,0],['Approved','approved','#198754',3,1],['Rejected','rejected','#dc3545',4,1]] as $row) {
            $this->insertIgnore('quotation_statuses', ['name' => $row[0], 'slug' => $row[1], 'color' => $row[2], 'sort_order' => $row[3], 'is_final' => $row[4], 'status' => 1]);
        }

        if ((int) $db->query('SELECT COUNT(*) FROM company_settings')->fetchColumn() === 0) {
            $db->prepare('INSERT INTO company_settings (company_name,gst_number,pan_number,email,mobile,website,address_line_1,address_line_2,country_id,state_id,city_id,postal_code,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)')
                ->execute(['Shree Axar Furniture','24ABCDE1234F1Z5','ABCDE1234F','info@shreeaxar.local','+91 98765 43210','https://shreeaxar.local','Furniture Market Road','Ahmedabad',$india,$gujarat,$this->idBy('cities','name','Ahmedabad'),'380001']);
        }
    }

    private function isMysql(PDO $db): bool
    {
        return $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    }

    private function insertIgnore(string $table, array $data): void
    {
        $db = Database::connect();
        $columns = array_keys($data);
        $prefix = $this->isMysql($db) ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
        $sql = $prefix.' INTO '.$table.' ('.implode(',', $columns).',created_at,updated_at) VALUES ('.implode(',', array_fill(0, count($columns), '?')).',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)';
        $db->prepare($sql)->execute(array_values($data));
    }

    private function exists(string $table, string $column, mixed $value): bool
    {
        $stmt = Database::connect()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function idBy(string $table, string $column, mixed $value): int
    {
        $stmt = Database::connect()->prepare("SELECT id FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        return (int) $stmt->fetchColumn();
    }

    private function syncRole(string $role, array $permissions): void
    {
        $db = Database::connect();
        $roleId = $this->idBy('roles', 'name', $role);
        foreach ($permissions as $permission) {
            $permissionId = $this->idBy('permissions', 'name', $permission);
            $prefix = $this->isMysql($db) ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
            $db->prepare($prefix.' INTO permission_role (permission_id, role_id) VALUES (?, ?)')->execute([$permissionId, $roleId]);
        }
    }

    private function assignRole(int $userId, string $role): void
    {
        $db = Database::connect();
        $prefix = $this->isMysql($db) ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
        $db->prepare($prefix.' INTO role_user (role_id, user_id) VALUES (?, ?)')->execute([$this->idBy('roles', 'name', $role), $userId]);
    }
}
