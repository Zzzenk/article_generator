<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410150438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_images (id INT AUTO_INCREMENT NOT NULL, article_id_id INT NOT NULL, image_link VARCHAR(255) NOT NULL, INDEX IDX_8AD829EA8F3EC46 (article_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_images ADD CONSTRAINT FK_8AD829EA8F3EC46 FOREIGN KEY (article_id_id) REFERENCES generated_articles (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_images DROP FOREIGN KEY FK_8AD829EA8F3EC46');
        $this->addSql('DROP TABLE article_images');
    }
}
