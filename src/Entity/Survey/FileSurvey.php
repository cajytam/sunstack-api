<?php

namespace App\Entity\Survey;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Survey\FileSurveyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: FileSurveyRepository::class)]
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
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['file_survey:read', 'read:id', 'timestamp:read']],
    denormalizationContext: ['groups' => ['file_survey:write', 'timestamp:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(SearchFilter::class, properties: ['survey' => 'exact'])]
#[ORM\HasLifecycleCallbacks]
class FileSurvey
{
    use IdIntTrait;

    #[Groups(['file_survey:write'])]
    #[Vich\UploadableField(mapping: 'survey_docs', fileNameProperty: 'filename')]
    public ?File $file = null;

    #[Groups(['file_survey:read', 'survey:read', 'surveySimulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[Groups(['file_survey:read', 'file_survey:write', 'survey:read'])]
    #[ORM\ManyToOne(inversedBy: 'fileSurveys')]
    private ?Survey $survey = null;

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): static
    {
        $this->survey = $survey;

        return $this;
    }
}
