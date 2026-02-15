<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Product\InverterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InverterRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['inverter:read', 'read:id']],
    denormalizationContext: ['groups' => ['inverter:write']],
    security: "is_granted('ROLE_USER')",
)]
class Inverter
{
    const RATIO_SP = 2;
    const RATIO_TP_IND = 1.5;
    const RATIO_TP_RES = 1.6;

    use IdIntTrait;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $brand = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $power = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $type = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $electricalPhase = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $nbMppt = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $typeInverter = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $maxInputPower = null;

    #[Groups(['inverter:read'])]
    #[ORM\OneToMany(mappedBy: 'inverter', targetEntity: InverterPrice::class)]
    private Collection $inverterPrices;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $nbStringPerMppt = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $mpptVoltageRangeMin = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $mpptVoltageRangeMax = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $maxDcInputVoltage = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isDcProtectionIntegrated = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $maxDcInputCurrentMppt = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $maxShortCircuitCurrentMppt = null;

    #[Groups(['inverter:read', 'inverter:write'])]
    #[ORM\ManyToOne(inversedBy: 'inverters')]
    private ?InverterCable $inverterCable = null;

    public function __construct()
    {
        $this->inverterPrices = new ArrayCollection();
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getPower(): ?float
    {
        return $this->power;
    }

    public function setPower(?float $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getElectricalPhase(): ?string
    {
        return $this->electricalPhase;
    }

    public function setElectricalPhase(?string $electricalPhase): static
    {
        $this->electricalPhase = $electricalPhase;

        return $this;
    }

    public function getMaxPVPower(): float
    {
        // si un maxInputPower est renseigné
        if ($this->getMaxInputPower()) {
            // s'il est < 5, c'est qu'il s'agit d'un index
            if ($this->getMaxInputPower() < 5) {
                return $this->getPower() * $this->getMaxInputPower() * 1000;
            }
            // sinon on prend directement la puissance max
            return $this->getMaxInputPower();
        }
        return $this->getPower() * ($this->getElectricalPhase() === 'TP' ? ($this->getType() === 'I' ? static::RATIO_TP_IND : static::RATIO_TP_RES) : static::RATIO_SP);
    }

    public function getNbMppt(): ?int
    {
        return $this->nbMppt;
    }

    public function setNbMppt(?int $nbMppt): static
    {
        $this->nbMppt = $nbMppt;

        return $this;
    }

    public function getTypeInverter(): ?string
    {
        return $this->typeInverter;
    }

    public function setTypeInverter(?string $typeInverter): static
    {
        $this->typeInverter = $typeInverter;

        return $this;
    }

    public function getMaxInputPower(): ?float
    {
        return $this->maxInputPower;
    }

    public function setMaxInputPower(?float $maxInputPower): static
    {
        $this->maxInputPower = $maxInputPower;

        return $this;
    }

    /**
     * @return Collection<int, InverterPrice>
     */
    public function getInverterPrices(): Collection
    {
        return $this->inverterPrices;
    }

    public function addInverterPrice(InverterPrice $inverterPrice): static
    {
        if (!$this->inverterPrices->contains($inverterPrice)) {
            $this->inverterPrices->add($inverterPrice);
            $inverterPrice->setInverter($this);
        }

        return $this;
    }

    public function removeInverterPrice(InverterPrice $inverterPrice): static
    {
        if ($this->inverterPrices->removeElement($inverterPrice)) {
            // set the owning side to null (unless already changed)
            if ($inverterPrice->getInverter() === $this) {
                $inverterPrice->setInverter(null);
            }
        }

        return $this;
    }

    #[Groups(['inverter:read'])]
    public function getCurrentPrice(\DateTimeImmutable $currentDate = new \DateTimeImmutable()): ArrayCollection|Collection
    {
        return $this->inverterPrices->filter(
            function (InverterPrice $price) use ($currentDate) {
                return ($currentDate >= $price->getStartDate() && ($currentDate <= $price->getEndDate() || $price->getEndDate() === null)) && $price->getType() === null;
            }
        );
    }

    #[Groups(['inverter:read'])]
    public function getCurrentPriceOptions(\DateTimeImmutable $currentDate = new \DateTimeImmutable()): ArrayCollection|Collection
    {
        return $this->inverterPrices->filter(
            function (InverterPrice $price) use ($currentDate) {
                return ($currentDate >= $price->getStartDate() && ($currentDate <= $price->getEndDate() || $price->getEndDate() === null)) && $price->getType() === 'options';
            }
        );
    }

    #[Groups(['inverter:read'])]
    public function getCurrentPriceProtectionBoxNonERP(\DateTimeImmutable $currentDate = new \DateTimeImmutable()): ArrayCollection|Collection
    {
        return $this->inverterPrices->filter(
            function (InverterPrice $price) use ($currentDate) {
                return ($currentDate >= $price->getStartDate() && ($currentDate <= $price->getEndDate() || $price->getEndDate() === null)) && $price->getType() === 'coffret_non_erp';
            }
        );
    }

    #[Groups(['inverter:read'])]
    public function getCurrentPriceProtectionBoxERP(\DateTimeImmutable $currentDate = new \DateTimeImmutable()): ArrayCollection|Collection
    {
        return $this->inverterPrices->filter(
            function (InverterPrice $price) use ($currentDate) {
                return ($currentDate >= $price->getStartDate() && ($currentDate <= $price->getEndDate() || $price->getEndDate() === null)) && $price->getType() === 'coffret_erp';
            }
        );
    }

    public function getNbStringPerMppt(): ?int
    {
        return $this->nbStringPerMppt;
    }

    public function setNbStringPerMppt(?int $nbStringPerMppt): static
    {
        $this->nbStringPerMppt = $nbStringPerMppt;

        return $this;
    }

    public function getMpptVoltageRangeMin(): ?int
    {
        return $this->mpptVoltageRangeMin;
    }

    public function setMpptVoltageRangeMin(?int $mpptVoltageRangeMin): static
    {
        $this->mpptVoltageRangeMin = $mpptVoltageRangeMin;

        return $this;
    }

    public function getMpptVoltageRangeMax(): ?int
    {
        return $this->mpptVoltageRangeMax;
    }

    public function setMpptVoltageRangeMax(?int $mpptVoltageRangeMax): static
    {
        $this->mpptVoltageRangeMax = $mpptVoltageRangeMax;

        return $this;
    }

    public function getMaxDcInputVoltage(): ?int
    {
        return $this->maxDcInputVoltage;
    }

    public function setMaxDcInputVoltage(?int $maxDcInputVoltage): static
    {
        $this->maxDcInputVoltage = $maxDcInputVoltage;

        return $this;
    }

    public function isIsDcProtectionIntegrated(): ?bool
    {
        return $this->isDcProtectionIntegrated;
    }

    public function setIsDcProtectionIntegrated(?bool $isDcProtectionIntegrated): static
    {
        $this->isDcProtectionIntegrated = $isDcProtectionIntegrated;

        return $this;
    }

    public function getMaxDcInputCurrentMppt(): ?int
    {
        return $this->maxDcInputCurrentMppt;
    }

    public function setMaxDcInputCurrentMppt(?int $maxDcInputCurrentMppt): static
    {
        $this->maxDcInputCurrentMppt = $maxDcInputCurrentMppt;

        return $this;
    }

    public function getMaxShortCircuitCurrentMppt(): ?int
    {
        return $this->maxShortCircuitCurrentMppt;
    }

    public function setMaxShortCircuitCurrentMppt(?int $maxShortCircuitCurrentMppt): static
    {
        $this->maxShortCircuitCurrentMppt = $maxShortCircuitCurrentMppt;

        return $this;
    }

    public function getInverterCable(): ?InverterCable
    {
        return $this->inverterCable;
    }

    public function setInverterCable(?InverterCable $inverterCable): static
    {
        $this->inverterCable = $inverterCable;

        return $this;
    }
}
