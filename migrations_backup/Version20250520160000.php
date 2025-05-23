<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the uploaded_files table
 */
final class Version20250520160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the uploaded_files table for tracking file uploads';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE uploaded_files (
                id INT AUTO_INCREMENT PRIMARY KEY,
                file_name VARCHAR(255) NOT NULL,
                uploaded_at DATETIME NOT NULL,
                processed_at DATETIME DEFAULT NULL,
                status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
                error_message TEXT DEFAULT NULL,
                INDEX idx_uploaded_files_status (status)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE uploaded_files
        SQL);
    }
}