<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Product\InverterCablePriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InverterCablePriceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['inverterCablePrice:read', 'read:id']],
    denormalizationContext: ['groups' => ['inverterCablePrice:write']],
    security: "is_granted('ROLE_USER')",
)]
class InverterCablePrice
{
    use IdIntTrait;

    #[Groups(['inverterCablePrice:read', 'inverterCablePrice:write', 'inverterCable:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[Groups(['inverterCablePrice:read', 'inverterCablePrice:write', 'inverterCable:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[Groups(['inverterCablePrice:read', 'inverterCablePrice:write', 'inverterCable:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[Groups(['inverterCablePrice:read', 'inverterCablePrice:write', 'inverterCable:read'])]
    #[ORM\ManyToOne(inversedBy: 'inverterCablePrices')]
    private ?InverterCable $inverterCable = null;

    #[Groups(['inverterCablePrice:read', 'inverterCablePrice:write', 'inverterCable:read'])]
    #[ORM\ManyToOne(inversedBy: 'inverterCablePrices')]
    private ?User $updatedBy = null;

    #[Groups(['inverterCablePrice:read', 'inverterCablePrice:write', 'inverterCable:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getInverterCable(): ?InverterCable
    {
        return $this->inverterCable;
    }

    public function setInverterCable(?InverterCable $inverterCable): static
    {
        $this->inverterCable = $inverterCable;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
