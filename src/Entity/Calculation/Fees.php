<?php

namespace App\Entity\Calculation;

use App\Entity\Enum\FeesType;

class Fees
{
    private ?string $id = null;
    private ?int $year = null;
    private ?FeesType $type = null;
    private ?float $amount = null;

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
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     */
    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return FeesType|null
     */
    public function getType(): ?FeesType
    {
        return $this->type;
    }

    /**
     * @param FeesType|null $type
     */
    public function setType(?FeesType $type): void
    {
        $this->type = $type;
    }
}
