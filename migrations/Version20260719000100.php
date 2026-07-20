<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260719000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criação inicial das tabelas: company, user, company_user, patient, billing, installment';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf($schema->hasTable('company'), 'Migration já foi aplicada.');

        if ($this->connection->getDatabasePlatform() instanceof SQLitePlatform) {
            $this->upSQLite();
        } else {
            $this->upMySQL();
        }
    }

    private function upSQLite(): void
    {
        $this->addSql('CREATE TABLE company (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            razao_social VARCHAR(255) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            is_active SMALLINT NOT NULL,
            min_entry_amount INTEGER NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE TABLE "user" (
            id VARCHAR(36) NOT NULL,
            email VARCHAR(180) NOT NULL,
            name VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            roles CLOB NOT NULL,
            is_active SMALLINT NOT NULL,
            must_change_pwd SMALLINT NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user"(email)');

        $this->addSql('CREATE TABLE company_user (
            user_id VARCHAR(36) NOT NULL,
            company_id VARCHAR(36) NOT NULL,
            PRIMARY KEY(user_id, company_id),
            FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
            FOREIGN KEY (company_id) REFERENCES company(id) ON DELETE CASCADE
        )');
        $this->addSql('CREATE INDEX IDX_COMPANY_USER_USER ON company_user(user_id)');
        $this->addSql('CREATE INDEX IDX_COMPANY_USER_COMPANY ON company_user(company_id)');

        $this->addSql('CREATE TABLE patient (
            id VARCHAR(36) NOT NULL,
            company_id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            cpf VARCHAR(11) NOT NULL,
            status VARCHAR(20) NOT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (company_id) REFERENCES company(id)
        )');
        $this->addSql('CREATE INDEX IDX_PATIENT_COMPANY ON patient(company_id)');

        $this->addSql('CREATE TABLE billing (
            id VARCHAR(36) NOT NULL,
            company_id VARCHAR(36) NOT NULL,
            patient_id VARCHAR(36) NOT NULL,
            created_by_id VARCHAR(36) NOT NULL,
            total_amount INTEGER NOT NULL,
            entry_amount INTEGER NOT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (company_id) REFERENCES company(id),
            FOREIGN KEY (patient_id) REFERENCES patient(id),
            FOREIGN KEY (created_by_id) REFERENCES "user"(id)
        )');
        $this->addSql('CREATE INDEX IDX_BILLING_COMPANY ON billing(company_id)');
        $this->addSql('CREATE INDEX IDX_BILLING_PATIENT ON billing(patient_id)');
        $this->addSql('CREATE INDEX IDX_BILLING_CREATED_BY ON billing(created_by_id)');

        $this->addSql('CREATE TABLE installment (
            id VARCHAR(36) NOT NULL,
            billing_id VARCHAR(36) NOT NULL,
            installment_number INTEGER NOT NULL,
            amount INTEGER NOT NULL,
            due_date DATE NOT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (billing_id) REFERENCES billing(id)
        )');
        $this->addSql('CREATE INDEX IDX_INSTALLMENT_BILLING ON installment(billing_id)');
    }

    private function upMySQL(): void
    {
        $this->addSql('CREATE TABLE company (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            razao_social VARCHAR(255) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            min_entry_amount INT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `user` (
            id VARCHAR(36) NOT NULL,
            email VARCHAR(180) NOT NULL,
            name VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            roles JSON NOT NULL,
            is_active TINYINT(1) NOT NULL,
            must_change_pwd TINYINT(1) NOT NULL,
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE company_user (
            user_id VARCHAR(36) NOT NULL,
            company_id VARCHAR(36) NOT NULL,
            INDEX IDX_COMPANY_USER_USER (user_id),
            INDEX IDX_COMPANY_USER_COMPANY (company_id),
            PRIMARY KEY(user_id, company_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE patient (
            id VARCHAR(36) NOT NULL,
            company_id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            cpf VARCHAR(11) NOT NULL,
            status VARCHAR(20) NOT NULL,
            INDEX IDX_PATIENT_COMPANY (company_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE billing (
            id VARCHAR(36) NOT NULL,
            company_id VARCHAR(36) NOT NULL,
            patient_id VARCHAR(36) NOT NULL,
            created_by_id VARCHAR(36) NOT NULL,
            total_amount INT NOT NULL,
            entry_amount INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_BILLING_COMPANY (company_id),
            INDEX IDX_BILLING_PATIENT (patient_id),
            INDEX IDX_BILLING_CREATED_BY (created_by_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE installment (
            id VARCHAR(36) NOT NULL,
            billing_id VARCHAR(36) NOT NULL,
            installment_number INT NOT NULL,
            amount INT NOT NULL,
            due_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            INDEX IDX_INSTALLMENT_BILLING (billing_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE company_user ADD CONSTRAINT FK_COMPANY_USER_USER FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_user ADD CONSTRAINT FK_COMPANY_USER_COMPANY FOREIGN KEY (company_id) REFERENCES company(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_PATIENT_COMPANY FOREIGN KEY (company_id) REFERENCES company(id)');
        $this->addSql('ALTER TABLE billing ADD CONSTRAINT FK_BILLING_COMPANY FOREIGN KEY (company_id) REFERENCES company(id)');
        $this->addSql('ALTER TABLE billing ADD CONSTRAINT FK_BILLING_PATIENT FOREIGN KEY (patient_id) REFERENCES patient(id)');
        $this->addSql('ALTER TABLE billing ADD CONSTRAINT FK_BILLING_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES `user`(id)');
        $this->addSql('ALTER TABLE installment ADD CONSTRAINT FK_INSTALLMENT_BILLING FOREIGN KEY (billing_id) REFERENCES billing(id)');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('installment');
        $schema->dropTable('billing');
        $schema->dropTable('patient');
        $schema->dropTable('company_user');
        $schema->dropTable('user');
        $schema->dropTable('company');
    }
}