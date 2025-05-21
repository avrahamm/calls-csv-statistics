<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521144251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove createdAt and updatedAt columns from uploaded_files table';
    }

    public function up(Schema $schema): void
    {
        // Remove createdAt and updatedAt columns from uploaded_files table
        $this->addSql('ALTER TABLE uploaded_files DROP createdAt, DROP updatedAt');
    }

    public function down(Schema $schema): void
    {
        // Add createdAt and updatedAt columns back to uploaded_files table
        $this->addSql('ALTER TABLE uploaded_files ADD createdAt DATETIME NOT NULL, ADD updatedAt DATETIME NOT NULL');
    }
}
