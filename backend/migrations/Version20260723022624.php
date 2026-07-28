<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260723022624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orders (order_id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(10, 2) NOT NULL, installments INT NOT NULL, payment_method ENUM(\'paghiper_boleto\'), user_id INT NOT NULL, INDEX IDX_E52FFDEEA76ED395 (user_id), PRIMARY KEY(order_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (user_id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, cpf VARCHAR(14) DEFAULT NULL, role ENUM(\'admin\', \'operator\') NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E93E3E11F0 (cpf), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES users (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEA76ED395');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE users');
    }
}
