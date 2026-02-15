<?php

namespace App\Entity\Calculation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Enum\InstallationPlace;
use App\Entity\Enum\InstallationType;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Calculation\PriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection()
    ],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'nbPanels' => 'exact',
        'installationType' => 'exact',
        'place' => 'exact'
    ]
)]
#[ORM\Entity(repositoryClass: PriceRepository::class)]
class Price
{
    use IdIntTrait;

    #[ORM\Column]
    private ?int $nbPanels = null;

    #[ORM\Column]
    private ?float $priceBasic = null;

    #[ORM\Column(nullable: true)]
    private ?float $priceDiscounted_1 = null;

    #[ORM\Column(nullable: true)]
    private ?float $priceDiscounted_2 = null;

    #[ORM\Column(nullable: true)]
    private ?float $priceDiscounted_3 = null;

    #[ORM\Column(nullable: true)]
    private ?float $priceDiscounted_4 = null;

    #[Assert\Choice(
        callback: [InstallationType::class, 'values'],
        message: 'La valeur doit être soit "C" pour Commercial ou "I" pour Industrie'
    )]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $installationType = null;

    #[Assert\Choice(
        callback: [InstallationPlace::class, 'values'],
        message: 'La valeur doit être soit "T" pour Toit ou "S" pour Sol'
    )]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $place = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEligibleToBonus = null;

    public function getNbPanels(): ?int
    {
        return $this->nbPanels;
    }

    public function setNbPanels(int $nbPanels): static
    {
        $this->nbPanels = $nbPanels;

        return $this;
    }

    public function getPriceBasic(): ?float
    {
        return $this->priceBasic;
    }

    public function setPriceBasic(float $priceBasic): static
    {
        $this->priceBasic = $priceBasic;

        return $this;
    }

    public function getPriceDiscounted1(): ?float
    {
        return $this->priceDiscounted_1;
    }

    public function setPriceDiscounted1(?float $priceDiscounted_1): static
    {
        $this->priceDiscounted_1 = $priceDiscounted_1;

        return $this;
    }

    public function getPriceDiscounted2(): ?float
    {
        return $this->priceDiscounted_2;
    }

    public function setPriceDiscounted2(?float $priceDiscounted_2): static
    {
        $this->priceDiscounted_2 = $priceDiscounted_2;

        return $this;
    }

    public function getPriceDiscounted3(): ?float
    {
        return $this->priceDiscounted_3;
    }

    public function setPriceDiscounted3(?float $priceDiscounted_3): static
    {
        $this->priceDiscounted_3 = $priceDiscounted_3;

        return $this;
    }

    public function getPriceDiscounted4(): ?float
    {
        return $this->priceDiscounted_4;
    }

    public function setPriceDiscounted4(?float $priceDiscounted_4): static
    {
        $this->priceDiscounted_4 = $priceDiscounted_4;

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

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function isIsEligibleToBonus(): ?bool
    {
        return $this->isEligibleToBonus;
    }

    public function setIsEligibleToBonus(?bool $isEligibleToBonus): static
    {
        $this->isEligibleToBonus = $isEligibleToBonus;

        return $this;
    }
}
