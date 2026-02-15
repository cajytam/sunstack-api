<?php

namespace App\Entity\Project;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\IdIntTrait;
use App\Repository\Project\SolarProjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SolarProjectRepository::class)]
#[ApiResource]
class SolarProject
{
    use IdIntTrait;
}
