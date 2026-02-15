<?php

namespace App\Entity\User;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\User\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['location:read', 'read:id']],
    denormalizationContext: ['groups' => ['location:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    use IdIntTrait;

    #[Groups(['location:read', 'location:write', 'user:read', 'userAll:read', 'simulationAll:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: User::class)]
    private Collection $users;

    #[Groups(['location:read', 'location:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $identifier = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setLocation($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getLocation() === $this) {
                $user->setLocation(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }
}
