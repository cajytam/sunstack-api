<?php

namespace App\Controller\PDF;

use App\Entity\Simulation\Simulation;
use App\Factory\File\FileFactory;
use App\Factory\File\PDFFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class AdministrativePDFController extends AbstractController
{
    #[Route('/pdf/mandate/representation/{identifier}', name: 'app_pdf_representation_mandate', methods: ['GET'])]
    #[IsGranted('PDF_SIMULATION', 'simulation')]
    public function generatePDFRepresentationMandate(
        Simulation  $simulation,
        string|null $action = null,
        string      $signatureType = 'mandat-representation'
    ): Response
    {
        $pdfFilename = 'mandat-representation-' . $simulation->getName() . '.pdf';
        $generateDir = $this->getParameter('kernel.cache_dir') . '/simulation/pdf/';
        $fullName = $generateDir . $pdfFilename;

        FileFactory::createDir($generateDir);

        $mandatSource = $this->getParameter('assets_dir') . "pdf/mandat-mon-expert-solaire.pdf";

        $pdf = new PDFFactory(
            $mandatSource,
            [
                'font-family' => 'Poppins',
                'font-size' => 10,
            ],
        );

        $pdf->page(1);

        $customer = $simulation->getTempCustomer() ?: $simulation->getCustomer();

        $signatureBase64 = null;
        $signatureDate = null;
        $signatures = $simulation->getSignatureSimulations();

        $signature = null;
        foreach ($signatures as $sign) {
            if ($sign->getPurpose() === $signatureType || ($simulation->getCreatedAt() > (new \DateTime('2023-10-16')) && $sign->getPurpose() === 'devis')) {
                $signature = $sign->getSignature();

                $signatureBase64 = $signature->getContent();
                if ($signature->getUpdatedAt()) {
                    $signatureDate = $signature->getUpdatedAt()->format('d/m/Y');
                } else {
                    $signatureDate = $signature->getCreatedAt()->format('d/m/Y');
                }
            }
        }

        // si particulier
        if ($customer->getCustomerType() === 1) {
            $clientName = strtoupper($customer->getFullName());

            $tmpStreet = trim(($customer?->getStreetNumber() ? $customer->getStreetNumber() : '') . ' ' . $customer->getStreetName());
            $tmpCity = trim($customer->getStreetCity() . ' ' . $customer->getStreetPostcode());
            $clientParticularAddress = $tmpStreet . ' - ' . $tmpCity;
            $pdf
                ->addText(
                    texte: $clientName,
                    posX: 55,
                    posY: 58
                )
                ->addText(
                    texte: $signature && $signature->getBirthday() ? $signature->getBirthday()->format('d/m/Y') : '',
                    posX: 36,
                    posY: 65,
                )
                ->addText(
                    texte: $signature && $signature->getBirthPlace() ? $signature->getBirthPlace() : '',
                    posX: 70,
                )
                ->addText(
                    texte: trim($clientParticularAddress),
                    posX: 45,
                    posY: 72.5,
                );

            if ($customer->getCivility()) {
                if (strtolower($customer->getCivility()) === 'mme') {
                    $barWidth = 3;
                    $barX = 18.5;
                } else {
                    $barWidth = 8;
                    $barX = 25;
                }
                $pdf->drawRectagle(
                    $barX,
                    57,
                    $barWidth,
                    .5,
                    [0, 0, 0]
                );
            }
        } else {
            // si professionnel
            $clientCompanyName = strtoupper(trim($customer->getLastname() . ' ' . $customer->getFirstname()));
            $clientCompany['name'] = $customer->getFullName() ?: '';
            $clientCompany['siret'] = $customer->getSiret() ?: '';
            /*            $tmpStreet = trim($customer->getStreetNumber() . ' ' . $customer->getStreetName());
                        $tmpCity = trim($customer->getStreetCity() . ' ' . $customer->getStreetPostcode());
                        $clientCompanyAddress = $tmpStreet . ' - ' . $tmpCity;
            */
            $pdf
                ->addText(
                    texte: $clientCompany['name'],
                    posX: 35,
                    posY: 88
                )
                ->addText(
                    texte: $clientCompany['siret'],
                    posX: 172,
                )
                ->addText(
                    texte: $clientCompanyName,
                    posX: 62,
                    posY: 95
                );
            if ($customer->getCivility()) {
                if (strtolower($customer->getCivility()) === 'mme') {
                    $barWidth = 4;
                    $barX = 44;
                } else {
                    $barWidth = 8;
                    $barX = 51;
                }
                $pdf->drawRectagle(
                    $barX,
                    94,
                    $barWidth,
                    .5,
                    [0, 0, 0]
                );
            }

            if ($customer->getPosition()) {
                $pdf->addText(
                    texte: trim($customer->getPosition()),
                    posX: 180,
                    posY: 95,
                    params: [
                        'font-size' => 9
                    ]
                );
            }
        }

        // PDL
        if ($simulation->isIsSameAddresses()) {
            $pdlStreet = ($customer->getStreetNumber() !== null ? $customer->getStreetNumber() : '')
                . ' ' . $customer->getStreetName()
                . ' - ' . $customer->getStreetPostCode()
                . ' ' . $customer->getStreetCity();
        } else {
            $pdlStreet =
                $simulation->getInstallationStreetNumber()
                . ' ' . $simulation->getInstallationStreetName()
                . ' - ' . $simulation->getInstallationStreetPostcode()
                . ' ' . $simulation->getInstallationStreetCity();
        }

        $pdf
            ->addText(
                texte: $pdlStreet,
                posX: 85,
                posY: 192.5
            );

        // signature et le reste
        $pdf->page(2)
            ->addText(
                texte: $customer->getStreetCity(),
                posX: 32,
                posY: 215,
            )
            ->addText(
                texte: $signatureDate ?: (new \DateTime())->format('d/m/Y'),
                posY: 221,
            )
            ->addText(
                texte: 'Roubaix',
                posX: 96,
                posY: 215,
            )
            ->addText(
                texte: $signatureDate ?: (new \DateTime())->format('d/m/Y'),
                posY: 221,
            )
            ->addText(
                texte: $signatureDate ?: (new \DateTime())->format('d/m/Y'),
                posX: 155,
            );

        if ($signatureBase64) {
            $pdf
                ->addImageBase64(
                    contentBase64: $signatureBase64,
                    width: 50,
                    height: 50,
                    posX: 18,
                    posY: 226
                );
        }

        return $this->generatePDF($pdf, $generateDir, $pdfFilename, $action, $fullName);
    }
    
    #[Route('/pdf/cgv/{identifier}', name: 'app_pdf_cgv', methods: ['GET'])]
    #[IsGranted('PDF_SIMULATION', 'simulation')]
    public function generateCGV(
        Simulation  $simulation,
        string|null $action = null,
    ): Response
    {
        $cgvSource = $this->getParameter('assets_dir') . "pdf/cgv.pdf";
        $pdfFilename = 'cgv-' . $simulation->getName() . '.pdf';
        $generateDir = $this->getParameter('kernel.cache_dir') . '/simulation/pdf/';

        $fullName = $generateDir . $pdfFilename;

        FileFactory::createDir($generateDir);

        $signatureBase64 = null;
        $signatures = $simulation->getSignatureSimulations();

        foreach ($signatures as $sign) {
            if ($sign->getPurpose() === 'devis') {
                $signature = $sign->getSignature();
                $signatureBase64 = $signature->getContent();
            }
        }

        $pdf = new PDFFactory(
            $cgvSource,
            [
                'font-family' => 'Poppins',
                'font-size' => 10,
            ],
        );

        for ($i = 1; $i < $pdf->getNbPages(); $i++) {
            $pdf->page($i);

            if ($signatureBase64) {

                $posY = $i === 9 ? 200 : 270;

                $pdf
                    ->addImageBase64(
                        contentBase64: $signatureBase64,
                        width: 50,
                        height: 50,
                        posX: 160,
                        posY: $posY
                    );
            }
        }

        return $this->generatePDF($pdf, $generateDir, $pdfFilename, $action, $fullName);
    }

    /**
     * @param PDFFactory $pdf
     * @param string $generateDir
     * @param string $pdfFilename
     * @param string|null $action
     * @param string $fullName
     * @return BinaryFileResponse|JsonResponse
     */
    public function generatePDF(PDFFactory $pdf, string $generateDir, string $pdfFilename, ?string $action, string $fullName): JsonResponse|BinaryFileResponse
    {
        $pdf->savePDFOnServer(
            $generateDir,
            $pdfFilename
        );

        if ("onlyGenerate" === $action) {
            return $this->json([
                'file' => $generateDir . $pdfFilename
            ]);
        }

        $response = new BinaryFileResponse($fullName);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            $pdfFilename
        );
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
