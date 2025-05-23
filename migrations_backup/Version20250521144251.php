<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521144251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove createdAt and updatedAt columns from uploaded_files table';
    }

    public function up(Schema $schema): void
    {
        // Remove createdAt and updatedAt columns from uploaded_files table
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'uploaded_files'
                AND COLUMN_NAME = 'createdAt'
            );
            SET @sql = IF(@column_exists > 0, 'ALTER TABLE uploaded_files DROP COLUMN createdAt', 'SELECT 1');
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
                AND COLUMN_NAME = 'updatedAt'
            );
            SET @sql = IF(@column_exists > 0, 'ALTER TABLE uploaded_files DROP COLUMN updatedAt', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Add createdAt and updatedAt columns back to uploaded_files table
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'uploaded_files'
                AND COLUMN_NAME = 'createdAt'
            );
            SET @sql = IF(@column_exists = 0, 'ALTER TABLE uploaded_files ADD COLUMN createdAt DATETIME NOT NULL', 'SELECT 1');
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
                AND COLUMN_NAME = 'updatedAt'
            );
            SET @sql = IF(@column_exists = 0, 'ALTER TABLE uploaded_files ADD COLUMN updatedAt DATETIME NOT NULL', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }
}
