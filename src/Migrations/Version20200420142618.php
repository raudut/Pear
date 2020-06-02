<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200420142618 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lender CHANGE iduser iduser_id INT NOT NULL');
        $this->addSql('ALTER TABLE lender ADD CONSTRAINT FK_AD80FA81786A81FB FOREIGN KEY (iduser_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD80FA81786A81FB ON lender (iduser_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lender DROP FOREIGN KEY FK_AD80FA81786A81FB');
        $this->addSql('DROP INDEX UNIQ_AD80FA81786A81FB ON lender');
        $this->addSql('ALTER TABLE lender CHANGE iduser_id iduser INT NOT NULL');
    }
}
