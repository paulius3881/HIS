<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200921182400 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE visit (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, worker_id INT NOT NULL, service_id INT DEFAULT NULL, time VARCHAR(255) NOT NULL, INDEX IDX_437EE93919EB6921 (client_id), INDEX IDX_437EE9396B20BA36 (worker_id), INDEX IDX_437EE939ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE visit ADD CONSTRAINT FK_437EE93919EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE visit ADD CONSTRAINT FK_437EE9396B20BA36 FOREIGN KEY (worker_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE visit ADD CONSTRAINT FK_437EE939ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE visit');
    }
}
