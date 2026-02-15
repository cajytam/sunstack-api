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
use App\Entity\Traits\IdIntTrait;
use App\Repository\Survey\SurveyItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SurveyItemRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['surveyItem:read', 'read:id']],
    denormalizationContext: ['groups' => ['surveyItem:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'surveyStep.id' => SearchFilterInterface::STRATEGY_EXACT
    ]
)]
class SurveyItem
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'surveyStep:read', 'surveyStep:write', 'survey:read', 'surveyItem:read', 'surveyItem:write', 'surveySimulation:read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['simulation:read', 'surveyStep:read', 'surveyStep:write', 'surveyItem:read', 'surveyItem:write', 'surveySimulation:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[Groups(['surveyStep:read', 'surveyStep:write', 'surveyItem:read', 'surveyItem:write', 'surveySimulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?array $options = null;

    #[Groups(['surveyStep:read', 'surveyStep:write', 'surveyItem:read', 'surveyItem:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $conditions = null;

    #[Groups(['surveyStep:read', 'surveyStep:write', 'surveyItem:read'])]
    #[ORM\OneToMany(mappedBy: 'surveyItem', targetEntity: Survey::class)]
    private Collection $surveys;

    #[Groups(['simulation:read', 'surveyStep:read', 'surveyStep:write', 'surveyItem:read', 'surveyItem:write'])]
    #[ORM\ManyToOne(inversedBy: 'surveyItems')]
    private ?SurveyStep $surveyStep = null;

    public function __construct()
    {
        $this->surveys = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    public function setConditions(?array $conditions): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return Collection<int, Survey>
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): static
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys->add($survey);
            $survey->setSurveyItem($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): static
    {
        if ($this->surveys->removeElement($survey)) {
            // set the owning side to null (unless already changed)
            if ($survey->getSurveyItem() === $this) {
                $survey->setSurveyItem(null);
            }
        }

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
}
