<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Product\PricingFactorPriceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PricingFactorPriceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['pricing_factor_price:read', 'read:id']],
    denormalizationContext: ['groups' => ['pricing_factor_price:write']],
    security: "is_granted('ROLE_USER')",
)]
class PricingFactorPrice
{
    use IdIntTrait;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $markedPrice = null;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $unmarkedPrice = null;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startAt = null;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endAt = null;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\ManyToOne(inversedBy: 'pricingFactorPrices')]
    private ?PricingFactorType $pricingFactor = null;

    #[Groups(['pricing_factor_type:read'])]
    #[ORM\OneToMany(mappedBy: 'pricingFactorPrice', targetEntity: PricingFactorCondition::class)]
    private Collection $pricingFactorConditions;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\ManyToOne]
    private ?User $addedBy = null;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $addedAt = null;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\ManyToOne]
    private ?User $updatedBy = null;

    #[Groups(['pricing_factor_price:read', 'pricing_factor_price:write', 'pricing_factor_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->pricingFactorConditions = new ArrayCollection();
    }

    public function getMarkedPrice(): ?float
    {
        return $this->markedPrice;
    }

    public function setMarkedPrice(?float $markedPrice): static
    {
        $this->markedPrice = $markedPrice;

        return $this;
    }

    public function getUnmarkedPrice(): ?float
    {
        return $this->unmarkedPrice;
    }

    public function setUnmarkedPrice(?float $unmarkedPrice): static
    {
        $this->unmarkedPrice = $unmarkedPrice;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getPricingFactor(): ?PricingFactorType
    {
        return $this->pricingFactor;
    }

    public function setPricingFactor(?PricingFactorType $pricingFactor): static
    {
        $this->pricingFactor = $pricingFactor;

        return $this;
    }

    /**
     * @return Collection<int, PricingFactorCondition>
     */
    public function getPricingFactorConditions(): Collection
    {
        return $this->pricingFactorConditions;
    }

    public function addPricingFactorCondition(PricingFactorCondition $pricingFactorCondition): static
    {
        if (!$this->pricingFactorConditions->contains($pricingFactorCondition)) {
            $this->pricingFactorConditions->add($pricingFactorCondition);
            $pricingFactorCondition->setPricingFactorPrice($this);
        }

        return $this;
    }

    public function removePricingFactorCondition(PricingFactorCondition $pricingFactorCondition): static
    {
        if ($this->pricingFactorConditions->removeElement($pricingFactorCondition)) {
            // set the owning side to null (unless already changed)
            if ($pricingFactorCondition->getPricingFactorPrice() === $this) {
                $pricingFactorCondition->setPricingFactorPrice(null);
            }
        }

        return $this;
    }

    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    public function setAddedBy(?User $addedBy): static
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(?\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
