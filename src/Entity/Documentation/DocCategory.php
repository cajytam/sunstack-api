<?php

namespace App\Entity\Documentation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Documentation\DocCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DocCategoryRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['doc_category:read', 'read:id', 'timestamp:read']],
    denormalizationContext: ['groups' => ['doc_category:write']],
    security: "is_granted('ROLE_USER')",
)]
class DocCategory
{
    use IdIntTrait;

    #[Groups(['doc_category:read', 'doc_category:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['doc_category:read', 'doc_category:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $sort = null;

    #[Groups(['doc_category:read', 'doc_category:write'])]
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: DocFile::class)]
    private Collection $docFiles;

    public function __construct()
    {
        $this->docFiles = new ArrayCollection();
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
     * @return Collection<int, DocFile>
     */
    public function getDocFiles(): Collection
    {
        return $this->docFiles;
    }

    public function addDocFile(DocFile $docFile): static
    {
        if (!$this->docFiles->contains($docFile)) {
            $this->docFiles->add($docFile);
            $docFile->setCategory($this);
        }

        return $this;
    }

    public function removeDocFile(DocFile $docFile): static
    {
        if ($this->docFiles->removeElement($docFile)) {
            // set the owning side to null (unless already changed)
            if ($docFile->getCategory() === $this) {
                $docFile->setCategory(null);
            }
        }

        return $this;
    }
}
