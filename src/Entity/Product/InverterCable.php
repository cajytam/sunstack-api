<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Product\InverterCableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InverterCableRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['inverterCable:read', 'read:id']],
    denormalizationContext: ['groups' => ['inverterCable:write']],
    security: "is_granted('ROLE_USER')",
)]
class InverterCable
{
    use IdIntTrait;

    #[Groups(['inverter:read', 'inverterCable:read', 'inverterCable:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, InverterCablePrice>
     */
    #[Groups(['inverter:read', 'inverterCable:read'])]
    #[ORM\OneToMany(mappedBy: 'inverterCable', targetEntity: InverterCablePrice::class)]
    private Collection $inverterCablePrices;

    /**
     * @var Collection<int, Inverter>
     */
    #[ORM\OneToMany(mappedBy: 'inverterCable', targetEntity: Inverter::class)]
    private Collection $inverters;

    public function __construct()
    {
        $this->inverterCablePrices = new ArrayCollection();
        $this->inverters = new ArrayCollection();
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

    /**
     * @return Collection<int, InverterCablePrice>
     */
    public function getInverterCablePrices(): Collection
    {
        return $this->inverterCablePrices;
    }

    public function addInverterCablePrice(InverterCablePrice $inverterCablePrice): static
    {
        if (!$this->inverterCablePrices->contains($inverterCablePrice)) {
            $this->inverterCablePrices->add($inverterCablePrice);
            $inverterCablePrice->setInverterCable($this);
        }

        return $this;
    }

    public function removeInverterCablePrice(InverterCablePrice $inverterCablePrice): static
    {
        if ($this->inverterCablePrices->removeElement($inverterCablePrice)) {
            // set the owning side to null (unless already changed)
            if ($inverterCablePrice->getInverterCable() === $this) {
                $inverterCablePrice->setInverterCable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inverter>
     */
    public function getInverters(): Collection
    {
        return $this->inverters;
    }

    public function addInverter(Inverter $inverter): static
    {
        if (!$this->inverters->contains($inverter)) {
            $this->inverters->add($inverter);
            $inverter->setInverterCable($this);
        }

        return $this;
    }

    public function removeInverter(Inverter $inverter): static
    {
        if ($this->inverters->removeElement($inverter)) {
            // set the owning side to null (unless already changed)
            if ($inverter->getInverterCable() === $this) {
                $inverter->setInverterCable(null);
            }
        }

        return $this;
    }

    #[Groups(['inverter:read', 'inverterCable:read'])]
    public function getCurrentPrice(\DateTimeImmutable $currentDate = new \DateTimeImmutable()): ArrayCollection|Collection
    {
        return $this->inverterCablePrices->filter(
            function (InverterCablePrice $price) use ($currentDate) {
                return ($currentDate >= $price->getStartDate() && ($currentDate <= $price->getEndDate() || $price->getEndDate() === null));
            }
        );
    }
}
