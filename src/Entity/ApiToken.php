<?php

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use App\Service\ProfileUpdateService;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
#[ORM\Table(name: 'api_token')]
class ApiToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'token', type: 'string', length: 255)]
    private ?string $token;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private ?User $user;

    /**
     * @param User|null $user
     */
    public function __construct(
        ?User $user,
        private readonly ProfileUpdateService $profileUpdateService
    )
    {
        $this->user = $user;
        $this->token = $this->generateToken();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function generateToken()
    {
        return $this->profileUpdateService->generateNewToken();
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
