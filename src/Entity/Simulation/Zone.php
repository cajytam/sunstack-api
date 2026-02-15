<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Customer\PostCode;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['zone:read', 'read:id']],
    denormalizationContext: ['groups' => ['zone:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(SearchFilter::class, properties: ['department' => 'exact', 'panelTilt' => 'exact', 'roofOrientation' => 'exact'])]
#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    const PANEL_TILT = [
        0, 10, 30, 45
    ];

    const ROOF_AZIMUTH = [
        'N' => 0,
        'NE' => 45,
        'E' => 90,
        'SE' => 135,
        'S' => 180,
        'SO' => 225,
        'O' => 270,
        'NO' => 315,
    ];

    use IdIntTrait;

    #[Groups(['zone:read', 'simulation:read', 'simulation:write'])]
    #[ORM\Column]
    private ?float $energyPotential = null;

    #[Groups(['zone:read', 'simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(callback: 'getDepartments')]
    private ?string $department = null;

    #[Groups(['zone:read', 'simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(callback: 'getPanelTilts')]
    private ?string $panelTilt = null;

    #[Groups(['zone:read', 'simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(callback: 'getRoofOrientations')]
    private ?string $roofOrientation = null;

    #[ORM\OneToMany(mappedBy: 'zone', targetEntity: SimulationItem::class)]
    private Collection $simulationItems;

    public function __construct()
    {
        $this->simulationItems = new ArrayCollection();
    }

    public function getEnergyPotential(): ?float
    {
        return $this->energyPotential;
    }

    public function setEnergyPotential(float $energyPotential): static
    {
        $this->energyPotential = $energyPotential;

        return $this;
    }

    public static function getDepartments(): array
    {
        return PostCode::getAllDepartments();
    }

    public static function getPanelTilts(): array
    {
        return self::PANEL_TILT;
    }

    public static function getRoofOrientations(): array
    {
        return self::ROOF_AZIMUTH;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getPanelTilt(): ?string
    {
        return $this->panelTilt;
    }

    public function setPanelTilt(?string $panelTilt): static
    {
        $this->panelTilt = $panelTilt;

        return $this;
    }

    public function getRoofOrientation(): ?string
    {
        return $this->roofOrientation;
    }

    public function setRoofOrientation(?string $roofOrientation): static
    {
        $this->roofOrientation = $roofOrientation;

        return $this;
    }

    /**
     * @return Collection<int, SimulationItem>
     */
    public function getSimulationItems(): Collection
    {
        return $this->simulationItems;
    }

    public function addSimulationItem(SimulationItem $simulationItem): static
    {
        if (!$this->simulationItems->contains($simulationItem)) {
            $this->simulationItems->add($simulationItem);
            $simulationItem->setZone($this);
        }

        return $this;
    }

    public function removeSimulationItem(SimulationItem $simulationItem): static
    {
        if ($this->simulationItems->removeElement($simulationItem)) {
            // set the owning side to null (unless already changed)
            if ($simulationItem->getZone() === $this) {
                $simulationItem->setZone(null);
            }
        }

        return $this;
    }
}
