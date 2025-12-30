<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230065851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_de_consultation DROP exemption, DROP debut_exemption, DROP fin_exemption, DROP adrresse, DROP patc');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_de_consultation ADD exemption JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD debut_exemption DATE DEFAULT NULL, ADD fin_exemption DATE DEFAULT NULL, ADD adrresse JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD patc INT DEFAULT NULL');
    }
}
