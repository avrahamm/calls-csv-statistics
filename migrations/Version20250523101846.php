<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for creating the ip_geolocation_cache table
 */
final class Version20250523101846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the ip_geolocation_cache table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS ip_geolocation_cache (
            ip_address VARCHAR(45) NOT NULL,
            continent_code VARCHAR(2) NOT NULL,
            last_checked DATETIME NOT NULL,
            PRIMARY KEY(ip_address)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ip_geolocation_cache');
    }
}
