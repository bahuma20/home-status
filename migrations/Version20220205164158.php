<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220205164158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__alert AS SELECT id, title, body, icon, created, priority, url, expires FROM alert');
        $this->addSql('DROP TABLE alert');
        $this->addSql('CREATE TABLE alert (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, body CLOB DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL, priority INTEGER NOT NULL, url VARCHAR(255) DEFAULT NULL, expires DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO alert (id, title, body, icon, created, priority, url, expires) SELECT id, title, body, icon, created, priority, url, expires FROM __temp__alert');
        $this->addSql('DROP TABLE __temp__alert');
        $this->addSql('DROP INDEX IDX_B6C6A7F5BE04EA9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cron_report AS SELECT id, job_id, run_at, run_time, exit_code, output, error FROM cron_report');
        $this->addSql('DROP TABLE cron_report');
        $this->addSql('CREATE TABLE cron_report (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, job_id INTEGER DEFAULT NULL, run_at DATETIME NOT NULL, run_time DOUBLE PRECISION NOT NULL, exit_code INTEGER NOT NULL, output CLOB NOT NULL, error CLOB NOT NULL, CONSTRAINT FK_B6C6A7F5BE04EA9 FOREIGN KEY (job_id) REFERENCES cron_job (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cron_report (id, job_id, run_at, run_time, exit_code, output, error) SELECT id, job_id, run_at, run_time, exit_code, output, error FROM __temp__cron_report');
        $this->addSql('DROP TABLE __temp__cron_report');
        $this->addSql('CREATE INDEX IDX_B6C6A7F5BE04EA9 ON cron_report (job_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__alert AS SELECT id, title, body, icon, created, priority, url, expires FROM alert');
        $this->addSql('DROP TABLE alert');
        $this->addSql('CREATE TABLE alert (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, body CLOB DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL, priority INTEGER NOT NULL, url VARCHAR(255) DEFAULT NULL, expires DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO alert (id, title, body, icon, created, priority, url, expires) SELECT id, title, body, icon, created, priority, url, expires FROM __temp__alert');
        $this->addSql('DROP TABLE __temp__alert');
        $this->addSql('DROP INDEX IDX_B6C6A7F5BE04EA9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cron_report AS SELECT id, job_id, run_at, run_time, exit_code, output, error FROM cron_report');
        $this->addSql('DROP TABLE cron_report');
        $this->addSql('CREATE TABLE cron_report (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, job_id INTEGER DEFAULT NULL, run_at DATETIME NOT NULL, run_time DOUBLE PRECISION NOT NULL, exit_code INTEGER NOT NULL, output CLOB NOT NULL, error CLOB NOT NULL)');
        $this->addSql('INSERT INTO cron_report (id, job_id, run_at, run_time, exit_code, output, error) SELECT id, job_id, run_at, run_time, exit_code, output, error FROM __temp__cron_report');
        $this->addSql('DROP TABLE __temp__cron_report');
        $this->addSql('CREATE INDEX IDX_B6C6A7F5BE04EA9 ON cron_report (job_id)');
    }
}
