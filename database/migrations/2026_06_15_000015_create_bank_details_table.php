<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS bank_details (id INTEGER PRIMARY KEY AUTOINCREMENT, company_setting_id INTEGER NULL, bank_name VARCHAR(190) NOT NULL, account_name VARCHAR(190) NOT NULL, account_number VARCHAR(80) NOT NULL, ifsc VARCHAR(30) NULL, branch VARCHAR(120) NULL, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS bank_details');
    }
};
