<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Simulation\FileTaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: FileTaskRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            inputFormats: [
                'multipart' => ['multipart/form-data'],
                'json' => ['application/json']
            ],
        ),
        new Patch(
            inputFormats: [
                'multipart' => ['multipart/form-data'],
                'json' => ['application/merge-patch+json']
            ],
        )
    ],
    normalizationContext: ['groups' => ['read:id', 'file_task:read']],
    denormalizationContext: ['groups' => ['file_task:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\HasLifecycleCallbacks]
class FileTask
{
    use IdIntTrait;

    #[Groups(['file_task:write'])]
    #[Vich\UploadableField(mapping: 'task_simulation_docs', fileNameProperty: 'filename')]
    public ?File $file = null;

    #[Groups(['file_task:read', 'simulation:read', 'task_simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[Groups(['file_task:read', 'file_task:write', 'simulation:read', 'task_simulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'fileTasks')]
    private ?User $user = null;

    #[Groups(['file_task:read', 'file_task:write', 'simulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'fileTasks')]
    private ?TaskSimulation $task = null;

    #[Groups(['file_task:read', 'file_task:write', 'task_simulation:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    #[Groups(['file_task:read', 'file_task:write', 'task_simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
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

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    #[ORM\PrePersist()]
    public function addedAt(): void
    {
        $this->addedAt = new \DateTimeImmutable();
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
}
