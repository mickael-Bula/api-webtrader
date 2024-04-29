<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240428195827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE cac_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE lvc_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cac (id INT NOT NULL, created_at DATE NOT NULL, opening DOUBLE PRECISION NOT NULL, closing DOUBLE PRECISION NOT NULL, higher DOUBLE PRECISION NOT NULL, lower DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE lvc (id INT NOT NULL, created_at DATE NOT NULL, closing DOUBLE PRECISION NOT NULL, opening DOUBLE PRECISION NOT NULL, higher DOUBLE PRECISION NOT NULL, lower DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cac_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE lvc_id_seq CASCADE');
        $this->addSql('DROP TABLE cac');
        $this->addSql('DROP TABLE lvc');
    }
}
