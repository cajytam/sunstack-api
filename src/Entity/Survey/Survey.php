<?php

namespace App\Entity\Survey;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\Simulation;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Entity\User\User;
use App\Repository\Survey\SurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SurveyRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['survey:read', 'read:id']],
    denormalizationContext: ['groups' => ['survey:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\HasLifecycleCallbacks]
class Survey
{
    use IdIntTrait;

    #[Groups(['survey:read', 'survey:write', 'surveySimulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    #[Groups(['simulation:read', 'survey:read', 'survey:write', 'surveySimulation:read'])]
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $statusValidation = null;

    #[Groups(['simulation:read', 'survey:read', 'survey:write', 'surveySimulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'surveys')]
    private ?User $reviewedBy = null;

    #[Groups(['simulation:read', 'survey:read', 'survey:write', 'surveySimulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[Groups(['simulation:read', 'survey:read', 'survey:write', 'surveySimulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'surveys')]
    private ?SurveyItem $surveyItem = null;

    #[Groups(['survey:read', 'survey:write', 'surveySimulation:read'])]
    #[ORM\ManyToOne(inversedBy: 'surveysSent')]
    private ?User $sentBy = null;

    #[Groups(['survey:read', 'survey:write'])]
    #[ORM\ManyToOne(inversedBy: 'surveys')]
    private ?Simulation $simulation = null;

    #[Groups(['survey:read', 'survey:write', 'surveySimulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[Groups(['survey:read', 'surveySimulation:read'])]
    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: FileSurvey::class)]
    private Collection $fileSurveys;

    public function __construct()
    {
        $this->fileSurveys = new ArrayCollection();
    }

    use TimeStampTrait;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStatusValidation(): ?string
    {
        return $this->statusValidation;
    }

    public function setStatusValidation(?string $statusValidation): static
    {
        $this->statusValidation = $statusValidation;

        return $this;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;

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

    public function getSurveyItem(): ?SurveyItem
    {
        return $this->surveyItem;
    }

    public function setSurveyItem(?SurveyItem $surveyItem): static
    {
        $this->surveyItem = $surveyItem;

        return $this;
    }

    public function getSentBy(): ?User
    {
        return $this->sentBy;
    }

    public function setSentBy(?User $sentBy): static
    {
        $this->sentBy = $sentBy;

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

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;

        return $this;
    }

    /**
     * @return Collection<int, FileSurvey>
     */
    public function getFileSurveys(): Collection
    {
        return $this->fileSurveys;
    }

    public function addFileSurvey(FileSurvey $fileSurvey): static
    {
        if (!$this->fileSurveys->contains($fileSurvey)) {
            $this->fileSurveys->add($fileSurvey);
            $fileSurvey->setSurvey($this);
        }

        return $this;
    }

    public function removeFileSurvey(FileSurvey $fileSurvey): static
    {
        if ($this->fileSurveys->removeElement($fileSurvey)) {
            // set the owning side to null (unless already changed)
            if ($fileSurvey->getSurvey() === $this) {
                $fileSurvey->setSurvey(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
