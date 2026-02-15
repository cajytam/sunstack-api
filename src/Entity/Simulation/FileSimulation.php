<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Entity\User\User;
use App\Repository\Simulation\FileSimulationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: FileSimulationRepository::class)]
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
    normalizationContext: ['groups' => ['file_simulation:read', 'read:id', 'timestamp:read']],
    denormalizationContext: ['groups' => ['file_simulation:write', 'timestamp:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(SearchFilter::class, properties: ['simulation' => 'exact'])]
#[ORM\HasLifecycleCallbacks]
class FileSimulation
{
    use IdIntTrait;

    #[Groups(['file_simulation:write'])]
    #[Vich\UploadableField(mapping: 'simulation_docs', fileNameProperty: 'filename')]
    public ?File $file = null;

    #[Groups(['simulation:read', 'file_simulation:read', 'file_simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[Groups(['simulation:read', 'file_simulation:read', 'file_simulation:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[Groups(['simulation:read', 'file_simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'fileSimulations')]
    private ?User $user = null;

    #[Groups(['file_simulation:write'])]
    #[ORM\ManyToOne(inversedBy: 'fileSimulations')]
    private ?Simulation $simulation = null;

    use TimeStampTrait;

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

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

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->getSimulation()->getIdentifier();
    }
}
