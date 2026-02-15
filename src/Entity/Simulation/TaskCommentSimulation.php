<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Simulation\TaskCommentSimulationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TaskCommentSimulationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['task_comment_simulation:read', 'read:id']],
    denormalizationContext: ['groups' => ['task_comment_simulation:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\HasLifecycleCallbacks]
class TaskCommentSimulation
{
    use IdIntTrait;

    #[Groups(['task_comment_simulation:read', 'task_comment_simulation:write', 'task_simulation:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[Groups(['task_comment_simulation:read', 'task_comment_simulation:write', 'task_simulation:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['task_comment_simulation:read', 'task_comment_simulation:write', 'task_simulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'taskCommentSimulations')]
    private ?User $addedBy = null;

    #[Groups(['task_comment_simulation:read', 'task_comment_simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'taskCommentSimulations')]
    private ?TaskSimulation $task = null;

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    #[ORM\PrePersist()]
    public function createdAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getTask(): ?TaskSimulation
    {
        return $this->task;
    }

    public function setTask(?TaskSimulation $task): static
    {
        $this->task = $task;

        return $this;
    }
}
