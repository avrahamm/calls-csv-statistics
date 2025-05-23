<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove foreign key constraint from uploaded_file_id column in calls table';
    }

    public function up(Schema $schema): void
    {
        // Remove foreign key constraint from uploaded_file_id column in calls table
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(*)
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'calls'
                AND CONSTRAINT_NAME = 'FK_D0A7D9E7E9F6D3D'
            );
            SET @sql = IF(@constraint_exists > 0, 'ALTER TABLE calls DROP FOREIGN KEY FK_D0A7D9E7E9F6D3D', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Add foreign key constraint back to uploaded_file_id column in calls table
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (
                SELECT COUNT(*)
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'calls'
                AND CONSTRAINT_NAME = 'FK_D0A7D9E7E9F6D3D'
            );
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'calls'
                AND COLUMN_NAME = 'uploaded_file_id'
            );
            SET @sql = IF(@constraint_exists = 0 AND @column_exists > 0, 'ALTER TABLE calls ADD CONSTRAINT FK_D0A7D9E7E9F6D3D FOREIGN KEY (uploaded_file_id) REFERENCES uploaded_files (id)', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }
}
