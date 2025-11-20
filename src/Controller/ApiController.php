<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController extends AbstractController
{
    #[Route('/api/personnel/', name: 'api_personnel')]
    public function getPersonnel(Request $request, HttpClientInterface $http): JsonResponse
    {
        $matricule = $request->query->get('matricule');

        if (!$matricule) {
            return new JsonResponse(['error' => 'Matricule manquant'], 400);
        }

        $response = $http->request('GET', 'https://ton-api-rh.com/personnel?matricule='.$matricule);

        if ($response->getStatusCode() !== 200) {
            return new JsonResponse(['error' => 'Non trouvé'], 404);
        }

        $data = $response->toArray();

        return new JsonResponse([
            'nom' => $data['nom'] ?? null,
            'grade' => $data['grade'] ?? null,
            'found' => true
        ]);
    }
}
