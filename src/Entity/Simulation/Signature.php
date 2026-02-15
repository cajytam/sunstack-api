<?php

namespace App\Entity\Simulation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Repository\Simulation\SignatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SignatureRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['signature:read', 'read:id', 'timestamp:read']],
    denormalizationContext: ['groups' => ['signature:write', 'timestamp:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: [
    'simulation' => 'exact'
])]
class Signature
{
    use IdIntTrait;

    #[Groups(['signature:read', 'signature:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[Groups(['signature:read', 'signature:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstnameSignataire = null;

    #[Groups(['signature:read', 'signature:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastnameSignataire = null;

    #[Groups(['signature:read', 'signature:write', 'simulation:read'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $typeSignature = null;

    #[Groups(['signature:read', 'signature:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isClauseSuspensive = null;

    #[Groups(['signature:read', 'signature:write', 'simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $birthday = null;

    #[Groups(['signature:read', 'signature:write', 'simulation:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthPlace = null;

    #[ORM\OneToMany(mappedBy: 'signature', targetEntity: SignatureSimulation::class)]
    private Collection $signatureSimulations;

    public function __construct()
    {
        $this->signatureSimulations = new ArrayCollection();
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

    public function getFirstnameSignataire(): ?string
    {
        return $this->firstnameSignataire;
    }

    public function setFirstnameSignataire(?string $firstnameSignataire): static
    {
        $this->firstnameSignataire = $firstnameSignataire;

        return $this;
    }

    public function getLastnameSignataire(): ?string
    {
        return $this->lastnameSignataire;
    }

    public function setLastnameSignataire(?string $lastnameSignataire): static
    {
        $this->lastnameSignataire = $lastnameSignataire;

        return $this;
    }

    public function getTypeSignature(): ?string
    {
        return $this->typeSignature;
    }

    public function setTypeSignature(?string $typeSignature): static
    {
        $this->typeSignature = $typeSignature;

        return $this;
    }

    public function isIsClauseSuspensive(): ?bool
    {
        return $this->isClauseSuspensive;
    }

    public function setIsClauseSuspensive(?bool $isClauseSuspensive): static
    {
        $this->isClauseSuspensive = $isClauseSuspensive;

        return $this;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeImmutable $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): static
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    /**
     * @return Collection<int, SignatureSimulation>
     */
    public function getSignatureSimulations(): Collection
    {
        return $this->signatureSimulations;
    }

    public function addSignatureSimulation(SignatureSimulation $signatureSimulation): static
    {
        if (!$this->signatureSimulations->contains($signatureSimulation)) {
            $this->signatureSimulations->add($signatureSimulation);
            $signatureSimulation->setSignature($this);
        }

        return $this;
    }

    public function removeSignatureSimulation(SignatureSimulation $signatureSimulation): static
    {
        if ($this->signatureSimulations->removeElement($signatureSimulation)) {
            // set the owning side to null (unless already changed)
            if ($signatureSimulation->getSignature() === $this) {
                $signatureSimulation->setSignature(null);
            }
        }

        return $this;
    }
}
