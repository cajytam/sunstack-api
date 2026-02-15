<?php

namespace App\Entity\User;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\Simulation;
use App\Entity\Traits\IdIntTrait;
use App\Repository\User\HistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'historie',
    operations: [
        new Get(),
        new GetCollection(),
        new Post()
    ],
    normalizationContext: ['groups' => ['history:read', 'read:id']],
    denormalizationContext: ['groups' => ['history:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(SearchFilter::class, properties: [
    'userId' => 'exact',
    'element' => 'exact',
    'elementId' => 'exact',
    'simulation' => 'exact',
])]
#[ORM\Entity(repositoryClass: HistoryRepository::class)]
class History
{
    use IdIntTrait;

    #[Groups(['history:read', 'history:write', 'user:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $element = null;

    #[Groups(['history:read', 'history:write', 'user:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $elementId = null;

    #[Groups(['history:read', 'history:write', 'simulation:read'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'histories')]
    private ?User $userId = null;

    #[Groups(['history:read', 'history:write', 'user:read', 'simulation:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $doneAt = null;

    #[Groups(['history:read', 'history:write', 'user:read', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[Groups(['history:read', 'history:write', 'user:read', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[Groups(['history:read', 'history:write', 'user:read'])]
    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'histories')]
    private ?Simulation $simulation = null;

    public function __construct()
    {
        $this->doneAt = new \DateTimeImmutable();
    }

    public function getElement(): ?string
    {
        return $this->element;
    }

    public function setElement(?string $element): static
    {
        $this->element = $element;

        return $this;
    }

    public function getElementId(): ?int
    {
        return $this->elementId;
    }

    public function setElementId(?int $elementId): static
    {
        $this->elementId = $elementId;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getDoneAt(): ?\DateTimeImmutable
    {
        return $this->doneAt;
    }

    public function setDoneAt(\DateTimeImmutable $doneAt): static
    {
        $this->doneAt = $doneAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
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

    #[Groups(['user:read'])]
    public function getUserTitle(): string
    {
        if ($this->simulation) {
            return "Devis " . $this->simulation->getName();
        }
        return '';
    }
}
