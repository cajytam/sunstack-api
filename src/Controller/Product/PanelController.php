<?php

namespace App\Controller\Product;

use App\Repository\Product\PanelRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/panels', name: 'api_panel_')]
class PanelController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/get-active/{installationType}/{dateForced}', name: 'main', methods: ['GET'])]
    public function getActivePanel(
        string          $installationType,
        PanelRepository $panelRepository,
        string|null     $dateForced = null,
    ): Response
    {
        if ($dateForced) $dateForced = new \DateTimeImmutable($dateForced);

        $panel = $panelRepository->getActivePanel($installationType, $dateForced);

        return $this->json($panel);
    }
}
