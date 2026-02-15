<?php

namespace App\Controller\Api;

use App\Repository\Customer\CompanyTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api', name: 'app_api_')]
class ApiController extends AbstractController
{
    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route('/siret/{siret}', name: 'informations_siret')]
    public function getInformationsFromSIRET(
        HttpClientInterface   $opendatasoftClient,
        CompanyTypeRepository $companyTypeRepository,
        string                $siret
    ): Response
    {
        $response = $opendatasoftClient->request(
            'GET',
            '',
            [
                'query' => [
                    'dataset' => 'economicref-france-sirene-v3',
                    'q' => 'siret=' . $siret
                ]
            ]
        );

        if (0 === $response->toArray()['nhits']) {
            return $this->json([
                'error' => 'Aucune donnée trouvée pour le SIRET <strong>' . $siret . '</strong>',
            ],
                Response::HTTP_BAD_REQUEST);
        }

        $d = $response->toArray()['records'][0];

        $data = [];
        $data['siret'] = array_key_exists('siret', $d['fields']) ? $d['fields']['siret'] : null;

        // company name
        $companyName = array_key_exists('denominationunitelegale', $d['fields']) ? $d['fields']['denominationunitelegale'] : null;
        $data['companyName'] = $companyName;

        //denomination
        $denomination = array_key_exists('denominationusuelleetablissement', $d['fields']) ? $d['fields']['denominationusuelleetablissement'] : null;
        $data['denomination'] = $denomination;

        $data['addressNumber'] = array_key_exists('numerovoieetablissement', $d['fields']) ? $d['fields']['numerovoieetablissement'] : null;
        $address = trim(
            (array_key_exists('typevoieetablissement', $d['fields']) ? $d['fields']['typevoieetablissement'] : '')
            . ' ' .
            (array_key_exists('libellevoieetablissement', $d['fields']) ? $d['fields']['libellevoieetablissement'] : '')
        );
        $data['addressName'] = strlen($address) > 0 ? $address : null;
        $data['addressCity'] = array_key_exists('libellecommuneetablissement', $d['fields']) ? $d['fields']['libellecommuneetablissement'] : null;
        $data['addressPostCode'] = array_key_exists('codepostaletablissement', $d['fields']) ? $d['fields']['codepostaletablissement'] : null;

        // date fermeture
        $data['estFerme'] =
            array_key_exists('etatadministratifetablissement', $d['fields']) && !(($d['fields']['etatadministratifetablissement'] === 'Actif'));
        $data['dateFermeture'] = array_key_exists('datefermetureunitelegale', $d['fields']) ? date_format(date_create($d['fields']['datefermetureunitelegale']), "d/m/Y") : null;

        // nature juridique
        $natureJuridique = array_key_exists('categoriejuridiqueunitelegale', $d['fields']) ? $d['fields']['categoriejuridiqueunitelegale'] : null;
        $nomNatureJuridique = array_key_exists('naturejuridiqueunitelegale', $d['fields']) ? $d['fields']['naturejuridiqueunitelegale'] : null;
        $uriNatureJuridique = null;
        if (null !== $natureJuridique) {
            $result = $companyTypeRepository->findCompanyTypeByCategorieJuridique($natureJuridique);
            if ($result) {
                $nomNatureJuridique = $result[0]->getName();
                $uriNatureJuridique = '/api/company_types/' . $result[0]->getID();
            }
        }

        $data['codeCategorieJuridique'] = $natureJuridique;
        $data['nomCategorieJuridique'] = $nomNatureJuridique;
        $data['uriCategorieJuridique'] = $uriNatureJuridique;
        if ($uriNatureJuridique && $nomNatureJuridique) {
            $data['objectCategorieJuridique'] = [
                'id' => $uriNatureJuridique,
                'name' => $nomNatureJuridique
            ];
        }
        $data['pdlLongitude'] = array_key_exists('geometry', $d) ? $d['geometry']['coordinates'][0] : null;
        $data['pdlLatitude'] = array_key_exists('geometry', $d) ? $d['geometry']['coordinates'][1] : null;

        return $this->json([
            'data' => $data
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/searchAddress/{addressSearch}', name: 'search_address')]
    public function getAddressSuggestions(
        HttpClientInterface $gouvadresseClient,
        string              $addressSearch
    ): JsonResponse
    {
        $addressSearch = str_replace(' ', '+', $addressSearch);
        $response = $gouvadresseClient->request(
            'GET',
            '',
            [
                'query' => [
                    'q' => $addressSearch,
                    'limit' => 7
                ]
            ]
        );

        return $this->json([
            'data' => $response->toArray()['features']
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/pvgis/radiation', name: 'search_radiation', methods: ['POST'])]
    public function getPvgisSearchRadiation(
        HttpClientInterface $pvgisClient,
        Request             $request
    ): JsonResponse
    {
        $parameters = $request->toArray();
        $inclinaisonPanel = $parameters['inclinaison'];
        $azimuthPanel = $parameters['azimuth'];
        $latitude = $parameters['lat'];
        $longitude = $parameters['lon'];
        $totalPowerKva = array_key_exists('totalPowerKva', $parameters) ? $parameters['totalPowerKva'] : 1;

        $response = $pvgisClient->request(
            'GET',
            'PVcalc',
            [
                'query' => [
                    'outputformat' => 'json',
                    'raddatabase' => 'PVGIS-SARAH2',
                    'mountingplace' => 'free',
                    'pvtechchoice' => 'crystSi',
                    'peakpower' => $totalPowerKva,
                    'loss' => 14,
                    'angle' => $inclinaisonPanel,
                    'aspect' => $azimuthPanel,
                    'lat' => $latitude,
                    'lon' => $longitude
                ]
            ]
        );

        $result = $response->toArray();

        $data = [];
        $data['monthly'] = $result['outputs']['monthly']['fixed'];
        $data['total'] = $result['outputs']['totals']['fixed'];

        return $this->json($data);
    }

    //https://re.jrc.ec.europa.eu/api/v5_2/tmy?lat=50.294&lon=2.779&browser=0&startyear=2010&endyear=2020&outputformat=json

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/pvgis/tmy/{latitude}/{longitude}', name: 'search_tmy', methods: ['GET'])]
    public function getPvgisSearchTmy(
        float                    $latitude,
        float                    $longitude,
        HttpClientInterface|null $pvgisClient,
    ): JsonResponse
    {
        $response = $pvgisClient->request(
            'GET',
            'tmy',
            [
                'query' => [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'startyear' => 2010,
                    'endyear' => 2020,
                    'outputformat' => 'json',
                ]
            ]
        );

        $result = $response->toArray();
        $tempMin = 50;
        $tempMinDate = null;
        foreach ($result['outputs']['tmy_hourly'] as $data) {
            if ($tempMin > $data['T2m']) {
                $tempMin = $data['T2m'];
                $tempMinDate = $data['time(UTC)'];
            }
        }

        return $this->json([
            'température minimale' => $tempMin,
            'date température minimale' => $tempMinDate
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/geogouv/{codeInseeVille}', name: 'api_geo_gouv', methods: ['GET'])]
    public function getApiGeoGouv(
        string                   $codeInseeVille,
        HttpClientInterface|null $geoapigouvcommunesClient,
    ): JsonResponse
    {
        $response = $geoapigouvcommunesClient->request(
            'GET',
            "communes/$codeInseeVille",
            [
                'query' => [
                    'fields' => 'nom,code,codesPostaux,centre,departement',
                    'format' => 'json',
                    'geometry' => 'centre',
                ]
            ]
        );

        $result = $response->toArray();

        return $this->json($result);
    }
}
