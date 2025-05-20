<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520151918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON country_phone_prefix
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country_phone_prefix DROP country_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country_phone_prefix ADD PRIMARY KEY (phone_prefix)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON country_phone_prefix
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country_phone_prefix ADD country_code VARCHAR(2) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country_phone_prefix ADD PRIMARY KEY (country_code, phone_prefix)
        SQL);
    }
}
