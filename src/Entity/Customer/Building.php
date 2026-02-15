<?php

namespace App\Entity\Customer;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\Simulation;
use App\Entity\Simulation\SimulationItem;
use App\Entity\Simulation\SimulationOption;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Customer\BuildingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['building:read', 'read:id']],
    denormalizationContext: ['groups' => ['building:write']],
    security: "is_granted('ROLE_USER')"
)]
class Building
{
    use IdIntTrait;

    #[Groups(['building:read', 'building:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['building:read', 'building:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdlNumber = null;

    #[Groups(['building:read', 'building:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isCustomerCertifiesOwnership = null;

    #[Groups(['building:read', 'building:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isAgreementBareOwner = null;

    #[Groups(['building:read', 'building:write'])]
    #[ORM\ManyToOne(inversedBy: 'buildings')]
    private ?Simulation $simulation = null;

    /**
     * @var Collection<int, SimulationItem>
     */
    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'building', targetEntity: SimulationItem::class)]
    private Collection $simulationItems;

    /**
     * @var Collection<int, SimulationOption>
     */
    #[Groups(['building:read', 'simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'building', targetEntity: SimulationOption::class)]
    private Collection $simulationOptions;

    public function __construct()
    {
        $this->simulationItems = new ArrayCollection();
        $this->simulationOptions = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPdlNumber(): ?string
    {
        return $this->pdlNumber;
    }

    public function setPdlNumber(?string $pdlNumber): static
    {
        $this->pdlNumber = $pdlNumber;

        return $this;
    }

    public function isIsCustomerCertifiesOwnership(): ?bool
    {
        return $this->isCustomerCertifiesOwnership;
    }

    public function setIsCustomerCertifiesOwnership(?bool $isCustomerCertifiesOwnership): static
    {
        $this->isCustomerCertifiesOwnership = $isCustomerCertifiesOwnership;

        return $this;
    }

    public function isIsAgreementBareOwner(): ?bool
    {
        return $this->isAgreementBareOwner;
    }

    public function setIsAgreementBareOwner(?bool $isAgreementBareOwner): static
    {
        $this->isAgreementBareOwner = $isAgreementBareOwner;

        return $this;
    }

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

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
            $simulationItem->setBuilding($this);
        }

        return $this;
    }

    public function removeSimulationItem(SimulationItem $simulationItem): static
    {
        if ($this->simulationItems->removeElement($simulationItem)) {
            // set the owning side to null (unless already changed)
            if ($simulationItem->getBuilding() === $this) {
                $simulationItem->setBuilding(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SimulationOption>
     */
    public function getSimulationOptions(): Collection
    {
        return $this->simulationOptions;
    }

    public function addSimulationOption(SimulationOption $simulationOption): static
    {
        if (!$this->simulationOptions->contains($simulationOption)) {
            $this->simulationOptions->add($simulationOption);
            $simulationOption->setBuilding($this);
        }

        return $this;
    }

    public function removeSimulationOption(SimulationOption $simulationOption): static
    {
        if ($this->simulationOptions->removeElement($simulationOption)) {
            // set the owning side to null (unless already changed)
            if ($simulationOption->getBuilding() === $this) {
                $simulationOption->setBuilding(null);
            }
        }

        return $this;
    }
}
