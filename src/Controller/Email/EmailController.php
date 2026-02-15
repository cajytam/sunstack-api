<?php

namespace App\Controller\Email;

use App\Entity\Simulation\Simulation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/email', name: 'app_email_')]
class EmailController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/simulation/{identifier}', name: 'simulation_proposal', methods: ['GET', 'POST'])]
    public function sendSimulationProposal(
        Simulation      $simulation,
        MailerInterface $mailer,
        Request         $request,
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        $data['body'] = str_replace("\n", "<br>", $data['body']);

        $htmlBody = $this->renderView(
            'emails/simulation/send_proposal.html',
            [
                'SIMULATION_TEXT' => $data['body'],
                'BASE_URL' => $this->getParameter('base_url')
            ]
        );
        $pathPDF = $this->getParameter('assets_dir') . 'pdf/';

        $signatureBase64 = null;
        $signatures = $simulation->getSignatureSimulations();

        foreach ($signatures as $sign) {
            if ($sign->getPurpose() === 'devis') {
                $signature = $sign->getSignature();
                $signatureBase64 = $signature->getContent();
            }
        }

        if ($signatureBase64) {
            $cgvFile = $this->forward('App\Controller\PDF\AdministrativePDFController::generateCGV', [
                'simulation' => $simulation,
                'action' => 'onlyGenerate'
            ]);
        }

        $email = (new Email())
            ->from(Address::create('SunStack<noreply@cajytam.fr>'))
            ->to($data['to_address'])
            ->subject($data['subject'])
            ->replyTo(Address::create('Support SunStack<support@cajytam.fr>'))
            ->html($htmlBody)
            ->addPart(new DataPart(new File($data['fileURL']), 'Votre simulation personnalisée.pdf'));

        if ($signatureBase64 && $cgvFile->getContent()) {
            $email->addPart(
                new DataPart(new File(json_decode($cgvFile->getContent())->file), 'Nos conditions Générales de Vente.pdf')
            );
        }

        $mailer->send($email);

        return $this->json([
            'success' => true
        ]);
    }
}
