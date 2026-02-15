<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\SimulationBattery;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Product\BatteryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BatteryRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['battery:read', 'read:id']],
    denormalizationContext: ['groups' => ['battery:write']],
    security: "is_granted('ROLE_USER')",
)]
class Battery
{
    use IdIntTrait;

    #[Groups(['battery:read', 'battery:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $brand = null;

    #[Groups(['battery:read', 'battery:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[Groups(['battery:read', 'battery:write'])]
    #[ORM\Column(nullable: true)]
    private ?float $capacity = null;

    /**
     * @var Collection<int, BatteryPrice>
     */
    #[Groups(['battery:read'])]
    #[ORM\OneToMany(mappedBy: 'battery', targetEntity: BatteryPrice::class)]
    private Collection $batteryPrices;

    /**
     * @var Collection<int, SimulationBattery>
     */
    #[ORM\OneToMany(mappedBy: 'battery', targetEntity: SimulationBattery::class)]
    private Collection $simulationBatteries;

    public function __construct()
    {
        $this->batteryPrices = new ArrayCollection();
        $this->simulationBatteries = new ArrayCollection();
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

    public function getCapacity(): ?float
    {
        return $this->capacity;
    }

    public function setCapacity(?float $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * @return Collection<int, BatteryPrice>
     */
    public function getBatteryPrices(): Collection
    {
        return $this->batteryPrices;
    }

    public function addBatteryPrice(BatteryPrice $batteryPrice): static
    {
        if (!$this->batteryPrices->contains($batteryPrice)) {
            $this->batteryPrices->add($batteryPrice);
            $batteryPrice->setBattery($this);
        }

        return $this;
    }

    public function removeBatteryPrice(BatteryPrice $batteryPrice): static
    {
        if ($this->batteryPrices->removeElement($batteryPrice)) {
            // set the owning side to null (unless already changed)
            if ($batteryPrice->getBattery() === $this) {
                $batteryPrice->setBattery(null);
            }
        }

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
            $simulationBattery->setBattery($this);
        }

        return $this;
    }

    public function removeSimulationBattery(SimulationBattery $simulationBattery): static
    {
        if ($this->simulationBatteries->removeElement($simulationBattery)) {
            // set the owning side to null (unless already changed)
            if ($simulationBattery->getBattery() === $this) {
                $simulationBattery->setBattery(null);
            }
        }

        return $this;
    }

    #[Groups(['battery:read'])]
    public function getCurrentPrice(\DateTimeImmutable $currentDate = new \DateTimeImmutable()): ArrayCollection|Collection
    {
        return $this->batteryPrices->filter(
            function (BatteryPrice $price) use ($currentDate) {
                return ($currentDate >= $price->getStartDate() && ($currentDate <= $price->getEndDate() || $price->getEndDate() === null));
            }
        );
    }
}
