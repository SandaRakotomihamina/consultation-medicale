<?php

namespace App\Controller;


use App\Form\DemandeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ConsultationList;
use App\Entity\DemandeDeConsultation;
use App\Entity\User;
use App\Form\ConsultationType;
use App\Form\UserType;
use App\Repository\ConsultationListRepository;
use App\Repository\DemandeDeConsultationRepository;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use \Symfony\Component\HttpFoundation\JsonResponse;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ConsultationListRepository $repository): Response
    {
        $consultations = $repository->findBy([], ['id' => 'DESC'], 4, 0);

        return $this->render('main/index.html.twig', [
            'consultations' => $consultations
        ]);
    }

    #############################################################################################################
    ###################################API pour plus de consultations listés#####################################
    #############################################################################################################
    #[Route('/api/consultations/load-more', name: 'api_consultations_load_more')]
    public function loadMoreConsultations(Request $request, ConsultationListRepository $repository): JsonResponse
    {
        $page = (int)$request->query->get('page', 1);
        $limit = 4;
        $offset = ($page - 1) * $limit;

        $consultations = $repository->findBy([], ['id' => 'DESC'], $limit, $offset);

        $html = '';
        foreach ($consultations as $consultation) {
            $html .= $this->renderView('main/consultations/_card.html.twig', [
                'consultation' => $consultation
            ]);
        }

        return $this->json([
            'html' => $html,
            'count' => count($consultations)
        ]);
    }


    #############################################################################################################
    ####################################Ajouter une nouvelle consultation########################################
    #############################################################################################################
    #[Route('/consultation/new', name: 'app_new_consultation')]
    #[IsGranted('ROLE_ADMIN')]
    public function newConsultation(Request $request, EntityManagerInterface $em): Response
    {
        $consultation = new ConsultationList();
        $consultation->setDate(new \DateTime());

        // Pré-remplissage depuis la demande
        $consultation->setGrade($request->query->get('grade'));
        $consultation->setNom($request->query->get('nom'));
        $consultation->setMatricule($request->query->get('matricule'));
        $consultation->setMotif($request->query->get('motif'));
        $consultation->setDelivreurDeMotif($request->query->get('delivreurMotif'));

        // Définir le délivreur d'observation automatiquement
        $user = $this->getUser();
        if ($user instanceof User) {
            $delivreur = trim($user->getTitle() . ' ' . $user->getName());
            $consultation->setDelivreurDObservation($delivreur);
        }

        $form = $this->createForm(ConsultationType::class, $consultation);
        if ($form->has('Date')) {
            $form->remove('Date');
        }

        $form->handleRequest($request);

        // Voir si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($consultation);
            $em->flush();

            // supprimer la demande associée si existe
            $demandeId = $request->query->get('id');
            if ($demandeId) {
                $demandeRepo = $em->getRepository(DemandeDeConsultation::class);
                $demande = $demandeRepo->find($demandeId);
                if ($demande) {
                    $em->remove($demande);
                    $em->flush();
                }
            }

            // SMS si repos
            /* if ($consultation->getRepos()) {
                $message = "Repos administré : " . $consultation->getRepos() . " au personnel " . $consultation->getNom();
                $client = \Symfony\Component\HttpClient\HttpClient::create();
                try {
                    $client->request('POST', 'https://api.smsmobile.mg/api/send-sms', [
                        'json' => [
                            'apiKey' => 'CLE_API',
                            'sender' => 'Service santé de la GENDARMERIE toby RATSIMANDRAVA',
                            'to' => 'numéro du déstinataire',
                            'message' => $message
                        ]
                    ]);
                    $this->addFlash('success', 'Message envoyé pour le repos administré à ' . $consultation->getNom());
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
                }
            } */

            $this->addFlash('success', 'Consultation enregistrée avec succès.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/consultations/new_consultation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #############################################################################################################
    #########################################Ajouter un nouvel utilisateur#######################################
    #############################################################################################################
    #[Route('/user/new', name: 'app_new_user')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function newUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $hashed = $passwordHasher->hashPassword($user, $plain);
                $user->setPassword($hashed);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/user/new_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #############################################################################################################
    ###########################################Recherche de consultation#########################################
    #############################################################################################################
    #[Route('/search', name: 'app_search')]
    public function search(Request $request, ConsultationListRepository $repo): Response
    {
        $query = $request->query->get('q', '');

        $results = [];
        if ($query) {
            $results = $repo->createQueryBuilder('c')
                ->where('c.Nom LIKE :q OR c.Matricule LIKE :q OR c.Grade LIKE :q')
                ->setParameter('q', "%$query%")
                ->orderBy('c.id', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        }

        return $this->render('main/search_results.html.twig', [
            'results' => $results,
            'query' => $query,
        ]);
    }

    #############################################################################################################
    ######################API pour charger plus de résultats de recherche########################################
    #############################################################################################################
    #[Route('/api/search/load-more', name: 'api_search_load_more')]
    public function loadMoreSearchResults(Request $request, ConsultationListRepository $repo): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $query = $request->query->get('q', '');
        $page = (int)$request->query->get('page', 1);
        $limit = 4;
        $offset = ($page - 1) * $limit;

        $results = [];
        if ($query) {
            $results = $repo->createQueryBuilder('c')
                ->where('c.Nom LIKE :q OR c.Matricule LIKE :q')
                ->setParameter('q', "%$query%")
                ->orderBy('c.id', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        }

        $html = '';
        foreach ($results as $consultation) {
            $html .= $this->renderView('main/consultations/_card.html.twig', ['consultation' => $consultation]);
        }

        return $this->json([
            'html' => $html,
            'count' => count($results)
        ]);
    }


    #############################################################################################################
    #########################################Modifier une consultation###########################################
    #############################################################################################################
    #[Route('/consultation/edit/{id}', name: 'app_consultation_edit')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function edit(ManagerRegistry $doctrine, Request $request, string $id): Response
    {
        $em = $doctrine->getManager();
        $consultation = $doctrine->getRepository(ConsultationList::class)->find($id);

        if (!$consultation) {
            throw $this->createNotFoundException('Consultation non trouvée pour l\'id '.$id);
        }

        if ($request->isMethod('POST')) {
            $consultation->setGrade($request->request->get('grade'));
            $consultation->setNom($request->request->get('nom'));
            $consultation->setMatricule($request->request->get('matricule'));
            $consultation->setMotif($request->request->get('motif'));
            $consultation->setDelivreurDeMotif($request->request->get('delivreur_de_motif'));
            $consultation->setObservation($request->request->get('observation'));
            $consultation->setDelivreurDObservation($request->request->get('delivreur_d_observation'));
            $consultation->setRepos($request->request->get('repos'));

            $em->persist($consultation);
            $em->flush();

            $this->addFlash('success', 'Consultation mise à jour avec succès !');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/consultations/edit_consultation.html.twig', [
            'consultation' => $consultation
        ]);
    }


    #############################################################################################################
    ##########################################Liste des utilisateurs#############################################
    #############################################################################################################
    #[Route('/users', name: 'app_list_user')]
    public function listUser(UserRepository $userRepository): Response
    {
        // Autoriser si SUPER_ADMIN ou ROLE_USER
        if (! $this->isGranted('ROLE_SUPER_ADMIN') && ! $this->isGranted('ROLE_USER')) {
            throw $this->createAccessDeniedException('Accès réservé aux super-admins et utilisateurs simples.');
        }

        $users = $userRepository->findAll();

        return $this->render('main/user/list_users.html.twig', [
            'users' => $users,
        ]);
    }


    #############################################################################################################
    #######################################Supprimer un utilisateur##############################################
    #############################################################################################################
    #[Route('/user/delete/{id}', name: 'app_user_delete')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function deleteUser(int $id, EntityManagerInterface $em, UserRepository $repo): Response
    {
        $user = $repo->find($id);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('app_list_user'); // Remplace par ta vraie route
    }


    #############################################################################################################
    ##################################Ajouter une nouvelle demande de consultation###############################
    #############################################################################################################
    #[Route('/demande/new', name: 'app_new_demande')]
    #[IsGranted('ROLE_USER')]
    public function newDemande(Request $request, EntityManagerInterface $em): Response
    {
        $demande = new DemandeDeConsultation();
        $demande->setDate(new \DateTime());

        $user = $this->getUser();
        if ($user instanceof User) {
            $delivreur = trim($user->getTitle() . ' ' . $user->getName());
            $demande->setDelivreurDeMotif($delivreur);
        }

        $form = $this->createForm(DemandeType::class, $demande);
        if ($form->has('Date')) {
            $form->remove('Date');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($demande);
            $em->flush();

            $this->addFlash('success', 'Demande de consultation enregistrée avec succès.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/demandes/new_demande.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #############################################################################################################
    #######################################Liste des demandes de consultation####################################
    #############################################################################################################

    #[Route('/demandes', name: 'app_list_demande')]
    #[IsGranted('ROLE_ADMIN')]
    public function listdemande(DemandeDeConsultationRepository $demandeDeConsultationRepository): Response
    {

        $demandes = $demandeDeConsultationRepository->findAll();

        return $this->render('main/demandes/list_demande.html.twig', [
            'Demandes' => $demandes,
        ]);
    }

}
