<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720020933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__billing AS SELECT id, company_id, patient_id, created_by_id, total_amount, entry_amount, status, created_at FROM billing');
        $this->addSql('DROP TABLE billing');
        $this->addSql('CREATE TABLE billing (id VARCHAR(36) NOT NULL, company_id VARCHAR(36) NOT NULL, patient_id VARCHAR(36) NOT NULL, created_by_id VARCHAR(36) NOT NULL, total_amount INTEGER NOT NULL, entry_amount INTEGER NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, pdf_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id), FOREIGN KEY (company_id) REFERENCES company (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (patient_id) REFERENCES patient (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO billing (id, company_id, patient_id, created_by_id, total_amount, entry_amount, status, created_at) SELECT id, company_id, patient_id, created_by_id, total_amount, entry_amount, status, created_at FROM __temp__billing');
        $this->addSql('DROP TABLE __temp__billing');
        $this->addSql('CREATE INDEX IDX_EC224CAA979B1AD6 ON billing (company_id)');
        $this->addSql('CREATE INDEX IDX_EC224CAA6B899279 ON billing (patient_id)');
        $this->addSql('CREATE INDEX IDX_EC224CAAB03A8386 ON billing (created_by_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__company AS SELECT id, name, razao_social, email, is_active FROM company');
        $this->addSql('DROP TABLE company');
        $this->addSql('CREATE TABLE company (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, cnpj VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, is_active BOOLEAN NOT NULL, default_patient_email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO company (id, name, cnpj, email, is_active) SELECT id, name, razao_social, email, is_active FROM __temp__company');
        $this->addSql('DROP TABLE __temp__company');
        $this->addSql('CREATE TEMPORARY TABLE __temp__installment AS SELECT id, billing_id, installment_number, amount, due_date FROM installment');
        $this->addSql('DROP TABLE installment');
        $this->addSql('CREATE TABLE installment (id VARCHAR(36) NOT NULL, billing_id VARCHAR(36) NOT NULL, installment_number INTEGER NOT NULL, amount INTEGER NOT NULL, due_date DATE NOT NULL, PRIMARY KEY(id), FOREIGN KEY (billing_id) REFERENCES billing (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO installment (id, billing_id, installment_number, amount, due_date) SELECT id, billing_id, installment_number, amount, due_date FROM __temp__installment');
        $this->addSql('DROP TABLE __temp__installment');
        $this->addSql('CREATE INDEX IDX_4B778ACD3B025C87 ON installment (billing_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__patient AS SELECT id, company_id, name, email, cpf, status FROM patient');
        $this->addSql('DROP TABLE patient');
        $this->addSql('CREATE TABLE patient (id VARCHAR(36) NOT NULL, company_id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, cpf VARCHAR(14) DEFAULT NULL, status VARCHAR(20) NOT NULL, phone VARCHAR(20) DEFAULT NULL, birthday DATE DEFAULT NULL, no_email BOOLEAN NOT NULL, PRIMARY KEY(id), FOREIGN KEY (company_id) REFERENCES company (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO patient (id, company_id, name, email, cpf, status) SELECT id, company_id, name, email, cpf, status FROM __temp__patient');
        $this->addSql('DROP TABLE __temp__patient');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB979B1AD6 ON patient (company_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, name, password, roles, is_active, must_change_pwd FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, is_active BOOLEAN NOT NULL, must_change_pwd BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO user (id, email, name, password, roles, is_active, must_change_pwd) SELECT id, email, name, password, roles, is_active, must_change_pwd FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__company_user AS SELECT user_id, company_id FROM company_user');
        $this->addSql('DROP TABLE company_user');
        $this->addSql('CREATE TABLE company_user (user_id VARCHAR(36) NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY(user_id, company_id), CONSTRAINT FK_CEFECCA7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CEFECCA7979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO company_user (user_id, company_id) SELECT user_id, company_id FROM __temp__company_user');
        $this->addSql('DROP TABLE __temp__company_user');
        $this->addSql('CREATE INDEX IDX_CEFECCA7A76ED395 ON company_user (user_id)');
        $this->addSql('CREATE INDEX IDX_CEFECCA7979B1AD6 ON company_user (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('CREATE TEMPORARY TABLE __temp__billing AS SELECT id, total_amount, entry_amount, status, created_at, company_id, patient_id, created_by_id FROM billing');
        $this->addSql('DROP TABLE billing');
        $this->addSql('CREATE TABLE billing (id VARCHAR(36) NOT NULL, total_amount INTEGER NOT NULL, entry_amount INTEGER NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, company_id VARCHAR(36) NOT NULL, patient_id VARCHAR(36) NOT NULL, created_by_id VARCHAR(36) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_EC224CAA979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EC224CAA6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EC224CAAB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO billing (id, total_amount, entry_amount, status, created_at, company_id, patient_id, created_by_id) SELECT id, total_amount, entry_amount, status, created_at, company_id, patient_id, created_by_id FROM __temp__billing');
        $this->addSql('DROP TABLE __temp__billing');
        $this->addSql('CREATE INDEX IDX_BILLING_CREATED_BY ON billing (created_by_id)');
        $this->addSql('CREATE INDEX IDX_BILLING_PATIENT ON billing (patient_id)');
        $this->addSql('CREATE INDEX IDX_BILLING_COMPANY ON billing (company_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__company AS SELECT id, name, email, is_active FROM company');
        $this->addSql('DROP TABLE company');
        $this->addSql('CREATE TABLE company (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, is_active SMALLINT NOT NULL, razao_social VARCHAR(255) DEFAULT NULL, min_entry_amount INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO company (id, name, email, is_active) SELECT id, name, email, is_active FROM __temp__company');
        $this->addSql('DROP TABLE __temp__company');
        $this->addSql('CREATE TEMPORARY TABLE __temp__company_user AS SELECT user_id, company_id FROM company_user');
        $this->addSql('DROP TABLE company_user');
        $this->addSql('CREATE TABLE company_user (user_id VARCHAR(36) NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY(user_id, company_id), FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, FOREIGN KEY (company_id) REFERENCES company (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO company_user (user_id, company_id) SELECT user_id, company_id FROM __temp__company_user');
        $this->addSql('DROP TABLE __temp__company_user');
        $this->addSql('CREATE INDEX IDX_COMPANY_USER_COMPANY ON company_user (company_id)');
        $this->addSql('CREATE INDEX IDX_COMPANY_USER_USER ON company_user (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__installment AS SELECT id, installment_number, amount, due_date, billing_id FROM installment');
        $this->addSql('DROP TABLE installment');
        $this->addSql('CREATE TABLE installment (id VARCHAR(36) NOT NULL, installment_number INTEGER NOT NULL, amount INTEGER NOT NULL, due_date DATE NOT NULL, billing_id VARCHAR(36) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_4B778ACD3B025C87 FOREIGN KEY (billing_id) REFERENCES billing (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO installment (id, installment_number, amount, due_date, billing_id) SELECT id, installment_number, amount, due_date, billing_id FROM __temp__installment');
        $this->addSql('DROP TABLE __temp__installment');
        $this->addSql('CREATE INDEX IDX_INSTALLMENT_BILLING ON installment (billing_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__patient AS SELECT id, name, email, cpf, status, company_id FROM patient');
        $this->addSql('DROP TABLE patient');
        $this->addSql('CREATE TABLE patient (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, cpf VARCHAR(11) NOT NULL, status VARCHAR(20) NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_1ADAD7EB979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO patient (id, name, email, cpf, status, company_id) SELECT id, name, email, cpf, status, company_id FROM __temp__patient');
        $this->addSql('DROP TABLE __temp__patient');
        $this->addSql('CREATE INDEX IDX_PATIENT_COMPANY ON patient (company_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, name, password, roles, is_active, must_change_pwd FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, is_active SMALLINT NOT NULL, must_change_pwd SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO "user" (id, email, name, password, roles, is_active, must_change_pwd) SELECT id, email, name, password, roles, is_active, must_change_pwd FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
    }
}
