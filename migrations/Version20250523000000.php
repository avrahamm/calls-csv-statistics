<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Revert all changes related to uploaded_file_id column in calls table
 */
final class Version20250523000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove uploaded_file_id column from calls table';
    }

    public function up(Schema $schema): void
    {
        // Remove index and column from calls table
        // Note: Foreign key constraint was already removed in Version20250522000000
        $this->addSql('DROP INDEX IDX_D0A7D9E7E9F6D3D ON calls');
        $this->addSql('ALTER TABLE calls DROP uploaded_file_id');
    }

    public function down(Schema $schema): void
    {
        // Add uploaded_file_id column back to calls table
        $this->addSql('ALTER TABLE calls ADD uploaded_file_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_D0A7D9E7E9F6D3D ON calls (uploaded_file_id)');
    }
}