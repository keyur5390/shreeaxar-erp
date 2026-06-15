<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, product_code VARCHAR(60) NOT NULL UNIQUE, product_name VARCHAR(190) NOT NULL, description TEXT NULL, category VARCHAR(120) NULL, unit_id INTEGER NULL, hsn_code VARCHAR(40) NULL, tax_id INTEGER NULL, base_price DECIMAL(15,2) DEFAULT 0, image_path VARCHAR(255) NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS products');
    }
};
