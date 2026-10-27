<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261023204829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Пользователи и их API-ключи';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_api_keys (id INT AUTO_INCREMENT NOT NULL, api_key VARCHAR(56) NOT NULL, user_agent VARCHAR(512) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME NOT NULL, expires_at DATETIME DEFAULT NULL, revoked_at DATETIME DEFAULT NULL, user_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_75F4D61FC912ED9D (api_key), INDEX idx_user_api_keys_user_id (user_id), INDEX idx_user_api_keys_revoked_at (revoked_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id CHAR(36) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, activated TINYINT(1) NOT NULL, panelistId INT NOT NULL, createdAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E9E836ED2C (panelistId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_api_keys ADD CONSTRAINT FK_75F4D61FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_api_keys DROP FOREIGN KEY FK_75F4D61FA76ED395');
        $this->addSql('DROP TABLE user_api_keys');
        $this->addSql('DROP TABLE users');
    }
}
