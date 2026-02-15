<?php

namespace App\Entity\Survey;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\Simulation;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Survey\SurveySimulationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SurveySimulationRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['surveySimulation:read', 'read:id', 'timestamp:read']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['surveySimulationAll:read', 'read:id', 'timestamp:read']],
        ),
        new Post(),
        new Patch(),
    ],
    denormalizationContext: ['groups' => ['surveySimulation:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'status' => SearchFilterInterface::STRATEGY_EXACT,
    ]
)]
class SurveySimulation
{
    const SURVEY_LEVEL = [
        'B' => 0,
        'V' => 1,
        'A' => 2,
        'C' => 3,
        'R' => 4,
    ];

    use IdIntTrait;

    #[Groups(['surveySimulation:write', 'surveySimulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'surveySimulations')]
    private ?Simulation $simulation = null;

    #[Groups(['surveySimulation:read', 'surveySimulation:write', 'simulation:read', 'surveySimulationAll:read'])]
    #[ORM\ManyToOne(inversedBy: 'surveySimulations')]
    private ?SurveyStep $surveyStep = null;

    #[Groups(['surveySimulation:read', 'surveySimulation:write', 'simulation:read', 'surveySimulationAll:read'])]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $status = null;

    #[Groups(['surveySimulation:read', 'surveySimulation:write', 'simulation:read', 'surveySimulationAll:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[Groups(['surveySimulation:read', 'surveySimulation:write', 'simulation:read', 'surveySimulationAll:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $statusUpdatedAt = null;

    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;

        return $this;
    }

    public function getSurveyStep(): ?SurveyStep
    {
        return $this->surveyStep;
    }

    public function setSurveyStep(?SurveyStep $surveyStep): static
    {
        $this->surveyStep = $surveyStep;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStatusUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->statusUpdatedAt;
    }

    public function setStatusUpdatedAt(?\DateTimeImmutable $statusUpdatedAt): static
    {
        $this->statusUpdatedAt = $statusUpdatedAt;

        return $this;
    }

    #[Groups(['simulation:read', 'surveySimulation:read', 'surveySimulationAll:read'])]
    public function getHighestStatus(): string
    {
        $highestLevel = 'B';
        foreach ($this->simulation->getSurveys() as $survey) {
            if ($survey->getSurveyItem()->getSurveyStep()->getId() === $this->getSurveyStep()->getId()) {
                if (static::SURVEY_LEVEL[$survey->getStatusValidation()] > static::SURVEY_LEVEL[$highestLevel]) {
                    $highestLevel = $survey->getStatusValidation();
                }
            }
        }
        return $highestLevel;
    }

    #[Groups(['simulation:read', 'surveySimulation:read', 'surveySimulationAll:read'])]
    public function getStatusForSendingNotification(): bool
    {
        if (count($this->simulation->getSurveys()->toArray()) === 0) {
            return false;
        }

        foreach ($this->simulation->getSurveys() as $survey) {
            if ($survey->getSurveyItem()->getSurveyStep()->getId() === $this->getSurveyStep()->getId()) {
                if (
                    $survey->getStatusValidation() === 'A'
                    || $survey->getStatusValidation() === 'B'
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    #[Groups(['simulation:read', 'surveySimulation:read', 'surveySimulationAll:read'])]
    public function getSurveyListOfUsersWhoParticipated(): array
    {
        $listUsers = [];
        if (count($this->simulation->getSurveys()->toArray()) === 0) {
            return $listUsers;
        }

        foreach ($this->simulation->getSurveys() as $survey) {
            if ($survey->getSurveyItem()->getSurveyStep()->getId() === $this->getSurveyStep()->getId()) {
                if (!in_array($survey->getSentBy()->getId(), $listUsers)) {
                    $listUsers[] = $survey->getSentBy()->getId();
                }
            }
        }
        return $listUsers;
    }
}
