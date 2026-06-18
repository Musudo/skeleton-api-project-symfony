<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260618065635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1) add nullable so the statement succeeds against existing rows
        $this->addSql('ALTER TABLE users ADD password_hash VARCHAR(255) DEFAULT NULL');
        // 2) backfill old dev users with an UNUSABLE hash — they must reset to log in
        $this->addSql("UPDATE users SET password_hash = '!' WHERE password_hash IS NULL");
        // 3) now enforce the real constraint
        $this->addSql('ALTER TABLE users ALTER password_hash SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP password_hash');
    }
}
