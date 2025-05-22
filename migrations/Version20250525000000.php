<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add phones_enriched and ips_enriched columns to uploaded_files table
 */
final class Version20250525000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add phones_enriched and ips_enriched columns to uploaded_files table';
    }

    public function up(Schema $schema): void
    {
        // Add phones_enriched and ips_enriched columns to uploaded_files table
        $this->addSql('ALTER TABLE uploaded_files ADD phones_enriched DATETIME DEFAULT NULL, ADD ips_enriched DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove phones_enriched and ips_enriched columns from uploaded_files table
        $this->addSql('ALTER TABLE uploaded_files DROP phones_enriched, DROP ips_enriched');
    }
}