<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003131513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse (id INT AUTO_INCREMENT NOT NULL, numero INT DEFAULT NULL, rue VARCHAR(255) DEFAULT NULL, complementaire VARCHAR(255) DEFAULT NULL, cp VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, adresse_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(10) DEFAULT NULL, INDEX IDX_C74404554DE7DC5C (adresse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE devis (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, entreprise_id INT NOT NULL, client_id INT NOT NULL, reference VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_debut_prestation DATE DEFAULT NULL, date_validite DATE DEFAULT NULL, total_ht INT NOT NULL, tva INT NOT NULL, total_ttc INT NOT NULL, tc LONGTEXT DEFAULT NULL, INDEX IDX_8B27C52BA76ED395 (user_id), INDEX IDX_8B27C52BA4AEAFEA (entreprise_id), INDEX IDX_8B27C52B19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE element (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, adresse_id INT DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, siret VARCHAR(255) DEFAULT NULL, code_ape VARCHAR(255) DEFAULT NULL, tva_intracom VARCHAR(255) DEFAULT NULL, telephone1 VARCHAR(10) DEFAULT NULL, telephone2 VARCHAR(10) DEFAULT NULL, web VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, contact VARCHAR(255) DEFAULT NULL, INDEX IDX_D19FA604DE7DC5C (adresse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestation (id INT AUTO_INCREMENT NOT NULL, element_id INT NOT NULL, devis_id INT DEFAULT NULL, qty INT NOT NULL, prix_ht INT NOT NULL, tva_percentage INT NOT NULL, tva INT NOT NULL, total_ttc INT NOT NULL, total_ht INT NOT NULL, INDEX IDX_51C88FAD1F1F2A24 (element_id), INDEX IDX_51C88FAD41DEFADA (devis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', password VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', token VARCHAR(255) DEFAULT NULL, registered_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404554DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA604DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD1F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404554DE7DC5C');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BA76ED395');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BA4AEAFEA');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B19EB6921');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA604DE7DC5C');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD1F1F2A24');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD41DEFADA');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE element');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE prestation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
