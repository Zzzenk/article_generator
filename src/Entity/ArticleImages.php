<?php

namespace App\Entity;

use App\Repository\ArticleImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleImagesRepository::class)]
class ArticleImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imageLink = null;

    #[ORM\ManyToOne(inversedBy: 'articleImages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GeneratedArticles $articleId = null;

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

    public function getArticleId(): ?GeneratedArticles
    {
        return $this->articleId;
    }

    public function setArticleId(?GeneratedArticles $articleId): self
    {
        $this->articleId = $articleId;

        return $this;
    }
}
