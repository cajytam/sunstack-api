<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Product\ChargingPoint;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\SimulationChargingPointRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SimulationChargingPointRepository::class)]
#[ApiResource]
class SimulationChargingPoint
{
    use IdIntTrait;

    #[ORM\ManyToOne(inversedBy: 'simulationChargingPoints')]
    private ?Simulation $simulation = null;

    #[ORM\ManyToOne(inversedBy: 'simulationChargingPoints')]
    private ?ChargingPoint $chargingPoint = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }
}
