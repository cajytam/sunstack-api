<?php

namespace App\Entity\Documentation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Entity\User\User;
use App\Repository\Documentation\DocFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: DocFileRepository::class)]
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
    normalizationContext: ['groups' => ['doc_file:read', 'read:id', 'timestamp:read']],
    denormalizationContext: ['groups' => ['doc_file:write', 'timestamp:write']],
    security: "is_granted('ROLE_USER')",
)]
class DocFile
{
    use IdIntTrait;

    #[Groups(['doc_file:write'])]
    #[Vich\UploadableField(mapping: 'documentation_file', fileNameProperty: 'path')]
    public ?File $file = null;

    #[Groups(['doc_category:read', 'doc_file:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[Groups(['doc_category:read', 'doc_file:write', 'doc_file:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['doc_category:read', 'doc_file:write', 'doc_file:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['doc_category:read', 'doc_file:write', 'doc_file:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $sort = null;

    #[Groups(['doc_file:read', 'doc_file:write'])]
    #[ORM\ManyToOne(inversedBy: 'docFiles')]
    private ?DocCategory $category = null;

    #[Groups(['doc_category:read', 'doc_file:read', 'doc_file:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $groupsCanSee = null;

    #[Groups(['doc_category:read', 'doc_file:read', 'doc_file:write'])]
    #[ORM\ManyToOne]
    private ?User $user = null;

    #[Groups(['doc_category:read', 'doc_file:read', 'doc_file:write'])]
    #[ORM\OneToMany(mappedBy: 'document', targetEntity: DocHistory::class)]
    private Collection $docHistories;

    use TimeStampTrait;

    public function __construct()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTimeImmutable());
        }
        if (!$this->sort) {
            $this->sort = 0;
        }
        $this->docHistories = new ArrayCollection();
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getCategory(): ?DocCategory
    {
        return $this->category;
    }

    public function setCategory(?DocCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getGroupsCanSee(): ?array
    {
        return $this->groupsCanSee;
    }

    public function setGroupsCanSee(?array $groupsCanSee): static
    {
        $this->groupsCanSee = $groupsCanSee;

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

    /**
     * @return Collection<int, DocHistory>
     */
    public function getDocHistories(): Collection
    {
        return $this->docHistories;
    }

    public function addDocHistory(DocHistory $docHistory): static
    {
        if (!$this->docHistories->contains($docHistory)) {
            $this->docHistories->add($docHistory);
            $docHistory->setDocument($this);
        }

        return $this;
    }

    public function removeDocHistory(DocHistory $docHistory): static
    {
        if ($this->docHistories->removeElement($docHistory)) {
            // set the owning side to null (unless already changed)
            if ($docHistory->getDocument() === $this) {
                $docHistory->setDocument(null);
            }
        }

        return $this;
    }
}
