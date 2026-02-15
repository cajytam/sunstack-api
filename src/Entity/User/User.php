<?php

namespace App\Entity\User;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\User\CreateUser;
use App\Entity\Product\InverterCablePrice;
use App\Entity\Product\InverterPrice;
use App\Entity\Sale\SellerBonus;
use App\Entity\Simulation\FileSimulation;
use App\Entity\Simulation\FileTask;
use App\Entity\Simulation\Simulation;
use App\Entity\Simulation\StatusSimulation;
use App\Entity\Simulation\TaskCommentSimulation;
use App\Entity\Simulation\TaskSimulation;
use App\Entity\Survey\Survey;
use App\Entity\Traits\CivilitiesTrait;
use App\Entity\Traits\IdIntTrait;
use App\Entity\Traits\TimeStampTrait;
use App\Repository\User\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['user:read', 'read:id', 'timestamp:read']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['userAll:read', 'read:id']],
        ),
        new Post(
            inputFormats: [
                'multipart' => ['multipart/form-data'],
                'json' => ['application/json']
            ],
            controller: CreateUser::class,
        ),
        new Patch(
            inputFormats: [
                'multipart' => ['multipart/form-data'],
                'json' => ['application/merge-patch+json']
            ],
            controller: CreateUser::class
        )
    ],
    denormalizationContext: ['groups' => ['user:write', 'timestamp:write']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('email', 'Cette adresse mail est déjà utilisée')]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'roles' => 'partial',
    ]
)]
#[ApiFilter(ExistsFilter::class, properties: ['deletedAt'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdIntTrait;

    #[Groups(['user:read', 'userAll:read', 'user:write'])]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[Groups(['user:read', 'userAll:read', 'user:write'])]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[Groups(['user:write'])]
    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $civility = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastname = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstname = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[Groups(['user:read', 'userAll:read', 'user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[Groups(['user:read', 'userAll:read', 'user:write', 'simulationAll:read'])]
    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Location $location = null;

    #[Groups(['user:write'])]
    #[Vich\UploadableField(mapping: 'user_avatar', fileNameProperty: 'picture')]
    public ?File $file = null;

    #[Groups([
        'user:read', 'user:write', 'userAll:read', 'simulation:read', 'simulationAll:read', 'history:read',
        'status_simulation:read', 'task_simulation:read', 'task_comment_simulation:read', 'file_task:read',
        'inverter:read', 'inverterPrice:read', 'panel:read', 'doc_category:read', 'panelPrice:read', 'pricing_factor_type:read',
        'battery:read', 'batteryPrice:read', 'inverterCablePrice:read', 'inverterCable:read', 'simulation_charging_point:read'
    ])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[Groups(['user:read'])]
    #[ORM\OneToMany(mappedBy: 'ownedBy', targetEntity: Simulation::class)]
    private Collection $simulations;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateBirthday = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(nullable: true)]
    private ?array $settings = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\OneToMany(mappedBy: 'userId', targetEntity: History::class)]
    private Collection $histories;

    #[Groups(['user:read'])]
    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'users')]
    private Collection $permissions;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AuthToken::class)]
    private Collection $authTokens;

    #[ORM\OneToMany(mappedBy: 'reviewedBy', targetEntity: Survey::class)]
    private Collection $surveys;

    #[ORM\OneToMany(mappedBy: 'ownedBy', targetEntity: StatusSimulation::class)]
    private Collection $statusSimulations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: FileSimulation::class)]
    private Collection $fileSimulations;

    #[ORM\OneToMany(mappedBy: 'sentBy', targetEntity: Survey::class)]
    private Collection $surveysSent;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SellerBonus::class)]
    private Collection $sellerBonuses;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'targetUser', targetEntity: TaskSimulation::class)]
    private Collection $taskSimulations;

    #[ORM\OneToMany(mappedBy: 'addedBy', targetEntity: TaskCommentSimulation::class)]
    private Collection $taskCommentSimulations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: FileTask::class)]
    private Collection $fileTasks;

    #[ORM\OneToMany(mappedBy: 'openedBy', targetEntity: TaskSimulation::class)]
    private Collection $taskSimulationOpened;

    #[ORM\OneToMany(mappedBy: 'closedBy', targetEntity: TaskSimulation::class)]
    private Collection $taskSimulationsClosed;

    #[ORM\OneToMany(mappedBy: 'updatedBy', targetEntity: InverterPrice::class)]
    private Collection $inverterPrices;

    /**
     * @var Collection<int, InverterCablePrice>
     */
    #[ORM\OneToMany(mappedBy: 'updatedBy', targetEntity: InverterCablePrice::class)]
    private Collection $inverterCablePrices;

    use TimeStampTrait;

    public function __construct()
    {
        $this->simulations = new ArrayCollection();
        $this->histories = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->authTokens = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->statusSimulations = new ArrayCollection();
        $this->fileSimulations = new ArrayCollection();
        $this->surveysSent = new ArrayCollection();
        $this->sellerBonuses = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->taskSimulations = new ArrayCollection();
        $this->taskCommentSimulations = new ArrayCollection();
        $this->fileTasks = new ArrayCollection();
        $this->taskSimulationOpened = new ArrayCollection();
        $this->taskSimulationsClosed = new ArrayCollection();
        $this->inverterPrices = new ArrayCollection();
        $this->inverterCablePrices = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): static
    {
        $this->civility = $civility;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

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

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Simulation>
     */
    public function getSimulations(): Collection
    {
        return $this->simulations;
    }

    public function addSimulation(Simulation $simulation): static
    {
        if (!$this->simulations->contains($simulation)) {
            $this->simulations->add($simulation);
            $simulation->setOwnedBy($this);
        }

        return $this;
    }

    public function removeSimulation(Simulation $simulation): static
    {
        if ($this->simulations->removeElement($simulation)) {
            // set the owning side to null (unless already changed)
            if ($simulation->getOwnedBy() === $this) {
                $simulation->setOwnedBy(null);
            }
        }

        return $this;
    }

    #[Groups([
        'simulation:read', 'simulationAll:read', 'user:read', 'userAll:read', 'history:read', 'survey:read',
        'status_simulation:read', 'surveySimulation:read', 'task_simulation:read', 'task_comment_simulation:read',
        'file_task:read', 'inverter:read', 'inverterPrice:read', 'doc_category:read', 'file:export', 'panel:read', 'panelPrice:read',
        'pricing_factor_type:read', 'battery:read', 'batteryPrice:read', 'inverterCable:read', 'inverterCablePrice:read', 'simulation_charging_point:read'
    ])]
    public function getFullName(): string
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    use CivilitiesTrait;

    public function getDateBirthday(): ?\DateTimeImmutable
    {
        return $this->dateBirthday;
    }

    public function setDateBirthday(?\DateTimeImmutable $dateBirthday): static
    {
        $this->dateBirthday = $dateBirthday;

        return $this;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): static
    {
        $this->settings = $settings;

        return $this;
    }

    public function getUri(): ?string
    {
        return '/api/users/' . $this->getId();
    }

    /**
     * @return Collection<int, History>
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(History $history): static
    {
        if (!$this->histories->contains($history)) {
            $this->histories->add($history);
            $history->setUserId($this);
        }

        return $this;
    }

    public function removeHistory(History $history): static
    {
        if ($this->histories->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getUserId() === $this) {
                $history->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return Collection<int, AuthToken>
     */
    public function getAuthTokens(): Collection
    {
        return $this->authTokens;
    }

    public function addAuthToken(AuthToken $authToken): static
    {
        if (!$this->authTokens->contains($authToken)) {
            $this->authTokens->add($authToken);
            $authToken->setUser($this);
        }

        return $this;
    }

    public function removeAuthToken(AuthToken $authToken): static
    {
        if ($this->authTokens->removeElement($authToken)) {
            // set the owning side to null (unless already changed)
            if ($authToken->getUser() === $this) {
                $authToken->setUser(null);
            }
        }

        return $this;
    }

    #[Groups(['user:read', 'userAll:read'])]
    public function isActive(): bool
    {
        if ($this->getDeletedAt()) {
            return false;
        }
        return true;
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->email,
            $this->password,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->id,
            $this->email,
            $this->password,
        ] = $data;
    }

    /**
     * @return Collection<int, Survey>
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): static
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys->add($survey);
            $survey->setReviewedBy($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): static
    {
        if ($this->surveys->removeElement($survey)) {
            // set the owning side to null (unless already changed)
            if ($survey->getReviewedBy() === $this) {
                $survey->setReviewedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StatusSimulation>
     */
    public function getStatusSimulations(): Collection
    {
        return $this->statusSimulations;
    }

    public function addStatusSimulation(StatusSimulation $statusSimulation): static
    {
        if (!$this->statusSimulations->contains($statusSimulation)) {
            $this->statusSimulations->add($statusSimulation);
            $statusSimulation->setOwnedBy($this);
        }

        return $this;
    }

    public function removeStatusSimulation(StatusSimulation $statusSimulation): static
    {
        if ($this->statusSimulations->removeElement($statusSimulation)) {
            // set the owning side to null (unless already changed)
            if ($statusSimulation->getOwnedBy() === $this) {
                $statusSimulation->setOwnedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FileSimulation>
     */
    public function getFileSimulations(): Collection
    {
        return $this->fileSimulations;
    }

    public function addFileSimulation(FileSimulation $fileSimulation): static
    {
        if (!$this->fileSimulations->contains($fileSimulation)) {
            $this->fileSimulations->add($fileSimulation);
            $fileSimulation->setUser($this);
        }

        return $this;
    }

    public function removeFileSimulation(FileSimulation $fileSimulation): static
    {
        if ($this->fileSimulations->removeElement($fileSimulation)) {
            // set the owning side to null (unless already changed)
            if ($fileSimulation->getUser() === $this) {
                $fileSimulation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Survey>
     */
    public function getSurveysSent(): Collection
    {
        return $this->surveysSent;
    }

    public function addSurveysSent(Survey $surveysSent): static
    {
        if (!$this->surveysSent->contains($surveysSent)) {
            $this->surveysSent->add($surveysSent);
            $surveysSent->setUser($this);
        }

        return $this;
    }

    public function removeSurveysSent(Survey $surveysSent): static
    {
        if ($this->surveysSent->removeElement($surveysSent)) {
            // set the owning side to null (unless already changed)
            if ($surveysSent->getUser() === $this) {
                $surveysSent->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SellerBonus>
     */
    public function getSellerBonuses(): Collection
    {
        return $this->sellerBonuses;
    }

    public function addSellerBonus(SellerBonus $sellerBonus): static
    {
        if (!$this->sellerBonuses->contains($sellerBonus)) {
            $this->sellerBonuses->add($sellerBonus);
            $sellerBonus->setUser($this);
        }

        return $this;
    }

    public function removeSellerBonus(SellerBonus $sellerBonus): static
    {
        if ($this->sellerBonuses->removeElement($sellerBonus)) {
            // set the owning side to null (unless already changed)
            if ($sellerBonus->getUser() === $this) {
                $sellerBonus->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskSimulation>
     */
    public function getTaskSimulations(): Collection
    {
        return $this->taskSimulations;
    }

    public function addTaskSimulation(TaskSimulation $taskSimulation): static
    {
        if (!$this->taskSimulations->contains($taskSimulation)) {
            $this->taskSimulations->add($taskSimulation);
            $taskSimulation->setTargetUser($this);
        }

        return $this;
    }

    public function removeTaskSimulation(TaskSimulation $taskSimulation): static
    {
        if ($this->taskSimulations->removeElement($taskSimulation)) {
            // set the owning side to null (unless already changed)
            if ($taskSimulation->getTargetUser() === $this) {
                $taskSimulation->setTargetUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskCommentSimulation>
     */
    public function getTaskCommentSimulations(): Collection
    {
        return $this->taskCommentSimulations;
    }

    public function addTaskCommentSimulation(TaskCommentSimulation $taskCommentSimulation): static
    {
        if (!$this->taskCommentSimulations->contains($taskCommentSimulation)) {
            $this->taskCommentSimulations->add($taskCommentSimulation);
            $taskCommentSimulation->setAddedBy($this);
        }

        return $this;
    }

    public function removeTaskCommentSimulation(TaskCommentSimulation $taskCommentSimulation): static
    {
        if ($this->taskCommentSimulations->removeElement($taskCommentSimulation)) {
            // set the owning side to null (unless already changed)
            if ($taskCommentSimulation->getAddedBy() === $this) {
                $taskCommentSimulation->setAddedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FileTask>
     */
    public function getFileTasks(): Collection
    {
        return $this->fileTasks;
    }

    public function addFileTask(FileTask $fileTask): static
    {
        if (!$this->fileTasks->contains($fileTask)) {
            $this->fileTasks->add($fileTask);
            $fileTask->setUser($this);
        }

        return $this;
    }

    public function removeFileTask(FileTask $fileTask): static
    {
        if ($this->fileTasks->removeElement($fileTask)) {
            // set the owning side to null (unless already changed)
            if ($fileTask->getUser() === $this) {
                $fileTask->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskSimulation>
     */
    public function getTaskSimulationOpened(): Collection
    {
        return $this->taskSimulationOpened;
    }

    public function addTaskSimulationOpened(TaskSimulation $taskSimulationOpened): static
    {
        if (!$this->taskSimulationOpened->contains($taskSimulationOpened)) {
            $this->taskSimulationOpened->add($taskSimulationOpened);
            $taskSimulationOpened->setOpenedBy($this);
        }

        return $this;
    }

    public function removeTaskSimulationOpened(TaskSimulation $taskSimulationOpened): static
    {
        if ($this->taskSimulationOpened->removeElement($taskSimulationOpened)) {
            // set the owning side to null (unless already changed)
            if ($taskSimulationOpened->getOpenedBy() === $this) {
                $taskSimulationOpened->setOpenedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskSimulation>
     */
    public function getTaskSimulationsClosed(): Collection
    {
        return $this->taskSimulationsClosed;
    }

    public function addTaskSimulationsClosed(TaskSimulation $taskSimulationsClosed): static
    {
        if (!$this->taskSimulationsClosed->contains($taskSimulationsClosed)) {
            $this->taskSimulationsClosed->add($taskSimulationsClosed);
            $taskSimulationsClosed->setClosedBy($this);
        }

        return $this;
    }

    public function removeTaskSimulationsClosed(TaskSimulation $taskSimulationsClosed): static
    {
        if ($this->taskSimulationsClosed->removeElement($taskSimulationsClosed)) {
            // set the owning side to null (unless already changed)
            if ($taskSimulationsClosed->getClosedBy() === $this) {
                $taskSimulationsClosed->setClosedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InverterPrice>
     */
    public function getInverterPrices(): Collection
    {
        return $this->inverterPrices;
    }

    public function addInverterPrice(InverterPrice $inverterPrice): static
    {
        if (!$this->inverterPrices->contains($inverterPrice)) {
            $this->inverterPrices->add($inverterPrice);
            $inverterPrice->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeInverterPrice(InverterPrice $inverterPrice): static
    {
        if ($this->inverterPrices->removeElement($inverterPrice)) {
            // set the owning side to null (unless already changed)
            if ($inverterPrice->getUpdatedBy() === $this) {
                $inverterPrice->setUpdatedBy(null);
            }
        }

        return $this;
    }

    #[Groups(['userAll:read'])]
    public function hasGeneratedASimulation(): bool
    {
        /*
            foreach ($this->getSimulations() as $simulation) {
                if (!$simulation->getDeletedAt()) {
                    return true;
                }
            }
            return false;
        */
        return (count($this->getSimulations()) > 0);
    }

    /**
     * @return Collection<int, InverterCablePrice>
     */
    public function getInverterCablePrices(): Collection
    {
        return $this->inverterCablePrices;
    }

    public function addInverterCablePrice(InverterCablePrice $inverterCablePrice): static
    {
        if (!$this->inverterCablePrices->contains($inverterCablePrice)) {
            $this->inverterCablePrices->add($inverterCablePrice);
            $inverterCablePrice->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeInverterCablePrice(InverterCablePrice $inverterCablePrice): static
    {
        if ($this->inverterCablePrices->removeElement($inverterCablePrice)) {
            // set the owning side to null (unless already changed)
            if ($inverterCablePrice->getUpdatedBy() === $this) {
                $inverterCablePrice->setUpdatedBy(null);
            }
        }

        return $this;
    }
}
