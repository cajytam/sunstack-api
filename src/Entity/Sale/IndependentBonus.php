<?php

namespace App\Entity\Sale;

use App\Entity\Traits\IdIntTrait;
use App\Repository\Sale\IndependentBonusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IndependentBonusRepository::class)]
class IndependentBonus
{
    use IdIntTrait;

    #[ORM\Column(nullable: true)]
    private ?int $nbPanels = null;

    #[ORM\Column(nullable: true)]
    private ?float $points = null;

    #[ORM\Column(nullable: true)]
    private ?float $points_1 = null;

    #[ORM\Column(nullable: true)]
    private ?float $points_2 = null;

    public function getNbPanels(): ?int
    {
        return $this->nbPanels;
    }

    public function setNbPanels(?int $nbPanels): static
    {
        $this->nbPanels = $nbPanels;

        return $this;
    }

    public function getPoints(): ?float
    {
        return $this->points;
    }

    public function setPoints(?float $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getPoints1(): ?float
    {
        return $this->points_1;
    }

    public function setPoints1(?float $points_1): static
    {
        $this->points_1 = $points_1;

        return $this;
    }

    public function getPoints2(): ?float
    {
        return $this->points_2;
    }

    public function setPoints2(?float $points_2): static
    {
        $this->points_2 = $points_2;

        return $this;
    }
}
