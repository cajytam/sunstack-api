<?php

namespace App\Controller\Simulation;

use App\Entity\Simulation\Simulation;
use App\Repository\Simulation\SimulationRepository;
use App\Utils\Generator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateSimulation extends AbstractController
{
    public function __construct(
        private readonly SimulationRepository $simulationRepository
    )
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Simulation $simulation): Simulation
    {
        if (null === $simulation->getNumber()) {
            $number = Generator::getSimulationNumber($this->simulationRepository);
            $simulation->setNumber($number);
        }
        $name = null !== $simulation->getProfile() ? $simulation->getProfile()->getIdentifier() : 'SUNSTACK';

        $simulation->setName($name . '-' . $simulation->getNumber());

        return $simulation;
    }
}