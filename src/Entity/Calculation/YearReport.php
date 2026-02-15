<?php

namespace App\Entity\Calculation;

class YearReport
{
    private ?int $year = null;
    private ?float $production = null;
    private ?float $consumption = null;
    private ?float $injection = null;
    private ?float $indexedKwhCost = null;
    private ?float $invoice = null;
    private ?float $sale = null;
    private ?float $new_invoice = null;
    private ?float $investment = null;
    private ?float $prime = null;
    private ?float $profit = null;
    private ?float $diff = null;
    private ?YearReport $previous = null;
    private ?YearReport $next = null;

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getProduction(): ?float
    {
        return $this->production;
    }

    public function setProduction(?float $production): static
    {
        $this->production = $production;

        return $this;
    }
    
    public function getConsumption(): ?float
    {
        return $this->consumption;
    }

    public function setConsumption(?float $consumption): static
    {
        $this->consumption = $consumption;

        return $this;
    }

    public function getInjection(): ?float
    {
        return $this->injection;
    }

    public function setInjection(?float $injection): static
    {
        $this->injection = $injection;

        return $this;
    }

    public function getIndexedKwhCost(): ?float
    {
        return $this->indexedKwhCost;
    }

    public function setIndexedKwhCost(?float $indexedKwhCost): static
    {
        $this->indexedKwhCost = $indexedKwhCost;

        return $this;
    }

    public function getInvoice(): ?float
    {
        return $this->invoice;
    }

    public function setInvoice(?float $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getSale(): ?float
    {
        return $this->sale;
    }

    public function setSale(?float $sale): static
    {
        $this->sale = $sale;

        return $this;
    }

    public function getNewInvoice(): ?float
    {
        return $this->new_invoice;
    }

    public function setNewInvoice(?float $new_invoice): static
    {
        $this->new_invoice = $new_invoice;

        return $this;
    }

    public function getInvestment(): ?float
    {
        return $this->investment;
    }

    public function setInvestment(?float $investment): static
    {
        $this->investment = $investment;

        return $this;
    }

    public function getPrime(): ?float
    {
        return $this->prime;
    }

    public function setPrime(?float $prime): static
    {
        $this->prime = $prime;

        return $this;
    }

    public function getProfit(): ?float
    {
        return $this->profit;
    }

    public function setProfit(?float $profit): static
    {
        $this->profit = $profit;

        return $this;
    }

    public function getDiff(): ?float
    {
        return $this->diff;
    }

    public function setDiff(?float $diff): static
    {
        $this->diff = $diff;

        return $this;
    }

    public function getPrevious(): ?YearReport
    {
        return $this->previous;
    }

    public function setPrevious(?YearReport $previous): static
    {
        $this->previous = $previous;

        return $this;
    }

    public function getNext(): ?YearReport
    {
        return $this->next;
    }

    public function setNext(?YearReport $next): static
    {
        $this->next = $next;

        return $this;
    }

    public function getPivot(): null|int
    {
        return match (true) {
            null === $this->previous && 0 === $this->diff => null,
            (null === $this->previous && $this->diff < 0) || (null !== $this->previous && $this->previous->diff < 0 && $this->diff < 0) => -1,
            (null === $this->previous && $this->diff > 0) || (null !== $this->previous && $this->previous->diff < 0 && $this->diff > 0) => 0,
            null !== $this->previous && $this->previous->diff > 0 && $this->diff > 0 => 1,
            default => null
        };
    }
}
