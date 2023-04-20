<?php

namespace App\Entity;

use App\Repository\ArticleImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleImagesRepository::class)]
#[ORM\Table(name: 'article_images')]
class ArticleImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'image_link', type: 'string', length: 255)]
    private ?string $imageLink = null;

    #[ORM\ManyToOne(targetEntity: GeneratedArticles::class, inversedBy: 'articleImages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GeneratedArticles $article = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageLink(): ?string
    {
        return $this->imageLink;
    }

    public function setImageLink(string $imageLink): self
    {
        $this->imageLink = $imageLink;

        return $this;
    }

    public function getArticle(): ?GeneratedArticles
    {
        return $this->article;
    }

    public function setArticle(?GeneratedArticles $article): self
    {
        $this->article = $article;

        return $this;
    }
}
