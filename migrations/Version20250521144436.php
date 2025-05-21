<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521144436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove createdAt and updatedAt columns from ip_geolocation_cache table';
    }

    public function up(Schema $schema): void
    {
        // Remove createdAt and updatedAt columns from ip_geolocation_cache table
        $this->addSql('ALTER TABLE ip_geolocation_cache DROP createdAt, DROP updatedAt');
    }

    public function down(Schema $schema): void
    {
        // Add createdAt and updatedAt columns back to ip_geolocation_cache table
        $this->addSql('ALTER TABLE ip_geolocation_cache ADD createdAt DATETIME NOT NULL, ADD updatedAt DATETIME NOT NULL');
    }
}
