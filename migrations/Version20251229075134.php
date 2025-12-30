<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229075134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE unite (codute VARCHAR(6) NOT NULL, libute VARCHAR(35) NOT NULL, local VARCHAR(35) NOT NULL, PRIMARY KEY(codute)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD codute VARCHAR(6) DEFAULT NULL, ADD libute VARCHAR(35) DEFAULT NULL, ADD local VARCHAR(35) DEFAULT NULL, CHANGE title title VARCHAR(128) DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE matricule matricule VARCHAR(16) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE unite');
        $this->addSql('ALTER TABLE user DROP codute, DROP libute, DROP local, CHANGE title title VARCHAR(128) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE matricule matricule VARCHAR(16) NOT NULL');
    }
}
