<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230415083620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_images DROP FOREIGN KEY FK_8AD829EA8F3EC46');
        $this->addSql('DROP INDEX IDX_8AD829EA8F3EC46 ON article_images');
        $this->addSql('ALTER TABLE article_images CHANGE article_id_id article_id INT NOT NULL');
        $this->addSql('ALTER TABLE article_images ADD CONSTRAINT FK_8AD829EA7294869C FOREIGN KEY (article_id) REFERENCES generated_articles (id)');
        $this->addSql('CREATE INDEX IDX_8AD829EA7294869C ON article_images (article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_images DROP FOREIGN KEY FK_8AD829EA7294869C');
        $this->addSql('DROP INDEX IDX_8AD829EA7294869C ON article_images');
        $this->addSql('ALTER TABLE article_images CHANGE article_id article_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE article_images ADD CONSTRAINT FK_8AD829EA8F3EC46 FOREIGN KEY (article_id_id) REFERENCES generated_articles (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8AD829EA8F3EC46 ON article_images (article_id_id)');
    }
}
