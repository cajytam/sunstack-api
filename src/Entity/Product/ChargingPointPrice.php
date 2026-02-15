<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Product\ChargingPointPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ChargingPointPriceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(forceEager: true),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['simulation_charging_point_price:read', 'read:id']],
    denormalizationContext: ['groups' => ['simulation_charging_point_price:write']],
    security: "is_granted('ROLE_USER')"
)]
class ChargingPointPrice
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'simulation_charging_point_price:read', 'simulation_charging_point_price:write', 'simulation_charging_point:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[Groups(['simulation:read', 'simulation_charging_point_price:read', 'simulation_charging_point_price:write', 'simulation_charging_point:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[Groups(['simulation:read', 'simulation_charging_point_price:read', 'simulation_charging_point_price:write', 'simulation_charging_point:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[Groups(['simulation:read', 'simulation_charging_point_price:read', 'simulation_charging_point_price:write'])]
    #[ORM\ManyToOne(inversedBy: 'chargingPointPrices')]
    private ?ChargingPoint $chargingPoint = null;

    #[Groups(['simulation:read', 'simulation_charging_point_price:read', 'simulation_charging_point_price:write', 'simulation_charging_point:read'])]
    #[ORM\ManyToOne]
    private ?User $updatedBy = null;

    #[Groups(['simulation:read', 'simulation_charging_point_price:read', 'simulation_charging_point_price:write', 'simulation_charging_point:read'])]
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

    public function getChargingPoint(): ?ChargingPoint
    {
        return $this->chargingPoint;
    }

    public function setChargingPoint(?ChargingPoint $chargingPoint): static
    {
        $this->chargingPoint = $chargingPoint;

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
