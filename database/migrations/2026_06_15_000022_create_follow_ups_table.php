<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS follow_ups (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_id INTEGER NOT NULL, assigned_to INTEGER NULL, due_at DATETIME NOT NULL, notes TEXT NULL, status VARCHAR(20) DEFAULT "pending", created_at DATETIME NULL, updated_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS follow_ups');
    }
};
