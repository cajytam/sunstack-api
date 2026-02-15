<?php

namespace App\Entity\Documentation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\User\User;
use App\Repository\Documentation\DocHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DocHistoryRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['doc_history:read', 'read:id']],
    denormalizationContext: ['groups' => ['doc_history:write']],
    security: "is_granted('ROLE_USER')",
)]
class DocHistory
{
    use IdIntTrait;

    #[Groups(['doc_history:read', 'doc_history:write', 'doc_category:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $action = null;

    #[Groups(['doc_history:read', 'doc_history:write', 'doc_category:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $doneAt = null;

    #[Groups(['doc_history:read', 'doc_history:write', 'doc_category:read'])]
    #[ORM\ManyToOne]
    private ?User $user = null;

    #[Groups(['doc_history:read', 'doc_history:write', 'doc_category:read'])]
    #[ORM\ManyToOne(inversedBy: 'docHistories')]
    private ?DocFile $document = null;

    public function __construct()
    {
        if (!$this->doneAt) {
            $this->doneAt = new \DateTimeImmutable();
        }
    }

    public function getAction(): ?int
    {
        return $this->action;
    }

    public function setAction(?int $action): static
    {
        $this->action = $action;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDocument(): ?DocFile
    {
        return $this->document;
    }

    public function setDocument(?DocFile $document): static
    {
        $this->document = $document;

        return $this;
    }
}
