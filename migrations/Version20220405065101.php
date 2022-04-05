<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220405065101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE histo_sortie');
        $this->addSql('ALTER TABLE participant ADD photo VARCHAR(255) DEFAULT NULL, CHANGE administrateur administrateur TINYINT(1) DEFAULT false, CHANGE actif actif TINYINT(1) DEFAULT true');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE histo_sortie (id INT NOT NULL, etat_sortie VARCHAR(100) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, site VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, description TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, nom VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, date_heure_debut DATETIME NOT NULL, organisateur_nom VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, organisateur_prenom VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, organisateur_email VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('ALTER TABLE participant DROP photo, CHANGE administrateur administrateur TINYINT(1) DEFAULT 0, CHANGE actif actif TINYINT(1) DEFAULT 1');
    }
}
