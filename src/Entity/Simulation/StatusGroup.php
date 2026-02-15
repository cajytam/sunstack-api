<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\Simulation\CreateStatusGroup;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\StatusGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StatusGroupRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            controller: CreateStatusGroup::class
        ),
        new Patch(
            controller: CreateStatusGroup::class
        )
    ],
    normalizationContext: ['groups' => ['status_group:read', 'read:id']],
    denormalizationContext: ['groups' => ['status_group:write']],
    security: "is_granted('ROLE_USER')"
)]
class StatusGroup
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'status_group:read', 'status_group:write', 'status:read', 'status_simulation:read', 'file:export'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name = null;

    #[Groups(['simulation:read', 'status_group:read', 'status_group:write', 'status_simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $sort = null;

    #[Groups(['status_group:read', 'status_group:write'])]
    #[ORM\OneToMany(mappedBy: 'statusGroup', targetEntity: Status::class)]
    private Collection $statuses;

    #[Groups(['simulation:read', 'status_group:read', 'status_group:write'])]
    #[ORM\Column]
    private array $whoCanChange = [];

    public function __construct()
    {
        $this->statuses = new ArrayCollection();
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

    /**
     * @return Collection<int, Status>
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
            $status->setStatusGroup($this);
        }

        return $this;
    }

    public function removeStatus(Status $status): static
    {
        if ($this->statuses->removeElement($status)) {
            // set the owning side to null (unless already changed)
            if ($status->getStatusGroup() === $this) {
                $status->setStatusGroup(null);
            }
        }

        return $this;
    }

    public function getWhoCanChange(): array
    {
        $roles = $this->whoCanChange;

        $roles[] = 'ROLE_ADMIN';

        return array_unique($roles);
    }

    public function setWhoCanChange(array $whoCanChange): static
    {
        $this->whoCanChange = $whoCanChange;

        return $this;
    }

    #[Groups(['status_group:read'])]
    public function getNumberOfSteps(): int
    {
        if ($this->statuses) {
            return count($this->statuses);
        }
        return 0;
    }
}
