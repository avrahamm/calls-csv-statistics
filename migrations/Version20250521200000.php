<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add uploaded_file_id column to calls table';
    }

    public function up(Schema $schema): void
    {
        // Add uploaded_file_id column to calls table
        $this->addSql('ALTER TABLE calls ADD uploaded_file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE calls ADD CONSTRAINT FK_D0A7D9E7E9F6D3D FOREIGN KEY (uploaded_file_id) REFERENCES uploaded_files (id)');
        $this->addSql('CREATE INDEX IDX_D0A7D9E7E9F6D3D ON calls (uploaded_file_id)');
    }

    public function down(Schema $schema): void
    {
        // Remove uploaded_file_id column from calls table
        $this->addSql('ALTER TABLE calls DROP FOREIGN KEY FK_D0A7D9E7E9F6D3D');
        $this->addSql('DROP INDEX IDX_D0A7D9E7E9F6D3D ON calls');
        $this->addSql('ALTER TABLE calls DROP uploaded_file_id');
    }
}