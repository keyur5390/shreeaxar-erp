<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS customer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER NOT NULL, address_type_id INTEGER NOT NULL, address_line_1 VARCHAR(255) NOT NULL, address_line_2 VARCHAR(255) NULL, country_id INTEGER NULL, state_id INTEGER NULL, city_id INTEGER NULL, postal_code VARCHAR(20) NULL, is_default TINYINT DEFAULT 0, created_at DATETIME NULL, updated_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS customer_addresses');
    }
};
