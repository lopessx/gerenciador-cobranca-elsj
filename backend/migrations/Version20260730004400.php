<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260730004400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create clients table and update orders to reference client instead of user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE clients (
            client_id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            cpf VARCHAR(14) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            address LONGTEXT DEFAULT NULL,
            UNIQUE INDEX UNIQ_CPF (cpf),
            PRIMARY KEY(client_id)
        ) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('ALTER TABLE orders ADD client_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_ORDERS_CLIENT FOREIGN KEY (client_id) REFERENCES clients(client_id)');

        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEA76ED395');

        $this->addSql('ALTER TABLE orders DROP user_id');

        $this->addSql('ALTER TABLE orders CHANGE client_id client_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE orders ADD user_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES users(user_id)');

        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_ORDERS_CLIENT');

        $this->addSql('ALTER TABLE orders DROP client_id');

        $this->addSql('DROP TABLE clients');
    }
}