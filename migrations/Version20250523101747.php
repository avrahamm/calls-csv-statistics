<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the continent_phone_prefix table
 */
final class Version20250523101747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the continent_phone_prefix table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE continent_phone_prefix (
            phone_prefix VARCHAR(16) NOT NULL,
            continent_code VARCHAR(2) NOT NULL,
            PRIMARY KEY(phone_prefix)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE continent_phone_prefix');
    }
}
