<?php

namespace App\Controller\File;

use App\Repository\Simulation\SimulationRepository;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Yectep\PhpSpreadsheetBundle\Factory;

#[Route('/api/export', name: 'app_export_')]
class ExportFileController extends AbstractController
{
    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    #[Route('/simulation', name: 'simulation', methods: ['POST'])]
    public function index(
        Request              $request,
        Factory              $factory,
        SimulationRepository $simulationRepository,
        SerializerInterface  $serializer
    ): Response
    {
        $parameters = $request->toArray();

        $fileFormat = array_key_exists('format', $parameters) ? $parameters['format'] : 'Xlsx';

        // Génération des filtres pour le findBy en fonction des paramètres de l'url du front
        $conditions = [];
        if (array_key_exists('ownedBy', $parameters['query']) && count($parameters['query']['ownedBy']) > 0) {
            $conditions['ownedBy'] = $parameters['query']['ownedBy'];
        }
        if (array_key_exists('exists[deletedAt]', $parameters['query'])) {
            $conditions['deletedAt'] = null;
        }

        $simulations = $simulationRepository->findBy(
            $conditions,
            ['id' => 'DESC']
        );

        $jsonContent = $serializer->serialize(
            $simulations,
            'json',
            ['groups' => ['file:export', 'read:id', 'timestamp:read']]
        );
        $rawData = [];
        if ($jsonContent) {
            $rawData = json_decode($jsonContent);
        }

        $spreadsheet = $factory->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Export');

        $data = static::generateDataArray($rawData);

        $columnsMap = [];

        $lineIndex = 2;
        foreach ($data as $line) {
            foreach ($line as $columnName => $columnValue) {
                if (str_starts_with($columnName, 'Date') && $columnValue) {
                    $columnValue = Date::PHPToExcel($columnValue);
                }
                if (is_int($columnIndex = array_search($columnName, $columnsMap))) {
                    $columnIndex++;
                } else {
                    $columnsMap[] = $columnName;
                    $columnIndex = count($columnsMap);
                }
                $sheet->getCell([$columnIndex, $lineIndex])->setValue($columnValue);
            }
            $lineIndex++;
        }
        foreach ($columnsMap as $columnMapId => $columnTitle) {
            $sheet->getCell([$columnMapId + 1, 1])->setValue($columnTitle);
        }

        $sheet->getStyle("G:I")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);

        switch ($fileFormat) {
            case 'Csv':
                $writer = new Csv($spreadsheet);
                $writer->setDelimiter(';');
                $writer->setEnclosure('"');
                $writer->setLineEnding("\r\n");
                $writer->setSheetIndex(0);
                $writer->setUseBOM(true);
                break;
            case 'Xlsx':
                $writer = new Xlsx($spreadsheet);
                break;
            default:
                $writer = $factory->createWriter($spreadsheet, $fileFormat);
        }

        ob_start();
        $writer->save('php://output');

        return new Response(
            ob_get_clean(),  // read from output buffer
            200,
            array(
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="doc.xlsx"',
            )
        );
    }

    /**
     * @throws \Exception
     */
    private function generateDataArray(array $rawData): array
    {
        $result = [];
        foreach ($rawData as $d) {
            $columns = [];
            $columns['Numéro devis'] = $d->name;
            $columns['Client'] = $d->customerName;
            $columns['Commercial'] = $d->ownedBy?->fullName;
            $columns['Statut sales'] = static::getDetailStatus($d->statusSimulations, 'Sales') ?: 'Devis généré';
            $columns['Statut tech'] = static::getDetailStatus($d->statusSimulations, 'Survey');
            $columns['Statut admin'] = static::getDetailStatus($d->statusSimulations, 'Back-Office');
            $columns['Date de création du devis'] = (new \DateTime($d->createdAt))->format('d/m/Y');
            $columns['Date de dernière mise à jour'] =
                $d->latestStatus?->createdAt
                    ? (new \DateTime($d->latestStatus?->createdAt))->format('d/m/Y')
                    : null;
            $columns['Date de signature'] = $d->signedAt ? (new \DateTime($d->signedAt))->format('d/m/Y') : null;
            $columns['Nombre de panneaux'] = $d->nbPanelsTotal ?: null;
            $columns['Type de panneaux'] = $d->panel?->power ? intval($d->panel->power) : null;
            $columns['Prix HT'] = $d->manualPrice ?: ($d->finalPriceHT ?: $d->installationPriceHT);

            $result[] = $columns;
        }
        return $result;
    }

    private function getLatestStatus(array $arrayOfStatus, string $category): \stdClass|null
    {
        foreach ($arrayOfStatus as $status) {
            if ($status->status->statusGroup->name === $category) {
                return $status;
            }
        }
        return null;
    }

    private function getDetailStatus($arrayOfStatus, string $category): string
    {
        $status = static::getLatestStatus($arrayOfStatus, $category);

        if (!$status) {
            return '';
        }
        $txt = '';
        $txt .= $status->status->name;

        if ($status->optionSelected) {
            $txt .= " : $status->optionSelected";
        }

        if ($status->reasonEvent) {
            $txt .= " : $status->reasonEvent";
        }

        if ($status->dateEvent) {
            $txt .= " : " . (new \DateTime($status->dateEvent))->format('d/m/Y');
        }

        return $txt;
    }
}
