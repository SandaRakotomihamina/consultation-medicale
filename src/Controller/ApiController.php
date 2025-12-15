<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\PersonnelRepository;
use App\Repository\UserRepository;
use App\Repository\DemandeDeConsultationRepository;
use App\Repository\ConsultationListRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ApiController extends AbstractController
{
    
    #############################################################################################################
    #################################API pour personnel de la version en PROD####################################
    #############################################################################################################
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


    #############################################################################################################
    ##################################API pour personnel de la version en DEV####################################
    #############################################################################################################
    #[Route('/api/personnel-local/{matricule}', name: 'api_personnel_local')]
    public function getPersonnelLocal(string $matricule, PersonnelRepository $personnelRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

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

    #############################################################################################################
    #######################API pour vérifier l'existence d'un utilisateur########################################
    #############################################################################################################
    #[Route('/api/check-user-exists', name: 'api_check_user_exists', methods: ['POST'])]
    public function checkUserExists(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $matricule = $data['matricule'] ?? null;
        $username = $data['username'] ?? null;

        $errors = [];

        if ($matricule) {
            $existingUser = $userRepository->findOneBy(['Matricule' => $matricule]);
            if ($existingUser) {
                $errors['matricule'] = 'Un utilisateur avec le matricule  '. $matricule .' existe déjà.';
            }
        }

        if ($username) {
            $existingUser = $userRepository->findOneBy(['username' => $username]);
            if ($existingUser) {
                $errors['username'] = 'Ce nom d\'utilisateur est déjà utilisé.';
            }
        }

        return new JsonResponse([
            'exists' => !empty($errors),
            'errors' => $errors
        ]);
    }

    #############################################################################################################
    #######################API pour vérifier les nouvelles demandes de consultation##############################
    #############################################################################################################
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/demandes/check-new', name: 'api_check_new_demandes', methods: ['GET'])]
    public function checkNewDemandes(Request $request, DemandeDeConsultationRepository $demandeRepository): JsonResponse
    {

        $lastId = (int) $request->query->get('lastId', 0);
        
        $newDemandes = $demandeRepository->createQueryBuilder('d')
            ->where('d.id > :lastId')
            ->setParameter('lastId', $lastId)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();

        $html = '';
        $maxId = $lastId;
        foreach ($newDemandes as $demande) {
            if ($demande->getId() > $maxId) {
                $maxId = $demande->getId();
            }
            $html .= $this->renderView('main/demandes/_card.html.twig', [
                'demande' => $demande
            ]);
        }

        return new JsonResponse([
            'new' => count($newDemandes) > 0,
            'count' => count($newDemandes),
            'html' => $html,
            'lastId' => $maxId
        ]);
    }

    #############################################################################################################
    #######################API pour vérifier les nouvelles consultations#########################################
    #############################################################################################################
    #[Route('/api/consultations/check-new', name: 'api_check_new_consultations', methods: ['GET'])]
    public function checkNewConsultations(Request $request, ConsultationListRepository $consultationRepository): JsonResponse
    {

        $lastId = (int) $request->query->get('lastId', 0);
        
        $newConsultations = $consultationRepository->createQueryBuilder('c')
            ->where('c.id > :lastId')
            ->setParameter('lastId', $lastId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        $html = '';
        $maxId = $lastId;
        foreach ($newConsultations as $consultation) {
            if ($consultation->getId() > $maxId) {
                $maxId = $consultation->getId();
            }
            $html .= $this->renderView('main/consultations/_card.html.twig', [
                'consultation' => $consultation
            ]);
        }

        return new JsonResponse([
            'new' => count($newConsultations) > 0,
            'count' => count($newConsultations),
            'html' => $html,
            'lastId' => $maxId
        ]);
    }
}
