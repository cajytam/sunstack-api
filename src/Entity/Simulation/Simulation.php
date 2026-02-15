<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\Simulation\CreateSimulation;
use App\Entity\Customer\Building;
use App\Entity\Customer\Customer;
use App\Entity\Enum\InstallationPlace;
use App\Entity\Product\Panel;
use App\Entity\Sale\SellerBonus;
use App\Entity\Survey\Survey;
use App\Entity\Survey\SurveySimulation;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Entity\User\History;
use App\Entity\User\User;
use App\Factory\Calculation\Calculator;
use App\Repository\Simulation\SimulationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['simulation:read', 'read:id', 'timestamp:read']],
        ),
        new GetCollection(
            order: ['createdAt' => 'DESC'],
            normalizationContext: ['groups' => ['read:id', 'timestamp:read', 'simulationAll:read', 'file:export']],
            forceEager: true
        ),
        new Post(
            controller: CreateSimulation::class
        ),
        new Patch(
            controller: CreateSimulation::class
        )
    ],
    denormalizationContext: ['groups' => ['simulation:write', 'timestamp:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'identifier' => SearchFilterInterface::STRATEGY_EXACT,
        'ownedBy' => SearchFilterInterface::STRATEGY_EXACT,
        'signatureSimulations.purpose' => SearchFilterInterface::STRATEGY_EXACT,
        'statusSimulations.status.id' => SearchFilterInterface::STRATEGY_EXACT
    ]
)]
#[ApiFilter(ExistsFilter::class, properties: ['deletedAt', 'signedAt'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: SimulationRepository::class)]
class Simulation
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $number = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write'])]
    #[ORM\Column(length: 255)]
    private ?string $identifier = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write', 'file:export'])]
    #[ORM\ManyToOne(inversedBy: 'simulations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ownedBy = null;

    #[Groups(['simulation:read', 'simulation:write', 'file:export'])]
    #[ORM\ManyToOne]
    private ?Panel $panel = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write', 'user:read', 'surveySimulation:read', 'file:export'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $consumptionQuantity = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $consumptionPrice = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulations')]
    private ?Profile $profile = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulations')]
    private ?TempCustomer $tempCustomer = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $reducedYield = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $priceEscalation = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'simulations')]
    private ?Customer $customer = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write', 'file:export'])]
    #[ORM\Column(nullable: true)]
    private ?float $installationPriceHT = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: SimulationItem::class)]
    private Collection $simulationItems;

    #[Assert\Choice(
        callback: [InstallationPlace::class, 'values'],
        message: 'La valeur doit être soit "S" pour Sol ou "T" pour Toit'
    )]
    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $installationLocation = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isArdoiseClouee = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $nbToitures = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $nbCharpentesNonVisibles = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $nbCharpentesBeton = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write', 'file:export'])]
    #[ORM\Column(nullable: true)]
    private ?float $finalPriceHT = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'simulation:write', 'file:export'])]
    #[ORM\Column(nullable: true)]
    private ?float $manualPrice = null;

    #[Groups(['simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: History::class)]
    private Collection $histories;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $salesPriceEscalation = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'file:export'])]
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: StatusSimulation::class)]
    #[ORM\OrderBy(['id' => 'desc'])]
    private Collection $statusSimulations;

    #[Groups(['simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: SignatureSimulation::class)]
    private Collection $signatureSimulations;

    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: FileSimulation::class)]
    private Collection $fileSimulations;

    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: SellerBonus::class)]
    private Collection $sellerBonuses;

    #[Groups(['simulation:read', 'surveySimulation:read'])]
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: Survey::class)]
    private Collection $surveys;

    #[Groups(['simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: SurveySimulation::class)]
    private Collection $surveySimulations;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $reducedYieldFirstYear = null;

    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: TaskSimulation::class)]
    private Collection $taskSimulations;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isForcePanelType = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $installationStreetNumber = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $installationStreetName = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $installationStreetPostcode = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $installationStreetCity = null;

    /**
     * @var Collection<int, Building>
     */
    #[Groups(['simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: Building::class)]
    private Collection $buildings;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isSameAddresses = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $surveyMainBuilding = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $nbSurveyOtherBuildings = null;

    /**
     * @var Collection<int, SimulationBattery>
     */
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: SimulationBattery::class)]
    private Collection $simulationBatteries;

    /**
     * @var Collection<int, SimulationChargingPoint>
     */
    #[ORM\OneToMany(mappedBy: 'simulation', targetEntity: SimulationChargingPoint::class)]
    private Collection $simulationChargingPoints;

    #[Groups(['simulation:read', 'simulation:write', 'simulationAll:read', 'file:export'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $signedAt = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $consumptionQuantityDetailed = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $consumptionPriceDetailed = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $seasonalPrices = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $subcriptionAnnualPrice = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $forcedSalesPrice = null;

    #[Groups(['simulation:read', 'simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $estimatedConnectionCost = null;

    public function __construct()
    {
        $this->simulationItems = new ArrayCollection();
        $this->histories = new ArrayCollection();
        $this->statusSimulations = new ArrayCollection();
        $this->signatureSimulations = new ArrayCollection();
        $this->fileSimulations = new ArrayCollection();
        $this->sellerBonuses = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->surveySimulations = new ArrayCollection();
        $this->taskSimulations = new ArrayCollection();
        $this->buildings = new ArrayCollection();
        $this->simulationBatteries = new ArrayCollection();
        $this->simulationChargingPoints = new ArrayCollection();
    }

    use TimeStampTrait;

    public function getNumber(): ?string
    {
        if (null === $this->number && null !== $this->getProfile()) {
            return $this->createNumber();
        }
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getOwnedBy(): ?User
    {
        return $this->ownedBy;
    }

    public function setOwnedBy(?User $ownedBy): static
    {
        $this->ownedBy = $ownedBy;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getConsumptionQuantity(): ?float
    {
        return $this->consumptionQuantity;
    }

    public function setConsumptionQuantity(?float $consumptionQuantity): static
    {
        $this->consumptionQuantity = $consumptionQuantity;

        return $this;
    }

    public function getConsumptionPrice(): ?float
    {
        return $this->consumptionPrice;
    }

    public function setConsumptionPrice(?float $consumptionPrice): static
    {
        $this->consumptionPrice = $consumptionPrice;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

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

    public function getReducedYield(): ?float
    {
        return $this->reducedYield;
    }

    public function setReducedYield(?float $reducedYield): static
    {
        $this->reducedYield = $reducedYield;

        return $this;
    }

    public function getPriceEscalation(): ?float
    {
        return $this->priceEscalation;
    }

    public function setPriceEscalation(?float $priceEscalation): static
    {
        $this->priceEscalation = $priceEscalation;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getInstallationPriceHT(): ?float
    {
        return $this->installationPriceHT;
    }

    public function setInstallationPriceHT(?float $installationPriceHT): static
    {
        $this->installationPriceHT = $installationPriceHT;

        return $this;
    }

    #[Groups(['simulation:read', 'simulationAll:read', 'file:export'])]
    public function getCustomerName(): string|null
    {
        if (null !== $this->getCustomer()) {
            return $this->getCustomer()->getFullName();
        } elseif (null !== $this->getTempCustomer()) {
            return $this->getTempCustomer()->getFullName();
        }
        return 'Non défini';
    }

    #[Groups(['simulation:read', 'simulationAll:read',])]
    public function getDisplayConsumption(): string
    {
        if (null === $this->getConsumptionPrice() || null === $this->getConsumptionQuantity()) {
            return 'Informations incomplètes';
        }
        return number_format(
                num: $this->getConsumptionQuantity(),
                thousands_separator: ' '
            ) .
            ' kWh - ' . $this->getConsumptionPrice() . '€/kWh';
    }

    #[Groups(['simulation:read', 'simulationAll:read'])]
    public function getCustomerInfos(): array
    {
        // TODO : Améliorer remplissage de l'array
        if (null !== $this->getCustomer()) {
            $customer = $this->getCustomer();
//            $pdl = $this->getPdl();
            return [
                'customer_id' => $customer->getId(),
                'tempCustomer_id' => null,
                'customerType' => $customer?->getCustomerTypeName(),
                'civilite' => $customer?->getCivility(),
                'fullname' => $this?->getCustomerName(),
                'siret' => $customer?->getSiret(),
                'firstname' => $customer?->getFirstname(),
                'lastname' => $customer?->getLastname(),
                'position' => null,
                'email' => $customer?->getEmail(),
                'phone' => $customer?->getPhoneNumber(),
//                'pdlNumber' => $pdl?->getPdlNumber(),
                'streetNumber' => $pdl?->getStreetNumber(),
                'streetName' => $pdl?->getStreetName(),
                'streetPostcode' => $pdl?->getStreetPostCode(),
                'streetCity' => $pdl?->getStreetCity(),
                'idPdl' => $pdl?->getId(),
                'idCustomer' => $customer?->getId(),
                'isTempCustomer' => false,
                'companyType' => $customer?->getCompanyType()?->getName(),
                'companyTypeFullname' => $customer?->getCompanyType()?->getFullname(),
                'canDeductVAT' => $customer?->isCanDeductVAT(),
            ];
        } elseif (null !== $this->getTempCustomer()) {
            $customer = $this->getTempCustomer();
            return [
                'customer_id' => null,
                'tempCustomer_id' => $customer->getId(),
                'customerType' => $customer?->getCustomerTypeName(),
                'civilite' => $customer?->getCivility(),
                'fullname' => $this?->getCustomerName(),
                'siret' => $customer?->getSiret(),
                'firstname' => $customer?->getFirstname(),
                'lastname' => $customer?->getLastname(),
                'position' => $customer?->getPosition(),
                'email' => $customer?->getEmail(),
                'phone' => $customer?->getPhoneNumber(),
//                'pdlNumber' => $customer?->getPdlNumber(),
                'streetNumber' => $customer?->getStreetNumber(),
                'streetName' => $customer?->getStreetName(),
                'streetPostcode' => $customer?->getStreetPostcode(),
                'streetCity' => $customer?->getStreetCity(),
                'idPdl' => null,
                'idCustomer' => $customer?->getId(),
                'isTempCustomer' => true,
                'companyType' => $customer?->getCompanyType()?->getName(),
                'companyTypeFullname' => $customer?->getCompanyType()?->getFullname(),
                'canDeductVAT' => $customer?->isCanDeductVAT(),
            ];
        }
        return [
            'tempCustomer_id' => null,
            'customer_id' => null,
            'customerType' => null,
            'civilite' => null,
            'fullname' => null,
            'firstname' => null,
            'lastname' => null,
            'position' => null,
            'email' => null,
            'phone' => null,
//            'pdlNumber' => null,
            'streetNumber' => null,
            'streetName' => null,
            'streetPostcode' => null,
            'streetCity' => null,
            'idPdl' => null,
            'idCustomer' => null,
            'isTempCustomer' => null,
            'companyType' => null,
            'companyTypeFullname' => null,
            'canDeductVAT' => null,
        ];
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
            $simulationItem->setSimulation($this);
        }

        return $this;
    }

    public function removeSimulationItem(SimulationItem $simulationItem): static
    {
        if ($this->simulationItems->removeElement($simulationItem)) {
            // set the owning side to null (unless already changed)
            if ($simulationItem->getSimulation() === $this) {
                $simulationItem->setSimulation(null);
            }
        }

        return $this;
    }

    public function getInstallationLocation(): ?string
    {
        /*        if (!$this->installationLocation) {
                    $buildings = $this->buildings;
                    foreach ($buildings as $building) {
                        $options = $building->getSimulationOptions();
                        foreach ($options as $option) {
                            return $option->getInstallationLocation();
                        }
                    }
                }*/
        return $this->installationLocation;
    }

    public function setInstallationLocation(?string $installationLocation): static
    {
        $this->installationLocation = $installationLocation;

        return $this;
    }

    #[Groups(['simulation:read', 'simulationAll:read', 'file:export'])]
    public function getNbPanelsTotal(): int
    {
        return array_reduce(
            $this->simulationItems->toArray(),
            fn($total, $simulationItem) => $total + $simulationItem->getNbPanel(),
            0
        );
    }

    #[Groups(['simulation:read', 'simulationAll:read',])]
    public function getTotalPower(): float
    {
        if ($this->getPanel()) {
            return ($this->getNbPanelsTotal() * $this->getPanel()->getPower()) / 1000;
        }
        return 0;
    }

    #[Groups(['simulation:read'])]
    public function getBonusAmount(): float|null
    {
        if ($this->getProfile()) {
            return Calculator::getBonus(self::getTotalPower(), $this->getProfile()->isIsEligibleForBonus(), $this->getCreatedAt());
        }
        return null;
    }

    public function createNumber(): string
    {
        $identifierZone = $this->getProfile()->getIdentifier();
        $dateNumber = $this->getCreatedAt()->format('ym');
        $increment = '0001';

        return "$identifierZone-$dateNumber-$increment";
    }

    public function isIsArdoiseClouee(): ?bool
    {
        return $this->isArdoiseClouee;
    }

    public function setIsArdoiseClouee(?bool $isArdoiseClouee): static
    {
        $this->isArdoiseClouee = $isArdoiseClouee;

        return $this;
    }

    public function getNbToitures(): ?int
    {
        return $this->nbToitures;
    }

    public function setNbToitures(?int $nbToitures): static
    {
        $this->nbToitures = $nbToitures;

        return $this;
    }

    public function getNbCharpentesNonVisibles(): ?int
    {
        return $this->nbCharpentesNonVisibles;
    }

    public function setNbCharpentesNonVisibles(?int $nbCharpentesNonVisibles): static
    {
        $this->nbCharpentesNonVisibles = $nbCharpentesNonVisibles;

        return $this;
    }

    public function getNbCharpentesBeton(): ?int
    {
        return $this->nbCharpentesBeton;
    }

    public function setNbCharpentesBeton(?int $nbCharpentesBeton): static
    {
        $this->nbCharpentesBeton = $nbCharpentesBeton;

        return $this;
    }

    public function getFinalPriceHT(): ?float
    {
        return $this->finalPriceHT;
    }

    public function setFinalPriceHT(?float $finalPriceHT): static
    {
        $this->finalPriceHT = $finalPriceHT;

        return $this;
    }

    public function getManualPrice(): ?float
    {
        return $this->manualPrice;
    }

    public function setManualPrice(?float $manualPrice): static
    {
        $this->manualPrice = $manualPrice;

        return $this;
    }

    /**
     * @return Collection<int, History>
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(History $history): static
    {
        if (!$this->histories->contains($history)) {
            $this->histories->add($history);
            $history->setSimulation($this);
        }

        return $this;
    }

    public function removeHistory(History $history): static
    {
        if ($this->histories->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getSimulation() === $this) {
                $history->setSimulation(null);
            }
        }

        return $this;
    }

    public function getSalesPriceEscalation(): ?float
    {
        return $this->salesPriceEscalation;
    }

    public function setSalesPriceEscalation(?float $salesPriceEscalation): static
    {
        $this->salesPriceEscalation = $salesPriceEscalation;

        return $this;
    }

    /**
     * @return Collection<int, StatusSimulation>
     */
    public function getStatusSimulations(): Collection
    {
        return $this->statusSimulations;
    }

    public function addStatusSimulation(StatusSimulation $statusSimulation): static
    {
        if (!$this->statusSimulations->contains($statusSimulation)) {
            $this->statusSimulations->add($statusSimulation);
            $statusSimulation->setSimulation($this);
        }

        return $this;
    }

    public function removeStatusSimulation(StatusSimulation $statusSimulation): static
    {
        if ($this->statusSimulations->removeElement($statusSimulation)) {
            // set the owning side to null (unless already changed)
            if ($statusSimulation->getSimulation() === $this) {
                $statusSimulation->setSimulation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SignatureSimulation>
     */
    public function getSignatureSimulations(): Collection
    {
        return $this->signatureSimulations;
    }

    public function addSignatureSimulation(SignatureSimulation $signatureSimulation): static
    {
        if (!$this->signatureSimulations->contains($signatureSimulation)) {
            $this->signatureSimulations->add($signatureSimulation);
            $signatureSimulation->setSimulation($this);
        }

        return $this;
    }

    public function removeSignatureSimulation(SignatureSimulation $signatureSimulation): static
    {
        if ($this->signatureSimulations->removeElement($signatureSimulation)) {
            // set the owning side to null (unless already changed)
            if ($signatureSimulation->getSimulation() === $this) {
                $signatureSimulation->setSimulation(null);
            }
        }

        return $this;
    }

    #[Groups(['simulation:read', 'simulationAll:read', 'file:export'])]
    public function getLatestStatus(): StatusSimulation|null
    {
        if (count($this->statusSimulations) > 0) {
            return $this->statusSimulations[0];
        }
        return null;
    }

    #[Groups(['simulation:read', 'simulationAll:read',])]
    public function getIsExpired(): bool
    {
        return $this->getCreatedAt()->add(\DateInterval::createFromDateString('1 month'))->getTimestamp() < (new \DateTime())->getTimestamp();
    }

    #[Groups(['simulation:read', 'simulationAll:read'])]
    public function getIsSigned(): bool
    {
        if ($this->getSignedAt() !== null) {
            return true;
        }

        /* foreach ($this->signatureSimulations as $signature) {
             if ($signature->getPurpose() === 'devis') {
                 return true;
             }
         }
         foreach ($this->getStatusSimulations() as $status) {
             if ($status->getStatus()->getId() === 4) {
                 return true;
             }
         }*/
        return false;
    }

    /**
     * @return Collection<int, FileSimulation>
     */
    public function getFileSimulations(): Collection
    {
        return $this->fileSimulations;
    }

    public function addFileSimulation(FileSimulation $fileSimulation): static
    {
        if (!$this->fileSimulations->contains($fileSimulation)) {
            $this->fileSimulations->add($fileSimulation);
            $fileSimulation->setSimulation($this);
        }

        return $this;
    }

    public function removeFileSimulation(FileSimulation $fileSimulation): static
    {
        if ($this->fileSimulations->removeElement($fileSimulation)) {
            // set the owning side to null (unless already changed)
            if ($fileSimulation->getSimulation() === $this) {
                $fileSimulation->setSimulation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SellerBonus>
     */
    public function getSellerBonuses(): Collection
    {
        return $this->sellerBonuses;
    }

    public function addSellerBonus(SellerBonus $sellerBonus): static
    {
        if (!$this->sellerBonuses->contains($sellerBonus)) {
            $this->sellerBonuses->add($sellerBonus);
            $sellerBonus->setSimulation($this);
        }

        return $this;
    }

    public function removeSellerBonus(SellerBonus $sellerBonus): static
    {
        if ($this->sellerBonuses->removeElement($sellerBonus)) {
            // set the owning side to null (unless already changed)
            if ($sellerBonus->getSimulation() === $this) {
                $sellerBonus->setSimulation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Survey>
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): static
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys->add($survey);
            $survey->setSimulation($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): static
    {
        if ($this->surveys->removeElement($survey)) {
            // set the owning side to null (unless already changed)
            if ($survey->getSimulation() === $this) {
                $survey->setSimulation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SurveySimulation>
     */
    public function getSurveySimulations(): Collection
    {
        return $this->surveySimulations;
    }

    public function addSurveySimulation(SurveySimulation $surveySimulation): static
    {
        if (!$this->surveySimulations->contains($surveySimulation)) {
            $this->surveySimulations->add($surveySimulation);
            $surveySimulation->setSimulation($this);
        }

        return $this;
    }

    public function removeSurveySimulation(SurveySimulation $surveySimulation): static
    {
        if ($this->surveySimulations->removeElement($surveySimulation)) {
            // set the owning side to null (unless already changed)
            if ($surveySimulation->getSimulation() === $this) {
                $surveySimulation->setSimulation(null);
            }
        }

        return $this;
    }

    public function getReducedYieldFirstYear(): ?float
    {
        return $this->reducedYieldFirstYear;
    }

    public function setReducedYieldFirstYear(?float $reducedYieldFirstYear): static
    {
        $this->reducedYieldFirstYear = $reducedYieldFirstYear;

        return $this;
    }

    /**
     * @return Collection<int, TaskSimulation>
     */
    public function getTaskSimulations(): Collection
    {
        return $this->taskSimulations;
    }

    public function addTaskSimulation(TaskSimulation $taskSimulation): static
    {
        if (!$this->taskSimulations->contains($taskSimulation)) {
            $this->taskSimulations->add($taskSimulation);
            $taskSimulation->setSimulation($this);
        }

        return $this;
    }

    public function removeTaskSimulation(TaskSimulation $taskSimulation): static
    {
        if ($this->taskSimulations->removeElement($taskSimulation)) {
            // set the owning side to null (unless already changed)
            if ($taskSimulation->getSimulation() === $this) {
                $taskSimulation->setSimulation(null);
            }
        }

        return $this;
    }

    public function isIsForcePanelType(): ?bool
    {
        return $this->isForcePanelType;
    }

    public function setIsForcePanelType(?bool $isForcePanelType): static
    {
        $this->isForcePanelType = $isForcePanelType;

        return $this;
    }

    public function getInstallationStreetNumber(): ?string
    {
        return $this->installationStreetNumber;
    }

    public function setInstallationStreetNumber(?string $installationStreetNumber): static
    {
        $this->installationStreetNumber = $installationStreetNumber;

        return $this;
    }

    public function getInstallationStreetName(): ?string
    {
        return $this->installationStreetName;
    }

    public function setInstallationStreetName(?string $installationStreetName): static
    {
        $this->installationStreetName = $installationStreetName;

        return $this;
    }

    public function getInstallationStreetPostcode(): ?string
    {
        return $this->installationStreetPostcode;
    }

    public function setInstallationStreetPostcode(?string $installationStreetPostcode): static
    {
        $this->installationStreetPostcode = $installationStreetPostcode;

        return $this;
    }

    public function getInstallationStreetCity(): ?string
    {
        return $this->installationStreetCity;
    }

    public function setInstallationStreetCity(?string $installationStreetCity): static
    {
        $this->installationStreetCity = $installationStreetCity;

        return $this;
    }

    /**
     * @return Collection<int, Building>
     */
    public function getBuildings(): Collection
    {
        return $this->buildings;
    }

    public function addBuilding(Building $building): static
    {
        if (!$this->buildings->contains($building)) {
            $this->buildings->add($building);
            $building->setSimulation($this);
        }

        return $this;
    }

    public function removeBuilding(Building $building): static
    {
        if ($this->buildings->removeElement($building)) {
            // set the owning side to null (unless already changed)
            if ($building->getSimulation() === $this) {
                $building->setSimulation(null);
            }
        }

        return $this;
    }

    public function isIsSameAddresses(): ?bool
    {
        return $this->isSameAddresses;
    }

    public function setIsSameAddresses(?bool $isSameAddresses): static
    {
        $this->isSameAddresses = $isSameAddresses;

        return $this;
    }

    public function isSurveyMainBuilding(): ?bool
    {
        return $this->surveyMainBuilding;
    }

    public function setSurveyMainBuilding(?bool $surveyMainBuilding): static
    {
        $this->surveyMainBuilding = $surveyMainBuilding;

        return $this;
    }

    public function getNbSurveyOtherBuildings(): ?int
    {
        return $this->nbSurveyOtherBuildings;
    }

    public function setNbSurveyOtherBuildings(?int $nbSurveyOtherBuildings): static
    {
        $this->nbSurveyOtherBuildings = $nbSurveyOtherBuildings;

        return $this;
    }

    /**
     * @return Collection<int, SimulationBattery>
     */
    public function getSimulationBatteries(): Collection
    {
        return $this->simulationBatteries;
    }

    public function addSimulationBattery(SimulationBattery $simulationBattery): static
    {
        if (!$this->simulationBatteries->contains($simulationBattery)) {
            $this->simulationBatteries->add($simulationBattery);
            $simulationBattery->setSimulation($this);
        }

        return $this;
    }

    public function removeSimulationBattery(SimulationBattery $simulationBattery): static
    {
        if ($this->simulationBatteries->removeElement($simulationBattery)) {
            // set the owning side to null (unless already changed)
            if ($simulationBattery->getSimulation() === $this) {
                $simulationBattery->setSimulation(null);
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
            $simulationChargingPoint->setSimulation($this);
        }

        return $this;
    }

    public function removeSimulationChargingPoint(SimulationChargingPoint $simulationChargingPoint): static
    {
        if ($this->simulationChargingPoints->removeElement($simulationChargingPoint)) {
            // set the owning side to null (unless already changed)
            if ($simulationChargingPoint->getSimulation() === $this) {
                $simulationChargingPoint->setSimulation(null);
            }
        }

        return $this;
    }

    #[Groups(['simulation:read'])]
    public function getRoofType(): string|null
    {
        $buildings = $this->getBuildings()->toArray();
        if (count($buildings) > 0) {
            $simulationOptions = $buildings[0]->getSimulationOptions()->toArray();
            if (count($simulationOptions) > 0) {
                return $simulationOptions[0]->getRoofType();
            }
        }
        return null;
    }

    public function getSignedAt(): ?\DateTimeImmutable
    {
        return $this->signedAt;
    }

    public function setSignedAt(?\DateTimeImmutable $signedAt): static
    {
        $this->signedAt = $signedAt;

        return $this;
    }

    #[Groups(['simulation:read'])]
    public function getInstallationDepartment(): int|null
    {
        if ($this->isSameAddresses) {
            return $this?->tempCustomer?->getStreetPostcode();
        }
        return $this->installationStreetPostcode;
    }

    public function getConsumptionQuantityDetailed(): ?array
    {
        return $this->consumptionQuantityDetailed;
    }

    public function setConsumptionQuantityDetailed(?array $consumptionQuantityDetailed): static
    {
        $this->consumptionQuantityDetailed = $consumptionQuantityDetailed;

        return $this;
    }

    public function getConsumptionPriceDetailed(): ?array
    {
        return $this->consumptionPriceDetailed;
    }

    public function setConsumptionPriceDetailed(?array $consumptionPriceDetailed): static
    {
        $this->consumptionPriceDetailed = $consumptionPriceDetailed;

        return $this;
    }

    public function isSeasonalPrices(): ?bool
    {
        return $this->seasonalPrices;
    }

    public function setSeasonalPrices(?bool $seasonalPrices): static
    {
        $this->seasonalPrices = $seasonalPrices;

        return $this;
    }

    public function getSubcriptionAnnualPrice(): ?float
    {
        return $this->subcriptionAnnualPrice;
    }

    public function setSubcriptionAnnualPrice(?float $subcriptionAnnualPrice): static
    {
        $this->subcriptionAnnualPrice = $subcriptionAnnualPrice;

        return $this;
    }

    public function getForcedSalesPrice(): ?float
    {
        return $this->forcedSalesPrice;
    }

    public function setForcedSalesPrice(?float $forcedSalesPrice): static
    {
        $this->forcedSalesPrice = $forcedSalesPrice;

        return $this;
    }

    public function getEstimatedConnectionCost(): ?float
    {
        return $this->estimatedConnectionCost;
    }

    public function setEstimatedConnectionCost(?float $estimatedConnectionCost): static
    {
        $this->estimatedConnectionCost = $estimatedConnectionCost;

        return $this;
    }
}
