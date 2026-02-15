<?php

namespace App\Entity\Sale;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\Simulation;
use App\Entity\User\User;
use App\Repository\Sale\SellerBonusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SellerBonusRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    security: "is_granted('ROLE_USER')"
)]
class SellerBonus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sellerBonuses')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?float $number = null;

    #[ORM\ManyToOne(inversedBy: 'sellerBonuses')]
    private ?Simulation $simulation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getNumber(): ?float
    {
        return $this->number;
    }

    public function setNumber(?float $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

        return $this;
    }
}
