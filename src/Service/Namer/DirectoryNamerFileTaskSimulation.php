<?php

namespace App\Service\Namer;

use App\Entity\Simulation\FileTask;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class DirectoryNamerFileTaskSimulation implements DirectoryNamerInterface
{
    /**
     * @param FileTask $object
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        return $object->getTask()->getSimulation()->getIdentifier();
    }
}