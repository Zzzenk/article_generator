<?php

namespace App\DTO;

class ArticleDataDTO
{
    private string $theme;
    private string $title;
    private ?int $sizeFrom;
    private ?int $sizeTo;
    private ?string $word1;
    private ?int $word1Count;
    private ?string $word2;
    private ?int $word2Count;
    private ?string $keyword0 = null;
    private ?string $keyword1 = null;
    private ?string $keyword2 = null;
    private ?string $keyword3 = null;
    private ?string $keyword4 = null;
    private ?string $keyword5 = null;
    private ?string $keyword6 = null;
    private ?array $images;

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setSizeFrom(int $sizeFrom): void
    {
        $this->sizeFrom = $sizeFrom;
    }

    public function getSizeFrom(): ?int
    {
        return $this->sizeFrom;
    }

    public function setSizeTo(int $sizeTo): void
    {
        $this->sizeTo = $sizeTo;
    }

    public function getSizeTo(): ?int
    {
        return $this->sizeTo;
    }

    public function setWord1(string $word1): void
    {
        $this->word1 = $word1;
    }

    public function getWord1(): ?string
    {
        return $this->word1;
    }

    public function setWord1Count(int $word1Count): void
    {
        $this->word1Count = $word1Count;
    }

    public function getWord1Count(): ?int
    {
        return $this->word1Count;
    }

    public function setWord2(string $word2): void
    {
        $this->word2 = $word2;
    }

    public function getWord2(): ?string
    {
        return $this->word2;
    }

    public function setWord2Count(int $word2Count): void
    {
        $this->word2Count = $word2Count;
    }

    public function getWord2Count(): ?int
    {
        return $this->word2Count;
    }

    public function setKeyword0(string $keyword0): void
    {
        $this->keyword0 = $keyword0;
    }

    public function getKeyword0(): ?string
    {
        return $this->keyword0;
    }

    public function setKeyword1(string $keyword1): void
    {
        $this->keyword1 = $keyword1;
    }

    public function getKeyword1(): ?string
    {
        return $this->keyword1;
    }

    public function setKeyword2(string $keyword2): void
    {
        $this->keyword2 = $keyword2;
    }

    public function getKeyword2(): ?string
    {
        return $this->keyword2;
    }

    public function setKeyword3(string $keyword3): void
    {
        $this->keyword3 = $keyword3;
    }

    public function getKeyword3(): ?string
    {
        return $this->keyword3;
    }

    public function setKeyword4(string $keyword4): void
    {
        $this->keyword4 = $keyword4;
    }

    public function getKeyword4(): ?string
    {
        return $this->keyword4;
    }

    public function setKeyword5(string $keyword5): void
    {
        $this->keyword5 = $keyword5;
    }

    public function getKeyword5(): ?string
    {
        return $this->keyword5;
    }

    public function setKeyword6(string $keyword6): void
    {
        $this->keyword6 = $keyword6;
    }

    public function getKeyword6(): ?string
    {
        return $this->keyword6;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }
}
