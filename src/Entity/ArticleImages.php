<?php

namespace App\Entity;

use App\Repository\ArticleImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleImagesRepository::class)]
class ArticleImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', nullable: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'image_link', length: 255, nullable: false)]
    private ?string $imageLink = null;

    #[ORM\ManyToOne(inversedBy: 'articleImages')]
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
