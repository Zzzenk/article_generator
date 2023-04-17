<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230408183409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE generated_articles ADD template_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE generated_articles ADD CONSTRAINT FK_FF7C06D14C924D98 FOREIGN KEY (template_id_id) REFERENCES module (id)');
        $this->addSql('CREATE INDEX IDX_FF7C06D14C924D98 ON generated_articles (template_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE generated_articles DROP FOREIGN KEY FK_FF7C06D14C924D98');
        $this->addSql('DROP INDEX IDX_FF7C06D14C924D98 ON generated_articles');
        $this->addSql('ALTER TABLE generated_articles DROP template_id_id');
    }
}
