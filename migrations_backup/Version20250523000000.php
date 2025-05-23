<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Revert all changes related to uploaded_file_id column in calls table
 */
final class Version20250523000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove uploaded_file_id column from calls table';
    }

    public function up(Schema $schema): void
    {
        // Remove index and column from calls table
        // Note: Foreign key constraint was already removed in Version20250522000000
        $this->addSql(<<<'SQL'
            SET @index_exists = (
                SELECT COUNT(*)
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'calls'
                AND INDEX_NAME = 'IDX_D0A7D9E7E9F6D3D'
            );
            SET @sql = IF(@index_exists > 0, 'DROP INDEX IDX_D0A7D9E7E9F6D3D ON calls', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'calls'
                AND COLUMN_NAME = 'uploaded_file_id'
            );
            SET @sql = IF(@column_exists > 0, 'ALTER TABLE calls DROP COLUMN uploaded_file_id', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Add uploaded_file_id column back to calls table
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'calls'
                AND COLUMN_NAME = 'uploaded_file_id'
            );
            SET @sql = IF(@column_exists = 0, 'ALTER TABLE calls ADD COLUMN uploaded_file_id INT DEFAULT NULL', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            SET @index_exists = (
                SELECT COUNT(*)
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'calls'
                AND INDEX_NAME = 'IDX_D0A7D9E7E9F6D3D'
            );
            SET @sql = IF(@index_exists = 0, 'CREATE INDEX IDX_D0A7D9E7E9F6D3D ON calls (uploaded_file_id)', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }
}
