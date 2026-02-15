<?php

namespace App\Entity\Calculation;

class IndexPrice extends Index
{
    private ?float $amount = null;
    private ?IndexPrice $previous = null;
    private ?IndexPrice $next = null;

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
     * @return IndexPrice|null
     */
    public function getPrevious(): ?IndexPrice
    {
        return $this->previous;
    }

    /**
     * @param IndexPrice|null $previous
     */
    public function setPrevious(?IndexPrice $previous): void
    {
        $this->previous = $previous;
    }

    /**
     * @return IndexPrice|null
     */
    public function getNext(): ?IndexPrice
    {
        return $this->next;
    }

    /**
     * @param IndexPrice|null $next
     */
    public function setNext(?IndexPrice $next): void
    {
        $this->next = $next;
    }
}
