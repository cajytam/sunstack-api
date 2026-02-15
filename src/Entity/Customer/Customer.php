<?php

namespace App\Entity\Customer;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\Simulation;
use App\Entity\Traits\CivilitiesTrait;
use App\Entity\Traits\CustomerTypeTrait;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Repository\Customer\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    security: "is_granted('ROLE_USER')",
)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    use IdIntTrait;
    use TimeStampTrait;
    use CivilitiesTrait;
    use CustomerTypeTrait;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $civility = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstname = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastname = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $streetNumber = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetName = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $streetLocation = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetCity = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $streetPostcode = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $streetPostbox = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $streetCedex = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneAreaCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foreignCountry = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $foreignTerritorialDivision = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isAgreeReceiveEmailAdm = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthDepartment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthCountry = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $denomination = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siret = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $representativeCivility = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $representativeLastname = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $representativeFirstname = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'customers')]
    private ?CompanyType $companyType = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Simulation::class)]
    private Collection $simulations;

    #[Groups(['simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: PDL::class)]
    private Collection $pdls;

    #[ORM\Column(nullable: true)]
    private ?bool $canDeductVAT = null;

    public function __construct()
    {
        $this->simulations = new ArrayCollection();
        $this->pdls = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): static
    {
        $this->civility = $civility;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

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

    public function getStreetLocation(): ?string
    {
        return $this->streetLocation;
    }

    public function setStreetLocation(?string $streetLocation): static
    {
        $this->streetLocation = $streetLocation;

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

    public function getStreetPostcode(): ?string
    {
        return $this->streetPostcode;
    }

    public function setStreetPostcode(?string $streetPostcode): static
    {
        $this->streetPostcode = $streetPostcode;

        return $this;
    }

    public function getStreetPostbox(): ?string
    {
        return $this->streetPostbox;
    }

    public function setStreetPostbox(?string $streetPostbox): static
    {
        $this->streetPostbox = $streetPostbox;

        return $this;
    }

    public function getStreetCedex(): ?string
    {
        return $this->streetCedex;
    }

    public function setStreetCedex(?string $streetCedex): static
    {
        $this->streetCedex = $streetCedex;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPhoneAreaCode(): ?string
    {
        return $this->phoneAreaCode;
    }

    public function setPhoneAreaCode(?string $phoneAreaCode): static
    {
        $this->phoneAreaCode = $phoneAreaCode;

        return $this;
    }

    public function getForeignCountry(): ?string
    {
        return $this->foreignCountry;
    }

    public function setForeignCountry(?string $foreignCountry): static
    {
        $this->foreignCountry = $foreignCountry;

        return $this;
    }

    public function getForeignTerritorialDivision(): ?string
    {
        return $this->foreignTerritorialDivision;
    }

    public function setForeignTerritorialDivision(?string $foreignTerritorialDivision): static
    {
        $this->foreignTerritorialDivision = $foreignTerritorialDivision;

        return $this;
    }

    public function isIsAgreeReceiveEmailAdm(): ?bool
    {
        return $this->isAgreeReceiveEmailAdm;
    }

    public function setIsAgreeReceiveEmailAdm(?bool $isAgreeReceiveEmailAdm): static
    {
        $this->isAgreeReceiveEmailAdm = $isAgreeReceiveEmailAdm;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthCity(): ?string
    {
        return $this->birthCity;
    }

    public function setBirthCity(?string $birthCity): static
    {
        $this->birthCity = $birthCity;

        return $this;
    }

    public function getBirthDepartment(): ?string
    {
        return $this->birthDepartment;
    }

    public function setBirthDepartment(?string $birthDepartment): static
    {
        $this->birthDepartment = $birthDepartment;

        return $this;
    }

    public function getBirthCountry(): ?string
    {
        return $this->birthCountry;
    }

    public function setBirthCountry(?string $birthCountry): static
    {
        $this->birthCountry = $birthCountry;

        return $this;
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(?string $denomination): static
    {
        $this->denomination = $denomination;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getRepresentativeCivility(): ?string
    {
        return $this->representativeCivility;
    }

    public function setRepresentativeCivility(?string $representativeCivility): static
    {
        $this->representativeCivility = $representativeCivility;

        return $this;
    }

    public function getRepresentativeLastname(): ?string
    {
        return $this->representativeLastname;
    }

    public function setRepresentativeLastname(?string $representativeLastname): static
    {
        $this->representativeLastname = $representativeLastname;

        return $this;
    }

    public function getRepresentativeFirstname(): ?string
    {
        return $this->representativeFirstname;
    }

    public function setRepresentativeFirstname(?string $representativeFirstname): static
    {
        $this->representativeFirstname = $representativeFirstname;

        return $this;
    }

    public function getCompanyType(): ?CompanyType
    {
        return $this->companyType;
    }

    public function setCompanyType(?CompanyType $companyType): static
    {
        $this->companyType = $companyType;

        return $this;
    }

    /**
     * @return Collection<int, Simulation>
     */
    public function getSimulations(): Collection
    {
        return $this->simulations;
    }

    public function addSimulation(Simulation $simulation): static
    {
        if (!$this->simulations->contains($simulation)) {
            $this->simulations->add($simulation);
            $simulation->setCustomer($this);
        }

        return $this;
    }

    public function removeSimulation(Simulation $simulation): static
    {
        if ($this->simulations->removeElement($simulation)) {
            // set the owning side to null (unless already changed)
            if ($simulation->getCustomer() === $this) {
                $simulation->setCustomer(null);
            }
        }

        return $this;
    }

    public function getFullName(): string|null
    {
        if (2 === $this->getCustomerType()) {
            return $this->getCompanyName();
        }
        return $this->getLastname() . ' ' . $this->getFirstname();
    }

    /**
     * @return Collection<int, PDL>
     */
    public function getPdls(): Collection
    {
        return $this->pdls;
    }

    public function addPdl(PDL $pdl): static
    {
        if (!$this->pdls->contains($pdl)) {
            $this->pdls->add($pdl);
            $pdl->setCustomer($this);
        }

        return $this;
    }

    public function removePdl(PDL $pdl): static
    {
        if ($this->pdls->removeElement($pdl)) {
            // set the owning side to null (unless already changed)
            if ($pdl->getCustomer() === $this) {
                $pdl->setCustomer(null);
            }
        }

        return $this;
    }

    public function isCanDeductVAT(): ?bool
    {
        return $this->canDeductVAT;
    }

    public function setCanDeductVAT(?bool $canDeductVAT): static
    {
        $this->canDeductVAT = $canDeductVAT;

        return $this;
    }
}
