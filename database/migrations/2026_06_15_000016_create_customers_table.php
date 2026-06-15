<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS customers (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_code VARCHAR(60) NOT NULL UNIQUE, company_name VARCHAR(190) NOT NULL, contact_person VARCHAR(120) NULL, email VARCHAR(190) NULL, mobile VARCHAR(30) NULL, gst_number VARCHAR(40) NULL, pan_number VARCHAR(20) NULL, country_id INTEGER NULL, state_id INTEGER NULL, city_id INTEGER NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS customers');
    }
};
