<?php

namespace App\Entity\Product;

class PanelProduction
{
    private ?string $id = null;
    private ?Panel $panel = null;
    private ?int $year = null;
    private ?float $production = null;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Panel|null
     */
    public function getPanel(): ?Panel
    {
        return $this->panel;
    }

    /**
     * @param Panel|null $panel
     */
    public function setPanel(?Panel $panel): void
    {
        $this->panel = $panel;
    }

    /**
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @param int|null $year
     */
    public function setYear(?int $year): void
    {
        $this->year = $year;
    }

    /**
     * @return float|null
//     */
//    public function getProduction(): ?float
//    {
//        return $this->production;
//    }

    /**
     * @param float|null $production
     */
    public function setProduction(?float $production): void
    {
        $this->production = $production;
    }
}
