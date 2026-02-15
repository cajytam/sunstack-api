<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Simulation\TaskSimulationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TaskSimulationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['task_simulation:read', 'read:id']],
    denormalizationContext: ['groups' => ['task_simulation:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'simulation' => SearchFilterInterface::STRATEGY_EXACT,
    ]
)]
#[ORM\HasLifecycleCallbacks]
class TaskSimulation
{
    use IdIntTrait;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $targetGroups = null;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'taskSimulations')]
    private ?User $targetUser = null;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $doneAt = null;

    #[Groups(['task_simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: FileTask::class)]
    private Collection $fileTasks;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'taskSimulations')]
    private ?Simulation $simulation = null;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'taskSimulationOpened')]
    private ?User $openedBy = null;

    #[Groups(['task_simulation:read'])]
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskCommentSimulation::class)]
    private Collection $taskCommentSimulations;

    #[Groups(['task_simulation:read', 'task_simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'taskSimulationsClosed')]
    private ?User $closedBy = null;

    public function __construct()
    {
        $this->fileTasks = new ArrayCollection();
        $this->taskCommentSimulations = new ArrayCollection();
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

    public function getTargetGroups(): ?array
    {
        return $this->targetGroups;
    }

    public function setTargetGroups(?array $targetGroups): static
    {
        $this->targetGroups = $targetGroups;

        return $this;
    }

    public function getTargetUser(): ?User
    {
        return $this->targetUser;
    }

    public function setTargetUser(?User $targetUser): static
    {
        $this->targetUser = $targetUser;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDoneAt(): ?\DateTimeImmutable
    {
        return $this->doneAt;
    }

    public function setDoneAt(?\DateTimeImmutable $doneAt): static
    {
        $this->doneAt = $doneAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function createdAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, FileTask>
     */
    public function getFileTasks(): Collection
    {
        return $this->fileTasks;
    }

    public function addFileTask(FileTask $fileTask): static
    {
        if (!$this->fileTasks->contains($fileTask)) {
            $this->fileTasks->add($fileTask);
            $fileTask->setTask($this);
        }

        return $this;
    }

    public function removeFileTask(FileTask $fileTask): static
    {
        if ($this->fileTasks->removeElement($fileTask)) {
            // set the owning side to null (unless already changed)
            if ($fileTask->getTask() === $this) {
                $fileTask->setTask(null);
            }
        }

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

    public function getOpenedBy(): ?User
    {
        return $this->openedBy;
    }

    public function setOpenedBy(?User $openedBy): static
    {
        $this->openedBy = $openedBy;

        return $this;
    }

    /**
     * @return Collection<int, TaskCommentSimulation>
     */
    public function getTaskCommentSimulations(): Collection
    {
        return $this->taskCommentSimulations;
    }

    public function addTaskCommentSimulation(TaskCommentSimulation $taskCommentSimulation): static
    {
        if (!$this->taskCommentSimulations->contains($taskCommentSimulation)) {
            $this->taskCommentSimulations->add($taskCommentSimulation);
            $taskCommentSimulation->setTask($this);
        }

        return $this;
    }

    public function removeTaskCommentSimulation(TaskCommentSimulation $taskCommentSimulation): static
    {
        if ($this->taskCommentSimulations->removeElement($taskCommentSimulation)) {
            // set the owning side to null (unless already changed)
            if ($taskCommentSimulation->getTask() === $this) {
                $taskCommentSimulation->setTask(null);
            }
        }

        return $this;
    }

    public function getClosedBy(): ?User
    {
        return $this->closedBy;
    }

    public function setClosedBy(?User $closedBy): static
    {
        $this->closedBy = $closedBy;

        return $this;
    }
}
