<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260331095509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE report DROP PRIMARY KEY, MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE report DROP uuid, DROP compensation_id, DROP checksum, DROP id');
        $this->addSql('ALTER TABLE report ADD id VARCHAR(64) NOT NULL PRIMARY KEY');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report ADD uuid BINARY(16) NOT NULL, ADD compensation_id VARCHAR(255) NOT NULL, ADD checksum VARCHAR(64) NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C42F7784D17F50A6 ON report (uuid)');
    }
}
