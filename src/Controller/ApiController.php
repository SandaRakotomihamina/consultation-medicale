<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\PersonnelRepository;

class ApiController extends AbstractController
{
    #[Route('/api/personnel/{matricule}', name: 'api_personnel')]
    public function getPersonnelbyAPI($matricule, HttpClientInterface $http): JsonResponse {

        if (!$matricule) {
            return new JsonResponse(['error' => 'Matricule manquant'], 400);
        }

        $client = HttpClient::create([
            'timeout' => 30,
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $url = "https://192.168.56.104:7000/apigrh/client?keyword=BYMLE&mle=" . $matricule;

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'API_KEY',
                    'x-api-key'     => 'name@example.com',
                    'Accept'        => 'application/json',
                ]
            ]);
        }
        catch (\Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface $e) {
            return new JsonResponse(['error' => 'Timeout API RH'], 504);
        }
        catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur API RH : '.$e->getMessage()], 500);
        }

        $status = $response->getStatusCode();

        if ($status !== 200) {
            return new JsonResponse(['error' => 'Non trouvé'], 404);
        }

        $json = $response->toArray(false);
        $data = $json[0] ?? [];

        return new JsonResponse([
            'nom' => $data['NOMPERS'] . $data['PRENOM']?? null,
            'grade' => $data['ABREVGRADE'] ?? null,
            'found' => !empty($data),
        ]);
    }

    #[Route('/api/personnel-local/{matricule}', name: 'api_personnel_local')]
    public function getPersonnelLocal(string $matricule, PersonnelRepository $personnelRepository): JsonResponse
    {
        if (!$matricule) {
            return new JsonResponse(['error' => 'Matricule manquant'], 400);
        }

        $personnel = $personnelRepository->find($matricule);

        if (!$personnel) {
            return new JsonResponse(['found' => false], 404);
        }

        return new JsonResponse([
            'nom' => $personnel->getNom(),
            'grade' => $personnel->getGrade(),
            'found' => true,
        ]);
    }
}
