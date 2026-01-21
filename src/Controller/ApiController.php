<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PersonnelRepository;
use App\Repository\UserRepository;
use App\Repository\DemandeDeConsultationRepository;
use App\Repository\ConsultationListRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use \Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;

class ApiController extends AbstractController
{
    
    #############################################################################################################
    #################################API pour personnel de la version en PROD####################################
    #############################################################################################################
    #[Route('/api/personnel/{matricule}', name: 'api_personnel')]
    public function getPersonnelbyAPI($matricule): JsonResponse {

        if (!$this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        if (!$matricule) {
            return new JsonResponse(['error' => 'Matricule manquant'], 400);
        }

        $client = HttpClient::create([
            'timeout' => 5,
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $url = "http://10.254.52.116:7000/apigrh/client?keyword=BYMLE&mle=" . $matricule;

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'rYihKSmzx8NiI4koQgaldspnQR9tXGQhw',
                    'x-api-key'     => 'sm@gendarmerie.gov.mg',
                    'Accept'        => 'application/json',
                ]
            ]);
        }
        catch (TimeoutExceptionInterface $e) {
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
            'nom' => ($data['NOMPERS'] ?? '') . ' ' . ($data['PRENOM'] ?? ''),
            'grade' => $data['ABREVGRADE'] ?? null,
            'LIBUTE' => $data['UNITE'] ?? null,
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
            'LIBUTE' => $personnel->getLIBUTE(),
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
    #################################API pour unité de la version PROD###########################################
    #############################################################################################################
    #[Route('/api/unite-search', name: 'api_unite_search_suggestions', methods: ['GET'])]
    public function searchUniteSuggestions(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $searchTerm = $request->query->get('q', '');

        if (strlen($searchTerm) < 2) {
            return new JsonResponse(['suggestions' => []]);
        }

        $client = HttpClient::create([
            'timeout' => 5,
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $url = 'http://10.254.52.116:7000/apigrh/client?keyword=ALLUTEGN';

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'rYihKSmzx8NiI4koQgaldspnQR9tXGQhw',
                    'x-api-key'     => 'sm@gendarmerie.gov.mg',
                    'Accept'        => 'application/json',
                ]
            ]);
        }
        catch (TimeoutExceptionInterface $e) {
            return new JsonResponse(['error' => 'Timeout API Unite'], 504);
        }
        catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur API Unite : '.$e->getMessage()], 500);
        }

        $status = $response->getStatusCode();

        if ($status !== 200) {
            return new JsonResponse(['suggestions' => []]);
        }

        $json = $response->toArray(false);
        $unites = is_array($json) ? $json : [];

        // Rechercher dans la liste complète des unités
        $searchTermUpper = strtoupper($searchTerm);
        $suggestions = [];
        
        foreach ($unites as $unite) {
            $unity = strtoupper($unite['UNITY'] ?? '');
            if (strpos($unity, $searchTermUpper) !== false) {
                $suggestions[] = [
                    'CODUTE' => $unite['CODUTE'] ?? null,
                    'UNITY' => $unite['UNITY'] ?? '',
                ];
            }
            if (count($suggestions) >= 10) {
                break;
            }
        }

        return new JsonResponse(['suggestions' => $suggestions]);
    }


    #############################################################################################################
    ####################################API pour unité de la version DEV#########################################
    #############################################################################################################
    #[Route('/api/unite-search-local', name: 'api_unite_search')]
    public function searchUnite(Request $request, \App\Repository\UniteRepository $uniteRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $searchTerm = (string) $request->query->get('q', '');

        if (strlen($searchTerm) < 2) {
            return new JsonResponse(['suggestions' => []]);
        }

        $qb = $uniteRepository->createQueryBuilder('u')
            ->where('LOWER(u.LIBUTE) LIKE :q OR LOWER(u.LOCAL) LIKE :q')
            ->setParameter('q', '%'.strtolower($searchTerm).'%')
            ->setMaxResults(10);

        $results = $qb->getQuery()->getResult();

        $suggestions = [];
        foreach ($results as $u) {
            $libute = method_exists($u, 'getLIBUTE') ? $u->getLIBUTE() : (property_exists($u, 'LIBUTE') ? $u->LIBUTE : '');
            $local = method_exists($u, 'getLOCAL') ? $u->getLOCAL() : (property_exists($u, 'LOCAL') ? $u->LOCAL : '');
            $codute = method_exists($u, 'getCODUTE') ? $u->getCODUTE() : (property_exists($u, 'CODUTE') ? $u->CODUTE : null);

            $unity = trim(($libute ?? '') . ' ' . ($local ?? ''));

            $suggestions[] = [
                'CODUTE' => $codute,
                'UNITY' => $unity,
            ];
        }

        return new JsonResponse(['suggestions' => $suggestions]);
    }


    #############################################################################################################
    ############################API pour vérifier l'existence d'une unité########################################
    #############################################################################################################
    #[Route('/api/check-unite-exists', name: 'api_check_unite_exists', methods: ['POST'])]
    public function checkUniteExists(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $codute = $data['codute'] ?? null;
        $libute = $data['libute'] ?? null;
        $local = $data['local'] ?? null;

        $errors = [];

        if ($codute && $libute && $local) {
            $existingUser = $userRepository->findOneBy([
                'CODUTE' => $codute,
                'LIBUTE' => $libute,
                'LOCAL' => $local
            ]);
            if ($existingUser) {
                $errors['unite'] = 'Un compte existe déjà pour l\'unité '. $libute . ' ' . $local .' - ' . $codute .'.';
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
        
        $qb = $consultationRepository->createQueryBuilder('c')
            ->where('c.id > :lastId')
            ->setParameter('lastId', $lastId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(4);

        // Si l'utilisateur est ROLE_USER, ne retourner que les consultations de la même LIBUTE
        if ($this->isGranted('ROLE_USER')) {
            $user = $this->getUser();
            $libute = ($user instanceof \App\Entity\User) ? $user->getLIBUTE() : null;
            $local = ($user instanceof \App\Entity\User) ? $user->getLOCAL() : null;
            $unite = $libute . ' ' . $local;

            if ($libute) {
                $qb->andWhere('c.LIBUTE = :unite')->setParameter('unite', $unite);
                $newConsultations = $qb->getQuery()->getResult();
            } else {
                // Pas de LIBUTE déclaré pour l'utilisateur => aucune nouvelle consultation
                $newConsultations = [];
            }
        } else {
            $newConsultations = $qb->getQuery()->getResult();
        }

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
