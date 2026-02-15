<?php

namespace App\Service\Namer;

use App\Entity\Survey\FileSurvey;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class DirectoryNamerFileSurvey implements DirectoryNamerInterface
{
    /**
     * @param FileSurvey $object
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        return $object->getSurvey()->getSimulation()->getIdentifier();
    }
}