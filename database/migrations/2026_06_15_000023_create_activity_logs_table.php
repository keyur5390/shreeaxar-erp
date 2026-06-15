<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS activity_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NULL, event VARCHAR(80) NOT NULL, subject_type VARCHAR(120) NULL, subject_id INTEGER NULL, old_values TEXT NULL, new_values TEXT NULL, ip_address VARCHAR(80) NULL, user_agent VARCHAR(500) NULL, created_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS activity_logs');
    }
};
