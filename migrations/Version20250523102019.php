<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the doctrine_migration_versions table
 */
final class Version20250523102019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the doctrine_migration_versions table for tracking migrations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS doctrine_migration_versions (
            version VARCHAR(191) NOT NULL,
            executed_at DATETIME DEFAULT NULL,
            execution_time INT DEFAULT NULL,
            PRIMARY KEY(version)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE doctrine_migration_versions');
    }
}
