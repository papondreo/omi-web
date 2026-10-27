<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261027074611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Скрытые пользователем опросы';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hidden_surveys (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, created_at DATETIME NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_31AD0F12A76ED395 (user_id), UNIQUE INDEX uniq_hidden_surveys_user_survey (user_id, survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE hidden_surveys ADD CONSTRAINT FK_31AD0F12A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hidden_surveys DROP FOREIGN KEY FK_31AD0F12A76ED395');
        $this->addSql('DROP TABLE hidden_surveys');
    }
}
