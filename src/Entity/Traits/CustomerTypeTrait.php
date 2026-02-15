<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait CustomerTypeTrait
{
    const CUSTOMER_TYPE = [
        1 => 'Particulier',
        2 => 'Professionnel',
    ];

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $customerType = null;

    #[Groups(['temp_customer:read', 'temp_customer:write', 'simulation:read'])]
    public function getCustomerType(): ?int
    {
        return $this->customerType;
    }

    #[Groups(['temp_customer:write'])]
    public function setCustomerType(?int $customerType): self
    {
        $this->customerType = $customerType;

        return $this;
    }

    public function getCustomerTypeName(): string
    {
        return self::CUSTOMER_TYPE[$this->customerType];
    }
}
