<?php

namespace App\Entity\Customer;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Enum\CompteurType;
use App\Entity\Simulation\SimulationItem;
use App\Entity\Simulation\TempCustomer;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Customer\PDLRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'pdl',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['pdl:read', 'read:id']],
    denormalizationContext: ['groups' => ['pdl:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: PDLRepository::class)]
class PDL
{
    use IdIntTrait;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(length: 50)]
    private ?string $pdlNumber = null;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $streetNumber = null;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetName = null;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $streetPostCode = null;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetCity = null;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(length: 1, nullable: true)]
    #[Assert\Choice(
        callback: [CompteurType::class, 'values'],
        message: 'La valeur doit être soit "M" pour Monophasé ou "T" pour Triphasé'
    )]
    private ?string $typeCompteur = null;

    #[Groups(['pdl:read', 'pdl:write'])]
    #[ORM\ManyToOne(inversedBy: 'pdls')]
    private ?Customer $customer = null;

    #[Groups(['pdl:read', 'pdl:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[Groups(['pdl:read', 'pdl:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $buildingId = null;

    #[Groups(['pdl:read', 'pdl:write'])]
    #[ORM\ManyToOne(inversedBy: 'pDLs')]
    private ?TempCustomer $tempCustomer = null;

    #[Groups(['pdl:read'])]
    #[ORM\OneToMany(mappedBy: 'pdl', targetEntity: SimulationItem::class)]
    private Collection $simulationItems;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isCustomerCertifiesOwnership = null;

    #[Groups(['pdl:read', 'pdl:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isAgreementBareOwner = null;

    public function __construct()
    {
        $this->simulationItems = new ArrayCollection();
    }

    public function getPdlNumber(): ?string
    {
        return $this->pdlNumber;
    }

    public function setPdlNumber(string $pdlNumber): static
    {
        $this->pdlNumber = $pdlNumber;

        return $this;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?string $streetNumber): static
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(?string $streetName): static
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getStreetPostCode(): ?string
    {
        return $this->streetPostCode;
    }

    public function setStreetPostCode(?string $streetPostCode): static
    {
        $this->streetPostCode = $streetPostCode;

        return $this;
    }

    public function getStreetCity(): ?string
    {
        return $this->streetCity;
    }

    public function setStreetCity(?string $streetCity): static
    {
        $this->streetCity = $streetCity;

        return $this;
    }

    public function getTypeCompteur(): ?string
    {
        return $this->typeCompteur;
    }

    public function setTypeCompteur(?string $typeCompteur): static
    {
        $this->typeCompteur = $typeCompteur;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getBuildingId(): ?string
    {
        return $this->buildingId;
    }

    public function setBuildingId(?string $buildingId): static
    {
        $this->buildingId = $buildingId;

        return $this;
    }

    public function getTempCustomer(): ?TempCustomer
    {
        return $this->tempCustomer;
    }

    public function setTempCustomer(?TempCustomer $tempCustomer): static
    {
        $this->tempCustomer = $tempCustomer;

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
            $simulationItem->setPdl($this);
        }

        return $this;
    }

    public function removeSimulationItem(SimulationItem $simulationItem): static
    {
        if ($this->simulationItems->removeElement($simulationItem)) {
            // set the owning side to null (unless already changed)
            if ($simulationItem->getPdl() === $this) {
                $simulationItem->setPdl(null);
            }
        }

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
}
