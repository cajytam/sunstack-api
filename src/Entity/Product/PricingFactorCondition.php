<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Product\PricingFactorConditionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PricingFactorConditionRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['pricing_factor_condition:read', 'read:id']],
    denormalizationContext: ['groups' => ['pricing_factor_condition:write']],
    security: "is_granted('ROLE_USER')",
)]
class PricingFactorCondition
{
    use IdIntTrait;

    #[Groups(['pricing_factor_condition:read', 'pricing_factor_condition:write'])]
    #[ORM\ManyToOne(inversedBy: 'pricingFactorConditions')]
    private ?PricingFactorPrice $pricingFactorPrice = null;

    #[Groups(['pricing_factor_condition:read', 'pricing_factor_condition:write', 'pricing_factor_type:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[Groups(['pricing_factor_condition:read', 'pricing_factor_condition:write', 'pricing_factor_type:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    #[Groups(['pricing_factor_condition:read', 'pricing_factor_condition:write', 'pricing_factor_type:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $limitType = null;

    public function getPricingFactorPrice(): ?PricingFactorPrice
    {
        return $this->pricingFactorPrice;
    }

    public function setPricingFactorPrice(?PricingFactorPrice $pricingFactorPrice): static
    {
        $this->pricingFactorPrice = $pricingFactorPrice;

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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getLimitType(): ?string
    {
        return $this->limitType;
    }

    public function setLimitType(?string $limitType): static
    {
        $this->limitType = $limitType;

        return $this;
    }
}
