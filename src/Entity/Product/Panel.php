<?php

namespace App\Entity\Product;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Product\PanelRepository;
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
    normalizationContext: ['groups' => ['panel:read', 'read:id']],
    denormalizationContext: ['groups' => ['panel:write']],
    security: "is_granted('ROLE_USER')",
)]
#[ApiFilter(
    SearchFilter::class,
    properties: ['installationType' => 'exact']
)]
#[ORM\Entity(repositoryClass: PanelRepository::class)]
class Panel
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $model = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write', 'file:export'])]
    #[ORM\Column(nullable: true)]
    private ?float $power = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $installationType = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $yieldLossFirstYear = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $yieldLossOtherYears = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $debutOnSaleAt = null;

    #[Groups(['panel:read'])]
    #[ORM\OneToMany(mappedBy: 'panel', targetEntity: PanelPrice::class)]
    private Collection $panelPrices;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $voltageOpenCircuit = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $coefVoc = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $shortCircuitCurrent = null;

    #[Groups(['simulation:read', 'panel:read', 'panel:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $coefIsc = null;

    public function __construct()
    {
        $this->panelPrices = new ArrayCollection();
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

    public function getInstallationType(): ?string
    {
        return $this->installationType;
    }

    public function setInstallationType(?string $installationType): static
    {
        $this->installationType = $installationType;

        return $this;
    }

    public function getYieldLossFirstYear(): ?float
    {
        return $this->yieldLossFirstYear;
    }

    public function setYieldLossFirstYear(?float $yieldLossFirstYear): static
    {
        $this->yieldLossFirstYear = $yieldLossFirstYear;

        return $this;
    }

    public function getYieldLossOtherYears(): ?float
    {
        return $this->yieldLossOtherYears;
    }

    public function setYieldLossOtherYears(?float $yieldLossOtherYears): static
    {
        $this->yieldLossOtherYears = $yieldLossOtherYears;

        return $this;
    }

    public function getDebutOnSaleAt(): ?\DateTimeImmutable
    {
        return $this->debutOnSaleAt;
    }

    public function setDebutOnSaleAt(?\DateTimeImmutable $debutOnSaleAt): static
    {
        $this->debutOnSaleAt = $debutOnSaleAt;

        return $this;
    }

    #[Groups(['simulation:read', 'panel:read'])]
    public function getIsActive(): bool
    {
        if ($this->debutOnSaleAt <= new \DateTimeImmutable()) {
            return true;
        }
        return false;
    }

    /**
     * @return Collection<int, PanelPrice>
     */
    public function getPanelPrices(): Collection
    {
        return $this->panelPrices;
    }

    public function addPanelPrice(PanelPrice $panelPrice): static
    {
        if (!$this->panelPrices->contains($panelPrice)) {
            $this->panelPrices->add($panelPrice);
            $panelPrice->setPanel($this);
        }

        return $this;
    }

    public function removePanelPrice(PanelPrice $panelPrice): static
    {
        if ($this->panelPrices->removeElement($panelPrice)) {
            // set the owning side to null (unless already changed)
            if ($panelPrice->getPanel() === $this) {
                $panelPrice->setPanel(null);
            }
        }

        return $this;
    }

    public function getVoltageOpenCircuit(): ?float
    {
        return $this->voltageOpenCircuit;
    }

    public function setVoltageOpenCircuit(?float $voltageOpenCircuit): static
    {
        $this->voltageOpenCircuit = $voltageOpenCircuit;

        return $this;
    }

    public function getCoefVoc(): ?float
    {
        return $this->coefVoc;
    }

    public function setCoefVoc(?float $coefVoc): static
    {
        $this->coefVoc = $coefVoc;

        return $this;
    }

    public function getShortCircuitCurrent(): ?float
    {
        return $this->shortCircuitCurrent;
    }

    public function setShortCircuitCurrent(?float $shortCircuitCurrent): static
    {
        $this->shortCircuitCurrent = $shortCircuitCurrent;

        return $this;
    }

    public function getCoefIsc(): ?float
    {
        return $this->coefIsc;
    }

    public function setCoefIsc(?float $coefIsc): static
    {
        $this->coefIsc = $coefIsc;

        return $this;
    }

    #[Groups(['panel:read'])]
    public function getFullname(): string
    {
        return $this->getModel() . ' (' . $this->getPower() . 'Wc)';
    }

    #[Groups(['panel:read'])]
    public function getCurrentPrice(\DateTimeImmutable $currentDate = new \DateTimeImmutable()): ArrayCollection|Collection
    {
        return $this->panelPrices->filter(
            function (PanelPrice $price) use ($currentDate) {
                return ($currentDate >= $price->getStartDate() && ($currentDate <= $price->getEndDate() || $price->getEndDate() === null));
            }
        );
    }
}
