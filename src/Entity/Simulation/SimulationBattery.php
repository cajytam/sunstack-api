<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Product\Battery;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\SimulationBatteryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SimulationBatteryRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(forceEager: true),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['simulation_battery:read', 'read:id']],
    denormalizationContext: ['groups' => ['simulation_battery:write']],
    security: "is_granted('ROLE_USER')"
)]
class SimulationBattery
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'simulation_battery:read', 'simulation_battery:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulationBatteries')]
    private ?Simulation $simulation = null;

    #[Groups(['simulation:read', 'simulation_battery:read', 'simulation_battery:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulationBatteries')]
    private ?Battery $battery = null;

    #[Groups(['simulation:read', 'simulation_battery:read', 'simulation_battery:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[Groups(['simulation:read', 'simulation_battery:read', 'simulation_battery:write'])]
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

    public function getBattery(): ?Battery
    {
        return $this->battery;
    }

    public function setBattery(?Battery $battery): static
    {
        $this->battery = $battery;

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
