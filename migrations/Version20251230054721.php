<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230054721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation_list ADD exemption JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD debut_exemption DATE DEFAULT NULL, ADD fin_exemption DATE DEFAULT NULL, ADD adrresse JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD patc INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_de_consultation ADD libute VARCHAR(35) DEFAULT NULL, ADD exemption JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD debut_exemption DATE DEFAULT NULL, ADD fin_exemption DATE DEFAULT NULL, ADD adrresse JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD patc INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personnel ADD libute VARCHAR(35) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD libute VARCHAR(35) DEFAULT NULL, ADD local VARCHAR(35) DEFAULT NULL, ADD codute VARCHAR(6) DEFAULT NULL, CHANGE title title VARCHAR(128) DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE matricule matricule VARCHAR(16) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation_list DROP exemption, DROP debut_exemption, DROP fin_exemption, DROP adrresse, DROP patc');
        $this->addSql('ALTER TABLE demande_de_consultation DROP libute, DROP exemption, DROP debut_exemption, DROP fin_exemption, DROP adrresse, DROP patc');
        $this->addSql('ALTER TABLE personnel DROP libute');
        $this->addSql('ALTER TABLE user DROP libute, DROP local, DROP codute, CHANGE title title VARCHAR(128) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE matricule matricule VARCHAR(16) NOT NULL');
    }
}
