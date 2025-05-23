<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the calls_staging table
 */
final class Version20250523101714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the calls_staging table for parallel processing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS calls_staging (
            id INT AUTO_INCREMENT NOT NULL,
            customer_id INT NOT NULL,
            call_date DATETIME NOT NULL,
            duration INT NOT NULL,
            dialed_number VARCHAR(32) NOT NULL,
            source_ip VARCHAR(45) NOT NULL,
            source_continent VARCHAR(2) DEFAULT NULL,
            dest_continent VARCHAR(2) DEFAULT NULL,
            within_same_cont TINYINT(1) DEFAULT NULL,
            uploaded_file_id INT DEFAULT NULL,
            batch_id VARCHAR(36) NOT NULL,
            chunk_filename VARCHAR(255) DEFAULT NULL,
            row_number_in_chunk INT DEFAULT NULL,
            is_valid TINYINT(1) DEFAULT 1,
            error_message VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX idx_batch_id (batch_id),
            INDEX idx_customer_id (customer_id),
            INDEX idx_source_ip (source_ip),
            INDEX idx_call_date (call_date)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE calls_staging');
    }
}
