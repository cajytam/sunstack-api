<?php

namespace App\Entity\Simulation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Simulation\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    order: ['consumptionRate' => 'DESC'],
    security: "is_granted('ROLE_USER')"
),
]
#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
    use IdIntTrait;

    #[Groups(['simulation:read'])]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[Groups(['simulation:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $description = null;

    #[Groups(['simulation:read'])]
    #[ORM\Column]
    private ?float $consumptionRate = null;

    #[ORM\OneToMany(mappedBy: 'profile', targetEntity: Simulation::class)]
    private Collection $simulations;

    #[Groups(['simulation:read'])]
    #[ORM\Column(nullable: true)]
    private ?bool $isEligibleForBonus = null;

    #[Groups(['simulation:read'])]
    #[ORM\Column(length: 3, nullable: true)]
    private ?string $identifier = null;

    public function __construct()
    {
        $this->simulations = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
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

    public function getConsumptionRate(): ?float
    {
        return $this->consumptionRate;
    }

    public function setConsumptionRate(float $consumptionRate): static
    {
        $this->consumptionRate = $consumptionRate;

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
            $simulation->setProfile($this);
        }

        return $this;
    }

    public function removeSimulation(Simulation $simulation): static
    {
        if ($this->simulations->removeElement($simulation)) {
            // set the owning side to null (unless already changed)
            if ($simulation->getProfile() === $this) {
                $simulation->setProfile(null);
            }
        }

        return $this;
    }

    public function isIsEligibleForBonus(): ?bool
    {
        return $this->isEligibleForBonus;
    }

    public function setIsEligibleForBonus(?bool $isEligibleForBonus): static
    {
        $this->isEligibleForBonus = $isEligibleForBonus;

        return $this;
    }

    private function getResellPrices(\DateTimeImmutable $dateRaccordement): null|array
    {
        // Ordre du plus grand au plus petit, avec en clé le nombre de panneau "à partir de"
        $resellPrices = [
            '2023-01-01' => [
                'rvt_totale' => [
                    '<100' => [
                        3 => .2395,
                        9 => .2035,
                        36 => .1458,
                        100 => .1268,
                    ],
                    '<500' => [
                        'all' => .1312,
                    ]
                ],
                'rvt_avec_conso' => [
                    '<100' => [
                        9 => .1339,
                        100 => .0803,
                    ],
                    '<500' => [
                        'all' => .1312,
                    ]
                ]
            ],
            '2024-01-04' => [
                'rvt_totale' => [
                    '<100' => [
                        3 => .1735,
                        9 => .1474,
                        36 => .1382,
                        100 => .1202,
                    ],
                    '<500' => [
                        'all' => .1208,
                    ]
                ],
                'rvt_avec_conso' => [
                    '<100' => [
                        9 => .13,
                        100 => .078,
                    ],
                    '<500' => [
                        'all' => .1208
                    ]
                ]
            ],
            '2024-03-16' => [
                'rvt_totale' => [
                    '<100' => [
                        3 => .1657,
                        9 => .1409,
                        36 => .1363,
                        100 => .1185,
                    ],
                    '<500' => [
                        'all' => .1171,
                    ]
                ],
                'rvt_avec_conso' => [
                    '<100' => [
                        9 => .1297,
                        100 => .0778,
                    ],
                    '<500' => [
                        'all' => .1171
                    ]
                ]
            ],
            '2024-04-24' => [
                'rvt_totale' => [
                    '<100' => [
                        3 => .1430,
                        9 => .1215,
                        36 => .1355,
                        100 => .1178,
                    ],
                    '<500' => [
                        'all' => .1141,
                    ]
                ],
                'rvt_avec_conso' => [
                    '<100' => [
                        9 => .1301,
                        100 => .0781,
                    ],
                    '<500' => [
                        'all' => .1141
                    ]
                ]
            ],
        ];

        return static::findValueAfterDate($resellPrices, $dateRaccordement);
    }

    public function getInjectionPrice(
        int $year, float $power, float $injection, \DateTimeImmutable $dateRaccordement = new \DateTimeImmutable(), string $installationLocation = 'T'
    ): float
    {
        $resellPrices = static::getResellPrices($dateRaccordement);

        if ($year >= 21) {
            return 0;
        }

        if ($power <= 0) {
            return 0;
        }

        $montantInjection = 0;

        if ($installationLocation === 'S') {
            return $injection * 0.08;
        }

        if (floatval(0) === floatval($this->getConsumptionRate())) {

            if ($power <= 100) {
                $maxTarifTc = $power * 1600;

                $montantInjection += (min($injection, $maxTarifTc)) *
                    (match (true) {
                        $power <= 3 => $resellPrices['rvt_totale']['<100']['3'],
                        $power <= 9 => $resellPrices['rvt_totale']['<100']['9'],
                        $power <= 36 => $resellPrices['rvt_totale']['<100']['36'],
                        $power <= 100 => $resellPrices['rvt_totale']['<100']['100'],
                    });

                if ($injection > $maxTarifTc) {
                    $montantInjection += ($injection - $maxTarifTc) * 0.05;
                }
            } elseif ($power <= 500) {
                $maxTarifTc = $power * 1100;
                $montantInjection += (min($injection, $maxTarifTc)) * $resellPrices['rvt_totale']['<500']['all'];

                if ($injection > $maxTarifTc) {
                    $montantInjection += ($injection - $maxTarifTc) * 0.04;
                }
            } else {
                $montantInjection += $injection * 0.1208;
//                $montantInjection += $injection * 0.08;
            }

        } elseif (floatval(1) !== floatval($this->getConsumptionRate())) {

            if ($power <= 100) {
                $maxTarifTc = $power * 1600;

                $montantInjection += (min($injection, $maxTarifTc)) *
                    (match (true) {
                        $power <= 9 => $resellPrices['rvt_avec_conso']['<100']['9'],
                        $power <= 100 => $resellPrices['rvt_avec_conso']['<100']['100'],
                    });

                if ($injection > $maxTarifTc) {
                    $montantInjection += ($injection - $maxTarifTc) * 0.05;
                }
            } elseif ($power <= 500) {
                $maxTarifTc = $power * 1100;

                $montantInjection += (min($injection, $maxTarifTc)) * $resellPrices['rvt_avec_conso']['<500']['all'];

                if ($injection > $maxTarifTc) {
                    $montantInjection += ($injection - $maxTarifTc) * 0.04;
                }
            } else {
                $montantInjection += $injection * 0.1208;
//                $montantInjection += $injection * 0.08;
            }
        }
        return $montantInjection;
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

    private function findValueAfterDate($listPDCPrice, \DateTimeImmutable $dateRaccordement)
    {
        krsort($listPDCPrice);

        foreach ($listPDCPrice as $date => $values) {
            if ($date <= $dateRaccordement->format('Y-m-d')) {
                return $values;
            }
        }
        return null;
    }
}
