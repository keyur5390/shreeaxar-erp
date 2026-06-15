<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS quotation_items (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_id INTEGER NOT NULL, product_id INTEGER NULL, product_code_snapshot VARCHAR(80) NULL, description TEXT NOT NULL, quantity DECIMAL(12,3) DEFAULT 1, unit_id INTEGER NULL, rate DECIMAL(15,2) DEFAULT 0, discount_type VARCHAR(20) DEFAULT "fixed", discount_value DECIMAL(15,2) DEFAULT 0, discount_amount DECIMAL(15,2) DEFAULT 0, tax_id INTEGER NULL, tax_rate_snapshot DECIMAL(5,2) DEFAULT 0, tax_amount DECIMAL(15,2) DEFAULT 0, line_total DECIMAL(15,2) DEFAULT 0, sort_order INTEGER DEFAULT 0, created_at DATETIME NULL, updated_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS quotation_items');
    }
};
