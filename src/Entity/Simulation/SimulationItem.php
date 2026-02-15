<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Customer\Building;
use App\Entity\Customer\PDL;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\SimulationItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(forceEager: true),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['simulation_item:read', 'read:id']],
    denormalizationContext: ['groups' => ['simulation_item:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: SimulationItemRepository::class)]
class SimulationItem
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'simulation_item:read', 'simulation_item:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulationItems')]
    private ?Simulation $simulation = null;

    #[Groups(['simulation:read', 'simulation_item:read', 'simulation_item:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $nbPanel = null;

    #[Groups(['simulation:read', 'simulation_item:read', 'simulation_item:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $energyPotential = null;

    #[Groups(['simulation:read', 'simulation:write', 'simulation_item:read', 'simulation_item:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulationItems')]
    private ?PDL $pdl = null;

    #[Groups(['simulation:read', 'simulation:write', 'simulation_item:read', 'simulation_item:write'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'simulationItems')]
    private ?Zone $zone = null;

    #[ORM\Column(nullable: true)]
    private ?float $firstYearTotalEstimatedProduction = null;

    #[Groups(['simulation:read', 'simulation_item:read','simulation_item:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulationItems')]
    private ?Building $building = null;

    #[Groups(['simulation:read', 'simulation_item:read','simulation_item:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $detailedEnergyPotential = null;

    #[Groups(['simulation:read', 'simulation_item:read','simulation_item:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $inclinaison = null;

    #[Groups(['simulation:read', 'simulation_item:read','simulation_item:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $azimuth = null;

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

        return $this;
    }

    public function getNbPanel(): ?int
    {
        return $this->nbPanel;
    }

    public function setNbPanel(?int $nbPanel): static
    {
        $this->nbPanel = $nbPanel;

        return $this;
    }

    public function getPanel(): ?Panel
    {
        return $this->panel;
    }

    public function setPanel(?Panel $panel): static
    {
        $this->panel = $panel;

        return $this;
    }

    public function getEnergyPotential(): ?int
    {
        return $this->energyPotential;
    }

    public function setEnergyPotential(?int $energyPotential): static
    {
        $this->energyPotential = $energyPotential;

        return $this;
    }

    public function getPdl(): ?PDL
    {
        return $this->pdl;
    }

    public function setPdl(?PDL $pdl): static
    {
        $this->pdl = $pdl;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    public function getFirstYearTotalEstimatedProduction(): ?float
    {
        return $this->firstYearTotalEstimatedProduction;
    }

    public function setFirstYearTotalEstimatedProduction(?float $firstYearTotalEstimatedProduction): static
    {
        $this->firstYearTotalEstimatedProduction = $firstYearTotalEstimatedProduction;

        return $this;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): static
    {
        $this->building = $building;

        return $this;
    }

    public function getDetailedEnergyPotential(): ?array
    {
        return $this->detailedEnergyPotential;
    }

    public function setDetailedEnergyPotential(?array $detailedEnergyPotential): static
    {
        $this->detailedEnergyPotential = $detailedEnergyPotential;

        return $this;
    }

    public function getInclinaison(): ?int
    {
        return $this->inclinaison;
    }

    public function setInclinaison(?int $inclinaison): static
    {
        $this->inclinaison = $inclinaison;

        return $this;
    }

    public function getAzimuth(): ?int
    {
        return $this->azimuth;
    }

    public function setAzimuth(?int $azimuth): static
    {
        $this->azimuth = $azimuth;

        return $this;
    }
}
