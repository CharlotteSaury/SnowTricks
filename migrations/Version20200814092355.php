<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200814092355 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reported_trick (id INT AUTO_INCREMENT NOT NULL, trick_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, main_image VARCHAR(255) DEFAULT NULL, INDEX IDX_750B611CB281BE2E (trick_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reported_trick_group (reported_trick_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B28519FE98491C3F (reported_trick_id), INDEX IDX_B28519FEFE54D947 (group_id), PRIMARY KEY(reported_trick_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reported_trick ADD CONSTRAINT FK_750B611CB281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('ALTER TABLE reported_trick_group ADD CONSTRAINT FK_B28519FE98491C3F FOREIGN KEY (reported_trick_id) REFERENCES reported_trick (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reported_trick_group ADD CONSTRAINT FK_B28519FEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD reported_trick_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F98491C3F FOREIGN KEY (reported_trick_id) REFERENCES reported_trick (id)');
        $this->addSql('CREATE INDEX IDX_C53D045F98491C3F ON image (reported_trick_id)');
        $this->addSql('ALTER TABLE video ADD reported_trick_id INT DEFAULT NULL, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C98491C3F FOREIGN KEY (reported_trick_id) REFERENCES reported_trick (id)');
        $this->addSql('CREATE INDEX IDX_7CC7DA2C98491C3F ON video (reported_trick_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F98491C3F');
        $this->addSql('ALTER TABLE reported_trick_group DROP FOREIGN KEY FK_B28519FE98491C3F');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C98491C3F');
        $this->addSql('DROP TABLE reported_trick');
        $this->addSql('DROP TABLE reported_trick_group');
        $this->addSql('DROP INDEX IDX_C53D045F98491C3F ON image');
        $this->addSql('ALTER TABLE image DROP reported_trick_id');
        $this->addSql('DROP INDEX IDX_7CC7DA2C98491C3F ON video');
        $this->addSql('ALTER TABLE video DROP reported_trick_id, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
