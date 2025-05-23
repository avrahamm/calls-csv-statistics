<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520151918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            SET @constraint_name = (
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'country_phone_prefix'
                AND CONSTRAINT_TYPE = 'PRIMARY KEY'
            );
            SET @sql = IF(@constraint_name IS NOT NULL, 'ALTER TABLE country_phone_prefix DROP PRIMARY KEY', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'country_phone_prefix'
                AND COLUMN_NAME = 'country_code'
            );
            SET @sql = IF(@column_exists > 0, 'ALTER TABLE country_phone_prefix DROP COLUMN country_code', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country_phone_prefix ADD PRIMARY KEY (phone_prefix)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            SET @constraint_name = (
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'country_phone_prefix'
                AND CONSTRAINT_TYPE = 'PRIMARY KEY'
            );
            SET @sql = IF(@constraint_name IS NOT NULL, 'ALTER TABLE country_phone_prefix DROP PRIMARY KEY', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            SET @column_exists = (
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'country_phone_prefix'
                AND COLUMN_NAME = 'country_code'
            );
            SET @sql = IF(@column_exists = 0, 'ALTER TABLE country_phone_prefix ADD COLUMN country_code VARCHAR(2) NOT NULL', 'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country_phone_prefix ADD PRIMARY KEY (country_code, phone_prefix)
        SQL);
    }
}
