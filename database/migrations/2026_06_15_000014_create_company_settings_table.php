<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS company_settings (id INTEGER PRIMARY KEY AUTOINCREMENT, company_name VARCHAR(190) NOT NULL, gst_number VARCHAR(40) NULL, pan_number VARCHAR(20) NULL, email VARCHAR(190) NULL, mobile VARCHAR(30) NULL, website VARCHAR(190) NULL, address_line_1 VARCHAR(255) NULL, address_line_2 VARCHAR(255) NULL, country_id INTEGER NULL, state_id INTEGER NULL, city_id INTEGER NULL, postal_code VARCHAR(20) NULL, logo_path VARCHAR(255) NULL, is_active TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS company_settings');
    }
};
