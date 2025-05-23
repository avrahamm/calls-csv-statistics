<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add phones_enriched and ips_enriched columns to uploaded_files table
 */
final class Version20250525000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add phones_enriched and ips_enriched columns to uploaded_files table';
    }

    public function up(Schema $schema): void
    {
        // Add phones_enriched and ips_enriched columns to uploaded_files table
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'uploaded_files'
                AND COLUMN_NAME = 'phones_enriched'
            );
            SET @sql = IF(@column_exists = 0, 'ALTER TABLE uploaded_files ADD COLUMN phones_enriched DATETIME DEFAULT NULL', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'uploaded_files'
                AND COLUMN_NAME = 'ips_enriched'
            );
            SET @sql = IF(@column_exists = 0, 'ALTER TABLE uploaded_files ADD COLUMN ips_enriched DATETIME DEFAULT NULL', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Remove phones_enriched and ips_enriched columns from uploaded_files table
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'uploaded_files'
                AND COLUMN_NAME = 'phones_enriched'
            );
            SET @sql = IF(@column_exists > 0, 'ALTER TABLE uploaded_files DROP COLUMN phones_enriched', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'uploaded_files'
                AND COLUMN_NAME = 'ips_enriched'
            );
            SET @sql = IF(@column_exists > 0, 'ALTER TABLE uploaded_files DROP COLUMN ips_enriched', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }
}
