<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the customer_call_statistics table
 */
final class Version20250523101815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the customer_call_statistics table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE customer_call_statistics (
            customer_id INT NOT NULL,
            num_calls_within_same_continent INT NOT NULL,
            total_duration_within_same_cont INT NOT NULL,
            total_num_calls INT NOT NULL,
            total_calls_duration INT NOT NULL,
            last_updated DATETIME NOT NULL,
            PRIMARY KEY(customer_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE customer_call_statistics');
    }
}
