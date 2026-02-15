<?php

namespace App\Controller\File;

use App\Entity\Documentation\DocFile;
use App\Entity\Documentation\DocHistory;
use App\Entity\Simulation\FileSimulation;
use App\Entity\Simulation\FileTask;
use App\Entity\Survey\FileSurvey;
use App\Repository\Documentation\DocHistoryRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Imagine\Gd\Imagine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/api/file', name: 'app_file_')]
class FileController extends AbstractController
{
    #[IsGranted('SURVEY_DOC_VIEW', 'fileSurvey')]
    #[Route('/survey/{id}/rotate/{direction}', name: 'survey_rotate')]
    public function rotateSurveyFile(
        FileSurvey $fileSurvey,
        string     $direction
    ): Response
    {
        $identifier = $fileSurvey->getSurvey()->getSimulation()->getIdentifier();
        $projectRoot = $this->getParameter('kernel.project_dir');

        $filepath = "$projectRoot/uploads/$identifier/" . $fileSurvey->getFilename();

        $imagine = new Imagine();
        $image = $imagine->open($filepath);
        $angle = $direction === 'left' ? -90 : 90;
        $image->rotate($angle);

        $image->save($filepath);

        return $this->json([
            'success' => true
        ]);
    }

    #[IsGranted('SURVEY_DOC_VIEW', 'fileSurvey')]
    #[Route('/survey/{id}/{mode}', name: 'survey')]
    public function renderSurveyFile(
        FileSurvey  $fileSurvey,
        string|null $mode = null
    ): Response
    {
        $identifier = $fileSurvey->getSurvey()->getSimulation()->getIdentifier();
        $projectRoot = $this->getParameter('kernel.project_dir');

        $filepath = "$projectRoot/uploads/$identifier/" . $fileSurvey->getFilename();
        $name = $fileSurvey->getSurvey()->getSimulation()->getName() . '-' . $fileSurvey->getSurvey()->getSurveyItem()->getName();
        return $this->extracted($filepath, $mode, $name);
    }

    #[IsGranted('TASK_DOC_VIEW', 'fileTask')]
    #[Route('/task/{id}/{mode}', name: 'task')]
    public function renderTaskFile(
        FileTask    $fileTask,
        string|null $mode = null
    ): Response
    {
        $identifier = $fileTask->getTask()->getSimulation()->getIdentifier();
        $projectRoot = $this->getParameter('kernel.project_dir');

        $filepath = "$projectRoot/uploads/$identifier/" . $fileTask->getFilename();
        $name = $fileTask->getFilename();
        return $this->extracted($filepath, $mode, $name);
    }

    #[IsGranted('SIMULATION_DOC_VIEW', 'fileSimulation')]
    #[Route('/simulation/{id}/{mode}', name: 'simulation')]
    public function renderSimulationFile(
        FileSimulation $fileSimulation,
        string|null    $mode = null
    ): Response
    {
        $identifier = $fileSimulation->getSimulation()->getIdentifier();
        $projectRoot = $this->getParameter('kernel.project_dir');

        $filepath = "$projectRoot/uploads/$identifier/" . $fileSimulation->getFilename();
        $name = $fileSimulation->getLabel();
        return $this->extracted($filepath, $mode, $name);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[IsGranted('DOCUMENTATION_VIEW', 'docFile')]
    #[Route('/documentation/{id}/{mode}', name: 'documentation')]
    public function renderDocumentationFile(
        DocFile              $docFile,
        DocHistoryRepository $docHistoryRepository,
        string|null          $mode = null,
    ): Response
    {
        $isEverOpenedDocument = $docHistoryRepository->findIsUserEverInteract(
            $this->getUser()->getId(),
            $docFile->getId()
        );

        if (!$isEverOpenedDocument) {
            $docFileHistory = new DocHistory();
            $docFileHistory
                ->setAction($mode === 'display' ? 1 : 2)
                ->setDocument($docFile)
                ->setUser($this->getUser())
            ;
            $docHistoryRepository->save($docFileHistory, true);
        }
        $projectRoot = $this->getParameter('kernel.project_dir');

        $filepath = "$projectRoot/uploads/docs/" . $docFile->getPath();
        $fullName = $docFile->getName() . '.pdf';
        $filename = pathinfo($fullName, PATHINFO_FILENAME);
        return $this->extracted($filepath, $mode, $filename);
    }

    /**
     * @param string $filepath
     * @param string|null $mode
     * @param string|null $name
     * @return BinaryFileResponse
     */
    public function extracted(string $filepath, ?string $mode, ?string $name): BinaryFileResponse
    {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $slugger = new AsciiSlugger();

        if ($mode === 'display') {
            $response = new BinaryFileResponse($filepath);
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $slugger->slug($name) . ".$ext"
            );
            $response->headers->set('Content-Type', mime_content_type($filepath));
            $response->headers->set('Content-Disposition', $disposition);
            return $response;
        } else {
            return $this->file($filepath, $slugger->slug($name) . ".$ext");
        }
    }
}
