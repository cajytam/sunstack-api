<?php

namespace App\Entity\Calculation;

use App\Entity\Product\Panel;
use App\Entity\Simulation\Profile;

class Site
{
    private ?string $id = null;
    private ?Profile $profile = null;
    private ?string $name = null;
    private ?float $installationPrice = null;
    private ?float $energyPrice = null;
    private ?float $energyConsumption = null;
    private ?float $zoneIndex = null;
    private ?Panel $panel = null;
    /** @var Index[] */
    private array $indexes = [];
    /** @var Fees[] */
    private array $fees = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): void
    {
        $this->profile = $profile;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getInstallationPrice(): ?float
    {
        return $this->installationPrice;
    }

    public function setInstallationPrice(?float $installationPrice): void
    {
        $this->installationPrice = $installationPrice;
    }

    public function getEnergyPrice(): ?float
    {
        return $this->energyPrice;
    }

    public function setEnergyPrice(?float $energyPrice): void
    {
        $this->energyPrice = $energyPrice;
    }

    public function getEnergyConsumption(): ?float
    {
        return $this->energyConsumption;
    }

    public function setEnergyConsumption(?float $energyConsumption): void
    {
        $this->energyConsumption = $energyConsumption;
    }

    public function getZoneIndex(): ?float
    {
        return $this->zoneIndex;
    }

    public function setZoneIndex(?float $zoneIndex): void
    {
        $this->zoneIndex = $zoneIndex;
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

    public function getIndex(int $year): ?Index
    {
        foreach ($this->indexes as $index) {
            if ($index->getYear() === $year) {
                return $index;
            }
        }
        return null;
    }

    /**
     * @return Index[]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param Index[] $indexes
     */
    public function setIndexes(array $indexes): void
    {
        $this->indexes = $indexes;
    }

    /**
     * @return Fees[]
     */
    public function getFees(): array
    {
        return $this->fees;
    }

    /**
     * @param Fees[] $fees
     */
    public function setFees(array $fees): void
    {
        $this->fees = $fees;
    }
}
