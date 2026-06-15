<?php
namespace App\Services;

use App\Core\Database;
use PDO;

final class SchemaService
{
    public function migrate(bool $fresh = false): void
    {
        $db = Database::connect();
        if ($fresh) {
            $tables = ['activity_logs','follow_ups','quotation_attachments','quotation_items','quotations','products','customer_addresses','customers','bank_details','company_settings','cities','states','countries','address_types','units','taxes','currencies','quotation_statuses','permission_role','role_user','permissions','roles','users'];
            foreach ($tables as $table) $db->exec('DROP TABLE IF EXISTS '.$table);
        }
        foreach ($this->statements() as $sql) {
            if ($this->isMysql($db)) {
                $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
            }
            $db->exec($sql);
        }
    }

    public function seed(): void
    {
        $db = Database::connect();
        $permissions = ['dashboard.view','roles.view','roles.create','roles.edit','roles.delete','users.view','users.create','users.edit','users.delete','company-settings.view','company-settings.edit','masters.view','masters.create','masters.edit','masters.delete','masters.export','customers.view','customers.create','customers.edit','customers.delete','customers.export','products.view','products.create','products.edit','products.delete','products.export','quotations.view','quotations.create','quotations.edit','quotations.delete','quotations.export','quotations.approve','reports.view','reports.export','activity-logs.view','activity-logs.export'];
        $stmt = $db->prepare('INSERT OR IGNORE INTO permissions (name, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        if ($this->isMysql($db)) $stmt = $db->prepare('INSERT IGNORE INTO permissions (name, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        foreach ($permissions as $p) $stmt->execute([$p]);
        foreach (['Super Admin','Admin','Sales Manager','Sales Executive','Finance'] as $role) $this->insertIgnore('roles', ['name'=>$role]);
        $this->syncRole('Super Admin', $permissions);
        $this->syncRole('Admin', array_values(array_filter($permissions, fn($p) => ! str_starts_with($p, 'activity-logs.export'))));
        $this->syncRole('Sales Manager', ['dashboard.view','customers.view','customers.create','customers.edit','products.view','quotations.view','quotations.create','quotations.edit','quotations.export','quotations.approve','reports.view','reports.export']);
        $this->syncRole('Sales Executive', ['dashboard.view','customers.view','customers.create','customers.edit','products.view','quotations.view','quotations.create','quotations.edit','reports.view']);
        $this->syncRole('Finance', ['dashboard.view','quotations.view','quotations.export','reports.view','reports.export']);
        if (! $this->exists('users', 'email', 'admin@example.com')) {
            $db->prepare('INSERT INTO users (name,email,password,email_verified_at,is_active,created_at,updated_at) VALUES (?,?,?,?,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)')->execute(['Super Admin','admin@example.com',password_hash('Password@123', PASSWORD_DEFAULT),date('Y-m-d H:i:s')]);
            $this->assignRole((int)$db->lastInsertId(), 'Super Admin');
        }
        foreach ([['India','IND'],['United States','USA']] as $row) $this->insertIgnore('countries', ['name'=>$row[0], 'iso_code'=>$row[1], 'status'=>1]);
        $india = $this->idBy('countries', 'iso_code', 'IND');
        foreach (['Gujarat','Maharashtra','Rajasthan'] as $state) $this->insertIgnore('states', ['country_id'=>$india, 'name'=>$state, 'status'=>1]);
        $gujarat = $this->idBy('states', 'name', 'Gujarat');
        foreach (['Ahmedabad','Surat','Vadodara','Rajkot'] as $city) $this->insertIgnore('cities', ['country_id'=>$india, 'state_id'=>$gujarat, 'name'=>$city, 'status'=>1]);
        foreach (['Billing','Shipping','Office','Factory'] as $name) $this->insertIgnore('address_types', ['name'=>$name, 'status'=>1]);
        foreach ([['Piece','PCS'],['Square Feet','SQFT'],['Running Feet','RFT'],['Set','SET']] as $row) $this->insertIgnore('units', ['name'=>$row[0], 'code'=>$row[1], 'status'=>1]);
        foreach ([['GST 0%',0],['GST 5%',5],['GST 12%',12],['GST 18%',18],['GST 28%',28]] as $row) $this->insertIgnore('taxes', ['name'=>$row[0], 'rate'=>$row[1], 'status'=>1]);
        foreach ([['Indian Rupee','INR','₹',1],['US Dollar','USD','$',83.5]] as $row) $this->insertIgnore('currencies', ['name'=>$row[0], 'code'=>$row[1], 'symbol'=>$row[2], 'exchange_rate'=>$row[3], 'status'=>1]);
        foreach ([['Draft','draft','#6c757d',1,0],['Sent','sent','#0d6efd',2,0],['Approved','approved','#198754',3,1],['Rejected','rejected','#dc3545',4,1]] as $row) $this->insertIgnore('quotation_statuses', ['name'=>$row[0], 'slug'=>$row[1], 'color'=>$row[2], 'sort_order'=>$row[3], 'is_final'=>$row[4], 'status'=>1]);
        if ((int)$db->query('SELECT COUNT(*) FROM company_settings')->fetchColumn() === 0) {
            $db->prepare('INSERT INTO company_settings (company_name,gst_number,pan_number,email,mobile,website,address_line_1,address_line_2,country_id,state_id,city_id,postal_code,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)')->execute(['Shree Axar Furniture','24ABCDE1234F1Z5','ABCDE1234F','info@shreeaxar.local','+91 98765 43210','https://shreeaxar.local','Furniture Market Road','Ahmedabad',$india,$gujarat,$this->idBy('cities','name','Ahmedabad'),'380001']);
        }
    }

    private function statements(): array
    {
        return [
            'CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, employee_code VARCHAR(50) NULL UNIQUE, phone VARCHAR(30) NULL, profile_photo VARCHAR(255) NULL, email_verified_at DATETIME NULL, is_active TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(100) NOT NULL UNIQUE, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL UNIQUE, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS role_user (role_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(role_id,user_id))',
            'CREATE TABLE IF NOT EXISTS permission_role (permission_id INTEGER NOT NULL, role_id INTEGER NOT NULL, PRIMARY KEY(permission_id,role_id))',
            'CREATE TABLE IF NOT EXISTS countries (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL UNIQUE, iso_code VARCHAR(3) NOT NULL UNIQUE, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS states (id INTEGER PRIMARY KEY AUTOINCREMENT, country_id INTEGER NOT NULL, name VARCHAR(120) NOT NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL, UNIQUE(country_id,name))',
            'CREATE TABLE IF NOT EXISTS cities (id INTEGER PRIMARY KEY AUTOINCREMENT, country_id INTEGER NOT NULL, state_id INTEGER NOT NULL, name VARCHAR(120) NOT NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL, UNIQUE(state_id,name))',
            'CREATE TABLE IF NOT EXISTS address_types (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL UNIQUE, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS units (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL, code VARCHAR(20) NOT NULL UNIQUE, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS taxes (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL UNIQUE, rate DECIMAL(5,2) NOT NULL DEFAULT 0, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS currencies (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL, code CHAR(3) NOT NULL UNIQUE, symbol VARCHAR(8) NOT NULL, exchange_rate DECIMAL(15,6) DEFAULT 1, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS quotation_statuses (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL UNIQUE, slug VARCHAR(120) NOT NULL UNIQUE, color VARCHAR(20) NOT NULL, sort_order INTEGER DEFAULT 0, is_final TINYINT DEFAULT 0, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS company_settings (id INTEGER PRIMARY KEY AUTOINCREMENT, company_name VARCHAR(190) NOT NULL, gst_number VARCHAR(40) NULL, pan_number VARCHAR(20) NULL, email VARCHAR(190) NULL, mobile VARCHAR(30) NULL, website VARCHAR(190) NULL, address_line_1 VARCHAR(255) NULL, address_line_2 VARCHAR(255) NULL, country_id INTEGER NULL, state_id INTEGER NULL, city_id INTEGER NULL, postal_code VARCHAR(20) NULL, logo_path VARCHAR(255) NULL, is_active TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS bank_details (id INTEGER PRIMARY KEY AUTOINCREMENT, company_setting_id INTEGER NULL, bank_name VARCHAR(190) NOT NULL, account_name VARCHAR(190) NOT NULL, account_number VARCHAR(80) NOT NULL, ifsc VARCHAR(30) NULL, branch VARCHAR(120) NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS customers (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_code VARCHAR(60) NOT NULL UNIQUE, company_name VARCHAR(190) NOT NULL, contact_person VARCHAR(120) NULL, email VARCHAR(190) NULL, mobile VARCHAR(30) NULL, gst_number VARCHAR(40) NULL, pan_number VARCHAR(20) NULL, country_id INTEGER NULL, state_id INTEGER NULL, city_id INTEGER NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS customer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER NOT NULL, address_type_id INTEGER NOT NULL, address_line_1 VARCHAR(255) NOT NULL, address_line_2 VARCHAR(255) NULL, country_id INTEGER NULL, state_id INTEGER NULL, city_id INTEGER NULL, postal_code VARCHAR(20) NULL, is_default TINYINT DEFAULT 0, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, product_code VARCHAR(60) NOT NULL UNIQUE, product_name VARCHAR(190) NOT NULL, description TEXT NULL, category VARCHAR(120) NULL, unit_id INTEGER NULL, hsn_code VARCHAR(40) NULL, tax_id INTEGER NULL, base_price DECIMAL(15,2) DEFAULT 0, image_path VARCHAR(255) NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS quotations (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_number VARCHAR(80) NOT NULL UNIQUE, customer_id INTEGER NOT NULL, quotation_date DATE NOT NULL, valid_till DATE NULL, sales_person_id INTEGER NULL, currency_id INTEGER NULL, quotation_status_id INTEGER NULL, sub_total DECIMAL(15,2) DEFAULT 0, item_discount_total DECIMAL(15,2) DEFAULT 0, global_discount_type VARCHAR(20) DEFAULT "fixed", global_discount_value DECIMAL(15,2) DEFAULT 0, global_discount_amount DECIMAL(15,2) DEFAULT 0, tax_total DECIMAL(15,2) DEFAULT 0, grand_total DECIMAL(15,2) DEFAULT 0, notes TEXT NULL, terms_conditions TEXT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS quotation_items (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_id INTEGER NOT NULL, product_id INTEGER NULL, product_code_snapshot VARCHAR(80) NULL, description TEXT NOT NULL, quantity DECIMAL(12,3) DEFAULT 1, unit_id INTEGER NULL, rate DECIMAL(15,2) DEFAULT 0, discount_type VARCHAR(20) DEFAULT "fixed", discount_value DECIMAL(15,2) DEFAULT 0, discount_amount DECIMAL(15,2) DEFAULT 0, tax_id INTEGER NULL, tax_rate_snapshot DECIMAL(5,2) DEFAULT 0, tax_amount DECIMAL(15,2) DEFAULT 0, line_total DECIMAL(15,2) DEFAULT 0, sort_order INTEGER DEFAULT 0, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS quotation_attachments (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_id INTEGER NOT NULL, file_name VARCHAR(190) NOT NULL, file_path VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NULL, size INTEGER NULL, created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS follow_ups (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_id INTEGER NOT NULL, assigned_to INTEGER NULL, due_at DATETIME NOT NULL, notes TEXT NULL, status VARCHAR(20) DEFAULT "pending", created_at DATETIME NULL, updated_at DATETIME NULL)',
            'CREATE TABLE IF NOT EXISTS activity_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NULL, event VARCHAR(80) NOT NULL, subject_type VARCHAR(120) NULL, subject_id INTEGER NULL, old_values TEXT NULL, new_values TEXT NULL, ip_address VARCHAR(80) NULL, user_agent VARCHAR(500) NULL, created_at DATETIME NULL)'
        ];
    }

    private function isMysql(PDO $db): bool { return $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'; }
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
        return (int)$stmt->fetchColumn() > 0;
    }
    private function idBy(string $table, string $column, mixed $value): int
    {
        $stmt = Database::connect()->prepare("SELECT id FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        return (int)$stmt->fetchColumn();
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
