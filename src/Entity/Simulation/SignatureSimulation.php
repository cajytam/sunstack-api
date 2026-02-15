<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\SignatureSimulationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SignatureSimulationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['simulation_signature:read', 'read:id', 'timestamp:read']],
    denormalizationContext: ['groups' => ['simulation_signature:write', 'timestamp:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'simulation' => 'exact',
        'purpose' => 'exact'
    ]
)]
class SignatureSimulation
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'simulation_signature:read', 'simulation_signature:write'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'signatureSimulations')]
    private ?Simulation $simulation = null;

    #[Groups(['simulation:read', 'simulation_signature:read', 'simulation_signature:write'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'signatureSimulations')]
    private ?Signature $signature = null;

    #[Groups(['simulation:read', 'simulation_signature:read', 'simulation_signature:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $purpose = null;

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

        return $this;
    }

    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    public function setSignature(?Signature $signature): static
    {
        $this->signature = $signature;

        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): static
    {
        $this->purpose = $purpose;

        return $this;
    }
}
