<?php

return new class
{
    public function up(PDO $db): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS quotation_attachments (id INTEGER PRIMARY KEY AUTOINCREMENT, quotation_id INTEGER NOT NULL, file_name VARCHAR(190) NOT NULL, file_path VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NULL, size INTEGER NULL, created_at DATETIME NULL, updated_at DATETIME NULL);
SQL;

        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $sql = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $sql);
        }

        $db->exec($sql);
    }

    public function down(PDO $db): void
    {
        $db->exec('DROP TABLE IF EXISTS quotation_attachments');
    }
};
