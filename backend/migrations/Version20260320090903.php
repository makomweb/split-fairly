<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260320090903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create report table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', status VARCHAR(50) NOT NULL, compensation_id LONGTEXT NOT NULL, checksum VARCHAR(64) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', error_message LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_D862F276D17F50A6 (uuid), INDEX idx_status (status), INDEX idx_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE report');
    }
}
