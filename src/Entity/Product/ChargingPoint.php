<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\SimulationChargingPoint;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Product\ChargingPointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ChargingPointRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(forceEager: true),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['simulation_charging_point:read', 'read:id']],
    denormalizationContext: ['groups' => ['simulation_charging_point:write']],
    security: "is_granted('ROLE_USER')"
)]
class ChargingPoint
{
    use IdIntTrait;

    #[Groups(['simulation_charging_point:read', 'simulation_charging_point:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $brand = null;

    #[Groups(['simulation_charging_point:read', 'simulation_charging_point:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[Groups(['simulation_charging_point:read', 'simulation_charging_point:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $power = null;

    /**
     * @var Collection<int, ChargingPointPrice>
     */
    #[Groups(['simulation_charging_point:read'])]
    #[ORM\OneToMany(mappedBy: 'chargingPoint', targetEntity: ChargingPointPrice::class)]
    private Collection $chargingPointPrices;

    /**
     * @var Collection<int, SimulationChargingPoint>
     */
    #[Groups(['simulation_charging_point:read'])]
    #[ORM\OneToMany(mappedBy: 'chargingPoint', targetEntity: SimulationChargingPoint::class)]
    private Collection $simulationChargingPoints;

    public function __construct()
    {
        $this->chargingPointPrices = new ArrayCollection();
        $this->simulationChargingPoints = new ArrayCollection();
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getPower(): ?float
    {
        return $this->power;
    }

    public function setPower(?float $power): static
    {
        $this->power = $power;

        return $this;
    }

    /**
     * @return Collection<int, ChargingPointPrice>
     */
    public function getChargingPointPrices(): Collection
    {
        return $this->chargingPointPrices;
    }

    public function addChargingPointPrice(ChargingPointPrice $chargingPointPrice): static
    {
        if (!$this->chargingPointPrices->contains($chargingPointPrice)) {
            $this->chargingPointPrices->add($chargingPointPrice);
            $chargingPointPrice->setChargingPoint($this);
        }

        return $this;
    }

    public function removeChargingPointPrice(ChargingPointPrice $chargingPointPrice): static
    {
        if ($this->chargingPointPrices->removeElement($chargingPointPrice)) {
            // set the owning side to null (unless already changed)
            if ($chargingPointPrice->getChargingPoint() === $this) {
                $chargingPointPrice->setChargingPoint(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SimulationChargingPoint>
     */
    public function getSimulationChargingPoints(): Collection
    {
        return $this->simulationChargingPoints;
    }

    public function addSimulationChargingPoint(SimulationChargingPoint $simulationChargingPoint): static
    {
        if (!$this->simulationChargingPoints->contains($simulationChargingPoint)) {
            $this->simulationChargingPoints->add($simulationChargingPoint);
            $simulationChargingPoint->setChargingPoint($this);
        }

        return $this;
    }

    public function removeSimulationChargingPoint(SimulationChargingPoint $simulationChargingPoint): static
    {
        if ($this->simulationChargingPoints->removeElement($simulationChargingPoint)) {
            // set the owning side to null (unless already changed)
            if ($simulationChargingPoint->getChargingPoint() === $this) {
                $simulationChargingPoint->setChargingPoint(null);
            }
        }

        return $this;
    }
}
