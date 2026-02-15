<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Product\BatteryPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BatteryPriceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['batteryPrice:read', 'read:id']],
    denormalizationContext: ['groups' => ['batteryPrice:write']],
    security: "is_granted('ROLE_USER')",
)]
class BatteryPrice
{
    use IdIntTrait;

    #[Groups(['batteryPrice:read', 'batteryPrice:write', 'battery:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[Groups(['batteryPrice:read', 'batteryPrice:write'])]
    #[ORM\ManyToOne(inversedBy: 'batteryPrices')]
    private ?Battery $battery = null;

    #[Groups(['batteryPrice:read', 'batteryPrice:write', 'battery:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $marginFixed = null;

    #[Groups(['batteryPrice:read', 'batteryPrice:write', 'battery:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[Groups(['batteryPrice:read', 'batteryPrice:write', 'battery:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[Groups(['batteryPrice:read', 'batteryPrice:write', 'battery:read'])]
    #[ORM\ManyToOne]
    private ?User $updatedBy = null;

    #[Groups(['batteryPrice:read', 'batteryPrice:write', 'battery:read'])]
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

    public function getBattery(): ?Battery
    {
        return $this->battery;
    }

    public function setBattery(?Battery $battery): static
    {
        $this->battery = $battery;

        return $this;
    }

    public function getMarginFixed(): ?string
    {
        return $this->marginFixed;
    }

    public function setMarginFixed(?string $marginFixed): static
    {
        $this->marginFixed = $marginFixed;

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
