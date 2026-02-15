<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Product\PricingFactorTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PricingFactorTypeRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['pricing_factor_type:read', 'read:id']],
    denormalizationContext: ['groups' => ['pricing_factor_type:write']],
    security: "is_granted('ROLE_USER')",
)]
class PricingFactorType
{
    use IdIntTrait;

    #[Groups(['pricing_factor_type:read', 'pricing_factor_type:write'])]
    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[Groups(['pricing_factor_type:read', 'pricing_factor_type:write'])]
    #[ORM\OneToMany(mappedBy: 'pricingFactor', targetEntity: PricingFactorPrice::class)]
    private Collection $pricingFactorPrices;

    public function __construct()
    {
        $this->pricingFactorPrices = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, PricingFactorPrice>
     */
    public function getPricingFactorPrices(): Collection
    {
        return $this->pricingFactorPrices;
    }

    public function addPricingFactorPrice(PricingFactorPrice $pricingFactorPrice): static
    {
        if (!$this->pricingFactorPrices->contains($pricingFactorPrice)) {
            $this->pricingFactorPrices->add($pricingFactorPrice);
            $pricingFactorPrice->setPricingFactor($this);
        }

        return $this;
    }

    public function removePricingFactorPrice(PricingFactorPrice $pricingFactorPrice): static
    {
        if ($this->pricingFactorPrices->removeElement($pricingFactorPrice)) {
            // set the owning side to null (unless already changed)
            if ($pricingFactorPrice->getPricingFactor() === $this) {
                $pricingFactorPrice->setPricingFactor(null);
            }
        }

        return $this;
    }
}
