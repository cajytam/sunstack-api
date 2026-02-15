<?php

namespace App\Entity\Customer;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Simulation\TempCustomer;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Customer\CompanyTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['company_type:read', 'read:id']],
    denormalizationContext: ['groups' => ['company_type:write']],
    order: ['name' => 'asc'],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: CompanyTypeRepository::class)]
class CompanyType
{
    use IdIntTrait;

    #[Groups(['simulation:read', 'company_type:write', 'company_type:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $name = null;

    #[Groups(['simulation:read', 'company_type:write', 'company_type:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fullname = null;

    #[Groups(['simulation:read', 'company_type:write', 'company_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isSubjectToVAT = null;

    #[ORM\OneToMany(mappedBy: 'companyType', targetEntity: Customer::class)]
    private Collection $customers;

    #[ORM\OneToMany(mappedBy: 'companyType', targetEntity: TempCustomer::class)]
    private Collection $tempCustomers;

    #[Groups(['company_type:write', 'company_type:read'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $mapInseeCategoriesJuridiques = [];

    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->tempCustomers = new ArrayCollection();
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

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(?string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function isIsSubjectToVAT(): ?bool
    {
        return $this->isSubjectToVAT;
    }

    public function setIsSubjectToVAT(?bool $isSubjectToVAT): static
    {
        $this->isSubjectToVAT = $isSubjectToVAT;

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setCompanyType($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getCompanyType() === $this) {
                $customer->setCompanyType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TempCustomer>
     */
    public function getTempCustomers(): Collection
    {
        return $this->tempCustomers;
    }

    public function addTempCustomer(TempCustomer $tempCustomer): static
    {
        if (!$this->tempCustomers->contains($tempCustomer)) {
            $this->tempCustomers->add($tempCustomer);
            $tempCustomer->setCompanyType($this);
        }

        return $this;
    }

    public function removeTempCustomer(TempCustomer $tempCustomer): static
    {
        if ($this->tempCustomers->removeElement($tempCustomer)) {
            // set the owning side to null (unless already changed)
            if ($tempCustomer->getCompanyType() === $this) {
                $tempCustomer->setCompanyType(null);
            }
        }

        return $this;
    }

    public function addMapInseeCategoriesJuridiques(string|int $categorieJuridique): static
    {
        if (!in_array($categorieJuridique, $this->getMapInseeCategoriesJuridiques())) {
            $this->mapInseeCategoriesJuridiques[] = $categorieJuridique;
        }

        return $this;
    }

    public function getMapInseeCategoriesJuridiques(): array
    {
        return $this->mapInseeCategoriesJuridiques;
    }

    public function setMapInseeCategoriesJuridiques(?array $mapInseeCategoriesJuridiques): static
    {
        $this->mapInseeCategoriesJuridiques = $mapInseeCategoriesJuridiques;

        return $this;
    }
}
