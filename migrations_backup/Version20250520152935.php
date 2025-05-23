<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520152935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename table country_phone_prefix to continent_phone_prefix';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE continent_phone_prefix (phone_prefix VARCHAR(16) NOT NULL, continent_code VARCHAR(2) NOT NULL, PRIMARY KEY(phone_prefix)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO continent_phone_prefix (phone_prefix, continent_code)
            SELECT phone_prefix, continent_code FROM country_phone_prefix
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE country_phone_prefix
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE country_phone_prefix (phone_prefix VARCHAR(16) NOT NULL, continent_code VARCHAR(2) NOT NULL, PRIMARY KEY(phone_prefix)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO country_phone_prefix (phone_prefix, continent_code)
            SELECT phone_prefix, continent_code FROM continent_phone_prefix
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE continent_phone_prefix
        SQL);
    }
}
