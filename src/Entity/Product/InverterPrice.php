<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Product\InverterPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InverterPriceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['inverterPrice:read', 'read:id']],
    denormalizationContext: ['groups' => ['inverterPrice:write']],
    security: "is_granted('ROLE_USER')",
)]
class InverterPrice
{
    use IdIntTrait;

    #[Groups(['inverterPrice:read', 'inverterPrice:write', 'inverter:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[Groups(['inverterPrice:read', 'inverterPrice:write', 'inverter:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[Groups(['inverterPrice:read', 'inverterPrice:write', 'inverter:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[Groups(['inverterPrice:read', 'inverterPrice:write'])]
    #[ORM\ManyToOne(inversedBy: 'inverterPrices')]
    private ?Inverter $inverter = null;

    #[Groups(['inverterPrice:read', 'inverterPrice:write', 'inverter:read'])]
    #[ORM\ManyToOne(inversedBy: 'inverterPrices')]
    private ?User $updatedBy = null;

    #[Groups(['inverterPrice:read', 'inverterPrice:write', 'inverter:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['inverterPrice:read', 'inverterPrice:write', 'inverter:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

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

    public function getInverter(): ?Inverter
    {
        return $this->inverter;
    }

    public function setInverter(?Inverter $inverter): static
    {
        $this->inverter = $inverter;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
