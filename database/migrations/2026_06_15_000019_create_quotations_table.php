<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS quotations (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_number VARCHAR(80) NOT NULL UNIQUE, customer_id INTEGER NOT NULL, quotation_date DATE NOT NULL, valid_till DATE NULL, sales_person_id INTEGER NULL, currency_id INTEGER NULL, quotation_status_id INTEGER NULL, sub_total DECIMAL(15,2) DEFAULT 0, item_discount_total DECIMAL(15,2) DEFAULT 0, global_discount_type VARCHAR(20) DEFAULT "fixed", global_discount_value DECIMAL(15,2) DEFAULT 0, global_discount_amount DECIMAL(15,2) DEFAULT 0, tax_total DECIMAL(15,2) DEFAULT 0, grand_total DECIMAL(15,2) DEFAULT 0, notes TEXT NULL, terms_conditions TEXT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS quotations');
    }
};
