<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove foreign key constraint from uploaded_file_id column in calls table';
    }

    public function up(Schema $schema): void
    {
        // Remove foreign key constraint from uploaded_file_id column in calls table
        $this->addSql('ALTER TABLE calls DROP FOREIGN KEY FK_D0A7D9E7E9F6D3D');
    }

    public function down(Schema $schema): void
    {
        // Add foreign key constraint back to uploaded_file_id column in calls table
        $this->addSql('ALTER TABLE calls ADD CONSTRAINT FK_D0A7D9E7E9F6D3D FOREIGN KEY (uploaded_file_id) REFERENCES uploaded_files (id)');
    }
}