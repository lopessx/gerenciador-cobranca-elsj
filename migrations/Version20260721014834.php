<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721014834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert roles JSON array to role VARCHAR enum with CHECK constraint';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, name, password, roles, is_active, must_change_pwd FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, must_change_pwd BOOLEAN NOT NULL, role VARCHAR(20) CHECK (role IN (\'ROLE_ADMIN\', \'ROLE_OPERATOR\', \'ROLE_READER\')) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO "user" (id, email, name, password, is_active, must_change_pwd, role) SELECT id, email, name, password, is_active, must_change_pwd, json_extract(roles, \'$[0]\') FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, name, password, role, is_active, must_change_pwd FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, must_change_pwd BOOLEAN NOT NULL, roles CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO "user" (id, email, name, password, is_active, must_change_pwd, roles) SELECT id, email, name, password, is_active, must_change_pwd, json_array(role) FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }
}
