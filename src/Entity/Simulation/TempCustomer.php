<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Customer\CompanyType;
use App\Entity\Customer\PDL;
use App\Entity\Traits\CustomerTypeTrait;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Repository\Simulation\TempCustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['temp_customer:read', 'read:id']],
    denormalizationContext: ['groups' => ['temp_customer:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: TempCustomerRepository::class)]
class TempCustomer
{
    use IdIntTrait;
    use TimeStampTrait;
    use CustomerTypeTrait;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstname = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastname = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'tempCustomers')]
    private ?CompanyType $companyType = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyDenomination = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siret = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $streetNumber = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetName = null;

    #[Groups(['temp_customer:read', 'temp_customer:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $streetPostBox = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $streetPostcode = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetCity = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\OneToMany(mappedBy: 'tempCustomer', targetEntity: Simulation::class)]
    private Collection $simulations;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'tempCustomer', targetEntity: PDL::class)]
    private Collection $pDLs;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $canDeductVAT = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $civility = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $representativeCivility = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $representativeFirstname = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $representativeLastname = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $representativePosition = null;

    public function __construct()
    {
        $this->simulations = new ArrayCollection();
        $this->pDLs = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getCompanyType(): ?CompanyType
    {
        return $this->companyType;
    }

    public function setCompanyType(?CompanyType $companyType): static
    {
        $this->companyType = $companyType;

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

    public function getCompanyDenomination(): ?string
    {
        return $this->companyDenomination;
    }

    public function setCompanyDenomination(?string $companyDenomination): static
    {
        $this->companyDenomination = $companyDenomination;

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

    public function getStreetPostBox(): ?string
    {
        return $this->streetPostBox;
    }

    public function setStreetPostBox(?string $streetPostBox): static
    {
        $this->streetPostBox = $streetPostBox;

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

    public function getStreetCity(): ?string
    {
        return $this->streetCity;
    }

    public function setStreetCity(?string $streetCity): static
    {
        $this->streetCity = $streetCity;

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
            $simulation->setTempCustomer($this);
        }

        return $this;
    }

    public function removeSimulation(Simulation $simulation): static
    {
        if ($this->simulations->removeElement($simulation)) {
            // set the owning side to null (unless already changed)
            if ($simulation->getTempCustomer() === $this) {
                $simulation->setTempCustomer(null);
            }
        }

        return $this;
    }

    public function getFullName(): string|null
    {
        if (2 === $this->getCustomerType()) {
            return ($this->getCompanyName() && strlen($this->getCompanyName()) > 0)
                ? $this->getCompanyName()
                : ($this->getCompanyDenomination() ?: $this->getLastname() . ' ' . $this->getFirstname());
        }
        return $this->getLastname() . ' ' . $this->getFirstname();
    }

    /**
     * @return Collection<int, PDL>
     */
    public function getPDLs(): Collection
    {
        return $this->pDLs;
    }

    public function addPDL(PDL $pDL): static
    {
        if (!$this->pDLs->contains($pDL)) {
            $this->pDLs->add($pDL);
            $pDL->setTempCustomer($this);
        }

        return $this;
    }

    public function removePDL(PDL $pDL): static
    {
        if ($this->pDLs->removeElement($pDL)) {
            // set the owning side to null (unless already changed)
            if ($pDL->getTempCustomer() === $this) {
                $pDL->setTempCustomer(null);
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

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): static
    {
        $this->civility = $civility;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;

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

    public function getRepresentativeFirstname(): ?string
    {
        return $this->representativeFirstname;
    }

    public function setRepresentativeFirstname(?string $representativeFirstname): static
    {
        $this->representativeFirstname = $representativeFirstname;

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

    public function getRepresentativePosition(): ?string
    {
        return $this->representativePosition;
    }

    public function setRepresentativePosition(?string $representativePosition): static
    {
        $this->representativePosition = $representativePosition;

        return $this;
    }
}
