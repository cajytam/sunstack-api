<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Customer\Building;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\SimulationOptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SimulationOptionRepository::class)]
#[ApiResource]
class SimulationOption
{
    use IdIntTrait;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phaseType = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $addBattery = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $addChargingPoint = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $installationLocation = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nbLevel = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $asbestosRoof = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nbRoofSection = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isErpBuilding = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'simulationOptions')]
    private ?Building $building = null;

    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roofType = null;

    public function getPhaseType(): ?string
    {
        return $this->phaseType;
    }

    public function setPhaseType(?string $phaseType): static
    {
        $this->phaseType = $phaseType;

        return $this;
    }

    public function getAddBattery(): ?string
    {
        return $this->addBattery;
    }

    public function setAddBattery(?string $addBattery): static
    {
        $this->addBattery = $addBattery;

        return $this;
    }

    public function getAddChargingPoint(): ?string
    {
        return $this->addChargingPoint;
    }

    public function setAddChargingPoint(?string $addChargingPoint): static
    {
        $this->addChargingPoint = $addChargingPoint;

        return $this;
    }

    public function getInstallationLocation(): ?string
    {
        return $this->installationLocation;
    }

    public function setInstallationLocation(?string $installationLocation): static
    {
        $this->installationLocation = $installationLocation;

        return $this;
    }

    public function getNbLevel(): ?string
    {
        return $this->nbLevel;
    }

    public function setNbLevel(?string $nbLevel): static
    {
        $this->nbLevel = $nbLevel;

        return $this;
    }

    public function getAsbestosRoof(): ?string
    {
        return $this->asbestosRoof;
    }

    public function setAsbestosRoof(?string $asbestosRoof): static
    {
        $this->asbestosRoof = $asbestosRoof;

        return $this;
    }

    public function getNbRoofSection(): ?string
    {
        return $this->nbRoofSection;
    }

    public function setNbRoofSection(?string $nbRoofSection): static
    {
        $this->nbRoofSection = $nbRoofSection;

        return $this;
    }

    public function isIsErpBuilding(): ?bool
    {
        return $this->isErpBuilding;
    }

    public function setIsErpBuilding(?bool $isErpBuilding): static
    {
        $this->isErpBuilding = $isErpBuilding;

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

    public function getRoofType(): ?string
    {
        return $this->roofType;
    }

    public function setRoofType(?string $roofType): static
    {
        $this->roofType = $roofType;

        return $this;
    }
}
