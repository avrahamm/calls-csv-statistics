<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the uploaded_files table
 */
final class Version20250523101948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the uploaded_files table for tracking file uploads';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE uploaded_files (
            id INT AUTO_INCREMENT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            uploaded_at DATETIME NOT NULL,
            processed_at DATETIME DEFAULT NULL,
            status VARCHAR(20) DEFAULT \'pending\',
            error_message TEXT DEFAULT NULL,
            phones_enriched DATETIME DEFAULT NULL,
            ips_enriched DATETIME DEFAULT NULL,
            INDEX idx_uploaded_files_status (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE uploaded_files');
    }
}
