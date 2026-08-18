<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260817234137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_installments (id INT AUTO_INCREMENT NOT NULL, installment_number INT NOT NULL, due_date DATE NOT NULL, amount NUMERIC(10, 2) NOT NULL, gateway_transaction_id VARCHAR(100) DEFAULT NULL, bank_slip_url VARCHAR(500) DEFAULT NULL, bank_slip_barcode VARCHAR(255) DEFAULT NULL, gateway_status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, order_id INT NOT NULL, INDEX IDX_FA5092E68D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE order_installments ADD CONSTRAINT FK_FA5092E68D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (order_id)');
        $this->addSql('ALTER TABLE clients RENAME INDEX uniq_cpf TO UNIQ_C82E743E3E11F0');
        $this->addSql('ALTER TABLE orders CHANGE payment_method payment_method VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE orders RENAME INDEX fk_orders_client TO IDX_E52FFDEE19EB6921');
        $this->addSql('ALTER TABLE users CHANGE role role VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_installments DROP FOREIGN KEY FK_FA5092E68D9F6D38');
        $this->addSql('DROP TABLE order_installments');
        $this->addSql('ALTER TABLE orders CHANGE payment_method payment_method ENUM(\'paghiper_boleto\') DEFAULT NULL');
        $this->addSql('ALTER TABLE orders RENAME INDEX idx_e52ffdee19eb6921 TO FK_ORDERS_CLIENT');
        $this->addSql('ALTER TABLE users CHANGE role role ENUM(\'admin\', \'operator\') NOT NULL');
        $this->addSql('ALTER TABLE clients RENAME INDEX uniq_c82e743e3e11f0 TO UNIQ_CPF');
    }
}
