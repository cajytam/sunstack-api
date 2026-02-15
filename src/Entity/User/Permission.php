<?php

namespace App\Entity\User;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\User\PermissionRepository;
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
    normalizationContext: ['groups' => ['permission:read', 'read:id']],
    denormalizationContext: ['groups' => ['permission:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    use IdIntTrait;

    #[Groups(['permission:read', 'permission:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['permission:read', 'permission:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[Groups(['permission:read', 'permission:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $canRead = null;

    #[Groups(['permission:read', 'permission:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $canModify = null;

    #[Groups(['permission:read', 'permission:write'])]
    #[ORM\Column(nullable: true)]
    private ?bool $canAdd = null;

    #[Groups(['permission:read', 'permission:write'])]
    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'permissions')]
    private Collection $roles;

    #[Groups(['permission:read', 'permission:write'])]
    #[ORM\Column(nullable: true)]
    private ?int $maximumItems = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'permissions')]
    private Collection $users;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isCanRead(): ?bool
    {
        return $this->canRead;
    }

    public function setCanRead(?bool $canRead): static
    {
        $this->canRead = $canRead;

        return $this;
    }

    public function isCanModify(): ?bool
    {
        return $this->canModify;
    }

    public function setCanModify(?bool $canModify): static
    {
        $this->canModify = $canModify;

        return $this;
    }

    public function isCanAdd(): ?bool
    {
        return $this->canAdd;
    }

    public function setCanAdd(?bool $canAdd): static
    {
        $this->canAdd = $canAdd;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addPermission($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        if ($this->roles->removeElement($role)) {
            $role->removePermission($this);
        }

        return $this;
    }

    public function getMaximumItems(): ?int
    {
        return $this->maximumItems;
    }

    public function setMaximumItems(?int $maximumItems): static
    {
        $this->maximumItems = $maximumItems;

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
            $user->addPermission($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removePermission($this);
        }

        return $this;
    }
}
