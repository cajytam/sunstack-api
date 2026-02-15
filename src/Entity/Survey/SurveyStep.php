<?php

namespace App\Entity\Survey;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Survey\SurveyStepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['surveyStep:read', 'read:id']],
    denormalizationContext: ['groups' => ['surveyStep:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: SurveyStepRepository::class)]
class SurveyStep
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'surveyStep:read', 'surveyStep:write', 'surveySimulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['simulation:read', 'surveyStep:read', 'surveyStep:write', 'surveySimulation:read'])]
    #[ORM\Column]
    private ?array $whoCanDo = [];

    #[Groups(['simulation:read', 'surveyStep:read', 'surveyStep:write', 'surveySimulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?array $whoCanControl = null;

    #[Groups(['simulation:read', 'surveyStep:read', 'surveyStep:write', 'surveySimulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $minProjectStatus = null;

    #[ORM\OneToMany(mappedBy: 'surveyStep', targetEntity: SurveySimulation::class)]
    private Collection $surveySimulations;

    #[Groups(['simulation:read', 'surveyStep:read', 'surveySimulation:read'])]
    #[ORM\OneToMany(mappedBy: 'surveyStep', targetEntity: SurveyItem::class)]
    private Collection $surveyItems;

    public function __construct()
    {
        $this->surveySimulations = new ArrayCollection();
        $this->surveyItems = new ArrayCollection();
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

    public function getWhoCanDo(): ?array
    {
        return array_unique($this->whoCanDo);
    }

    public function setWhoCanDo(?array $whoCanDo): static
    {
        $this->whoCanDo = $whoCanDo;

        return $this;
    }

    public function getWhoCanControl(): ?array
    {
        return $this->whoCanControl;
    }

    public function setWhoCanControl(?array $whoCanControl): static
    {
        $this->whoCanControl = $whoCanControl;

        return $this;
    }

    public function getMinProjectStatus(): ?int
    {
        return $this->minProjectStatus;
    }

    public function setMinProjectStatus(?int $minProjectStatus): static
    {
        $this->minProjectStatus = $minProjectStatus;

        return $this;
    }

    /**
     * @return Collection<int, SurveySimulation>
     */
    public function getSurveySimulations(): Collection
    {
        return $this->surveySimulations;
    }

    public function addSurveySimulation(SurveySimulation $surveySimulation): static
    {
        if (!$this->surveySimulations->contains($surveySimulation)) {
            $this->surveySimulations->add($surveySimulation);
            $surveySimulation->setSurveyStep($this);
        }

        return $this;
    }

    public function removeSurveySimulation(SurveySimulation $surveySimulation): static
    {
        if ($this->surveySimulations->removeElement($surveySimulation)) {
            // set the owning side to null (unless already changed)
            if ($surveySimulation->getSurveyStep() === $this) {
                $surveySimulation->setSurveyStep(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SurveyItem>
     */
    public function getSurveyItems(): Collection
    {
        return $this->surveyItems;
    }

    public function addSurveyItem(SurveyItem $surveyItem): static
    {
        if (!$this->surveyItems->contains($surveyItem)) {
            $this->surveyItems->add($surveyItem);
            $surveyItem->setSurveyStep($this);
        }

        return $this;
    }

    public function removeSurveyItem(SurveyItem $surveyItem): static
    {
        if ($this->surveyItems->removeElement($surveyItem)) {
            // set the owning side to null (unless already changed)
            if ($surveyItem->getSurveyStep() === $this) {
                $surveyItem->setSurveyStep(null);
            }
        }

        return $this;
    }
}
