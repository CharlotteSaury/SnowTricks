<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200821165303 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91E518FD904');
        $this->addSql('DROP INDEX IDX_D8F0A91E518FD904 ON trick');
        $this->addSql('ALTER TABLE trick CHANGE reported_tricks_id parentTrick_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91E4DF74943 FOREIGN KEY (parentTrick_id) REFERENCES trick (id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91E4DF74943 ON trick (parentTrick_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91E4DF74943');
        $this->addSql('DROP INDEX IDX_D8F0A91E4DF74943 ON trick');
        $this->addSql('ALTER TABLE trick CHANGE parenttrick_id reported_tricks_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91E518FD904 FOREIGN KEY (reported_tricks_id) REFERENCES trick (id)');
        $this->addSql('CREATE INDEX IDX_D8F0A91E518FD904 ON trick (reported_tricks_id)');
    }
}
