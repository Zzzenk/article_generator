<?php

namespace App\Entity;

use App\Repository\UnregisteredUsersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnregisteredUsersRepository::class)]
class UnregisteredUsers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(name: 'ip', length: 255)]
    private ?string $IP = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIP(): ?string
    {
        return $this->IP;
    }

    public function setIP(string $IP): self
    {
        $this->IP = $IP;

        return $this;
    }
}
