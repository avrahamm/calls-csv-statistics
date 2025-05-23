<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520133638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE calls (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, call_date DATETIME NOT NULL, duration INT NOT NULL, dialed_number VARCHAR(32) NOT NULL, source_ip VARCHAR(45) NOT NULL, source_continent VARCHAR(2) DEFAULT NULL, dest_continent VARCHAR(2) DEFAULT NULL, within_same_cont TINYINT(1) DEFAULT NULL, INDEX idx_customer_id (customer_id), INDEX idx_source_ip (source_ip), INDEX idx_call_date (call_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE country_phone_prefix (phone_prefix VARCHAR(16) NOT NULL, continent_code VARCHAR(2) NOT NULL, PRIMARY KEY(phone_prefix)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE customer_call_statistics (customer_id INT NOT NULL, num_calls_within_same_continent INT NOT NULL, total_duration_within_same_cont INT NOT NULL, total_num_calls INT NOT NULL, total_calls_duration INT NOT NULL, last_updated DATETIME NOT NULL, PRIMARY KEY(customer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ip_geolocation_cache (ip_address VARCHAR(45) NOT NULL, continent_code VARCHAR(2) NOT NULL, last_checked DATETIME NOT NULL, PRIMARY KEY(ip_address)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE calls
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE country_phone_prefix
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE customer_call_statistics
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ip_geolocation_cache
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
