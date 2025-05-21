<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521144005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove createdAt and updatedAt columns from calls table';
    }

    public function up(Schema $schema): void
    {
        // Remove createdAt and updatedAt columns from calls table
        $this->addSql('ALTER TABLE calls DROP createdAt, DROP updatedAt');
    }

    public function down(Schema $schema): void
    {
        // Add createdAt and updatedAt columns back to calls table
        $this->addSql('ALTER TABLE calls ADD createdAt DATETIME NOT NULL, ADD updatedAt DATETIME NOT NULL');
    }
}
