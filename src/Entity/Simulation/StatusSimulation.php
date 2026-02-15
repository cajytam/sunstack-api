<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Simulation\StatusSimulationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StatusSimulationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post()
    ],
    normalizationContext: ['groups' => ['status_simulation:read', 'read:id']],
    denormalizationContext: ['groups' => ['status_simulation:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class, properties: [
    'simulation' => 'exact',
])]
class StatusSimulation
{
    use IdIntTrait;

    #[Groups(['status_simulation:write'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'statusSimulations')]
    private ?Simulation $simulation = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'status_simulation:read', 'status_simulation:write', 'file:export'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'statusSimulations')]
    private ?Status $status = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'status_simulation:read', 'status_simulation:write', 'file:export'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['simulation:read', 'status_simulation:read', 'status_simulation:write'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'statusSimulations')]
    private ?User $ownedBy = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'status_simulation:read', 'status_simulation:write', 'file:export'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEvent = null;

    #[Groups(['simulation:read', 'status_simulation:read', 'status_simulation:write', 'file:export'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reasonEvent = null;

    #[Groups(['simulation:read', 'simulationAll:read', 'status_simulation:read', 'status_simulation:write', 'file:export'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $optionSelected = null;

    public function __construct()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTimeImmutable());
        }
    }

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOwnedBy(): ?User
    {
        return $this->ownedBy;
    }

    public function setOwnedBy(?User $ownedBy): static
    {
        $this->ownedBy = $ownedBy;

        return $this;
    }

    public function getDateEvent(): ?\DateTimeInterface
    {
        return $this->dateEvent;
    }

    public function setDateEvent(?\DateTimeInterface $dateEvent): static
    {
        $this->dateEvent = $dateEvent;

        return $this;
    }

    public function getReasonEvent(): ?string
    {
        return $this->reasonEvent;
    }

    public function setReasonEvent(?string $reasonEvent): static
    {
        $this->reasonEvent = $reasonEvent;

        return $this;
    }

    public function getOptionSelected(): ?string
    {
        return $this->optionSelected;
    }

    public function setOptionSelected(?string $optionSelected): static
    {
        $this->optionSelected = $optionSelected;

        return $this;
    }
}
