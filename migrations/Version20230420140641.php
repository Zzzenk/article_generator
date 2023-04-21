<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230420140641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_content CHANGE body body LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE generated_articles CHANGE template template LONGTEXT NOT NULL, CHANGE keywords keywords VARCHAR(1000) NOT NULL');
        $this->addSql('ALTER TABLE module CHANGE code code LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE unregistered_users CHANGE ip ip VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_content CHANGE body body VARCHAR(10000) NOT NULL');
        $this->addSql('ALTER TABLE generated_articles CHANGE template template VARCHAR(20000) NOT NULL, CHANGE keywords keywords VARCHAR(1000) DEFAULT NULL');
        $this->addSql('ALTER TABLE module CHANGE code code VARCHAR(10000) NOT NULL');
        $this->addSql('ALTER TABLE unregistered_users CHANGE ip ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL');
    }
}
