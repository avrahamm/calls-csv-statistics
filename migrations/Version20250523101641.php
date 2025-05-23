<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the calls table
 */
final class Version20250523101641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the calls table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS calls (
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
            INDEX idx_customer_id (customer_id), 
            INDEX idx_source_ip (source_ip), 
            INDEX idx_call_date (call_date), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE calls');
    }
}
