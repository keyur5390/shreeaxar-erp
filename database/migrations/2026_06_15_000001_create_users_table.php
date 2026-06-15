<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, employee_code VARCHAR(50) NULL UNIQUE, phone VARCHAR(30) NULL, profile_photo VARCHAR(255) NULL, email_verified_at DATETIME NULL, is_active TINYINT DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL, deleted_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS users');
    }
};
