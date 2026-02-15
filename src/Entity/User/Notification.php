<?php

namespace App\Entity\User;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\User\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['notification:read', 'read:id']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['notification:read', 'read:id']],
        ),
        new Post(),
        new Patch(),
        new Delete()
    ],
    denormalizationContext: ['groups' => ['notification:write']],
    order: ['id' => 'DESC'],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'user' => SearchFilterInterface::STRATEGY_EXACT,
        'typeNotif' => SearchFilterInterface::STRATEGY_EXACT
    ]
)]
#[ApiFilter(ExistsFilter::class, properties: ['typeNotif'])]
class Notification
{
    use IdIntTrait;

    #[Groups(['notification:read', 'notification:write'])]
    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?User $user = null;

    #[Groups(['notification:read', 'notification:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $message = null;

    #[Groups(['notification:read', 'notification:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $url = null;

    #[Groups(['notification:read', 'notification:write'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['notification:read', 'notification:write'])]
    #[ORM\Column]
    private ?bool $isRead = false;

    #[Groups(['notification:read', 'notification:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[Groups(['notification:read', 'notification:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $typeNotif = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getUrl(): ?array
    {
        return $this->url;
    }

    public function setUrl(?array $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTypeNotif(): ?string
    {
        return $this->typeNotif;
    }

    public function setTypeNotif(?string $typeNotif): static
    {
        $this->typeNotif = $typeNotif;

        return $this;
    }
}
