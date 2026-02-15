<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\Simulation\CreateStatus;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            controller: CreateStatus::class
        ),
        new Patch(
            controller: CreateStatus::class
        )
    ],
    normalizationContext: ['groups' => ['status:read', 'read:id']],
    denormalizationContext: ['groups' => ['status:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: ['statusGroup' => 'exact']
)]
class Status
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'simulationAll:read', 'status:read', 'status_simulation:read', 'status:write', 'file:export'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name = null;

    #[Groups(['status:read', 'status:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $sort = null;

    #[Groups(['simulation:read', 'status:read', 'status_simulation:read', 'status:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $required = null;

    #[Groups(['simulation:read', 'status:read', 'status:write', 'status_simulation:read', 'file:export'])]
    #[ORM\ManyToOne(fetch: "EAGER", inversedBy: 'statuses')]
    private ?StatusGroup $statusGroup = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: StatusSimulation::class)]
    private Collection $statusSimulations;

    #[Groups(['simulation:read', 'simulationAll:read', 'status:read', 'status_simulation:read', 'status:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $color = null;

    #[Groups(['simulation:read', 'status:read', 'status_simulation:read', 'status:write'])]
    #[ORM\Column]
    private ?bool $isKeyStep = null;

    public function __construct()
    {
        $this->statusSimulations = new ArrayCollection();
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

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    public function getRequired(): ?array
    {
        return $this->required;
    }

    public function setRequired(?array $required): static
    {
        $this->required = $required;

        return $this;
    }

    public function getStatusGroup(): ?StatusGroup
    {
        return $this->statusGroup;
    }

    public function setStatusGroup(?StatusGroup $statusGroup): static
    {
        $this->statusGroup = $statusGroup;

        return $this;
    }

    /**
     * @return Collection<int, StatusSimulation>
     */
    public function getStatusSimulations(): Collection
    {
        return $this->statusSimulations;
    }

    public function addStatusSimulation(StatusSimulation $statusSimulation): static
    {
        if (!$this->statusSimulations->contains($statusSimulation)) {
            $this->statusSimulations->add($statusSimulation);
            $statusSimulation->setStatus($this);
        }

        return $this;
    }

    public function removeStatusSimulation(StatusSimulation $statusSimulation): static
    {
        if ($this->statusSimulations->removeElement($statusSimulation)) {
            // set the owning side to null (unless already changed)
            if ($statusSimulation->getStatus() === $this) {
                $statusSimulation->setStatus(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function isIsKeyStep(): ?bool
    {
        return $this->isKeyStep;
    }

    public function setIsKeyStep(bool $isKeyStep): static
    {
        $this->isKeyStep = $isKeyStep;

        return $this;
    }
}
