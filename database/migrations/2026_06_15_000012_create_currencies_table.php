<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS currencies (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL, code CHAR(3) NOT NULL UNIQUE, symbol VARCHAR(8) NOT NULL, exchange_rate DECIMAL(15,6) DEFAULT 1, status TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS currencies');
    }
};
