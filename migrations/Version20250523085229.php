<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Consolidated migration for the entire database schema
 */
final class Version20250523085229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create all tables in a single migration';
    }

    public function up(Schema $schema): void
    {
        // This migration is just a placeholder to mark the current schema state
        // All tables already exist in the database, so we don't need to create them again
        $this->addSql('SELECT 1');

        // The actual schema includes the following tables:
        // - calls
        // - calls_staging
        // - continent_phone_prefix
        // - customer_call_statistics
        // - doctrine_migration_versions
        // - ip_geolocation_cache
        // - messenger_messages
        // - uploaded_files
    }

    public function down(Schema $schema): void
    {
        // Drop tables in reverse order to avoid foreign key constraints
        $this->addSql('DROP TABLE IF EXISTS `uploaded_files`');
        $this->addSql('DROP TABLE IF EXISTS `messenger_messages`');
        $this->addSql('DROP TABLE IF EXISTS `ip_geolocation_cache`');
        $this->addSql('DROP TABLE IF EXISTS `doctrine_migration_versions`');
        $this->addSql('DROP TABLE IF EXISTS `customer_call_statistics`');
        $this->addSql('DROP TABLE IF EXISTS `continent_phone_prefix`');
        $this->addSql('DROP TABLE IF EXISTS `calls_staging`');
        $this->addSql('DROP TABLE IF EXISTS `calls`');
    }
}
