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
use App\Entity\ExemptionOption;
use App\Entity\AdresseOption;
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
    #############################################################################################################
    #########################################Lister les consultations############################################
    #############################################################################################################
    #[Route('/', name: 'app_main')]
    public function index(ConsultationListRepository $repository): Response
    {
        // Si l'utilisateur est ROLE_USER, ne montrer que les consultations de la même LIBUTE
        if ($this->isGranted('ROLE_USER')) {
            $user = $this->getUser();
            $libute = ($user instanceof User) ? $user->getLIBUTE() : null;

            if ($libute) {
                $consultations = $repository->createQueryBuilder('c')
                    ->where('c.LIBUTE = :libute')
                    ->setParameter('libute', $libute)
                    ->orderBy('c.id', 'DESC')
                    ->setMaxResults(4)
                    ->getQuery()
                    ->getResult();
            } else {
                // Si pas de LIBUTE connue, ne rien afficher
                $consultations = [];
            }
        } else {
            $consultations = $repository->findBy([], ['id' => 'DESC'], 4, 0);
        }

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

        // Si ROLE_USER, filtrer par LIBUTE
        if ($this->isGranted('ROLE_USER')) {
            $user = $this->getUser();
            $libute = ($user instanceof User) ? $user->getLIBUTE() : null;

            if ($libute) {
                $consultations = $repository->createQueryBuilder('c')
                    ->where('c.LIBUTE = :libute')
                    ->setParameter('libute', $libute)
                    ->orderBy('c.id', 'DESC')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
            } else {
                $consultations = [];
            }
        } else {
            $consultations = $repository->findBy([], ['id' => 'DESC'], $limit, $offset);
        }

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
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/consultation/new/{demandeId}', name: 'app_new_consultation_from_demande')]
    
    public function newConsultation(Request $request, EntityManagerInterface $em, int $demandeId): Response
    {
        $consultation = new ConsultationList();
        $consultation->setDate(new \DateTime());

        // Récupérer la demande depuis la base de données
        $demandeRepo = $em->getRepository(DemandeDeConsultation::class);
        $demande = $demandeRepo->find($demandeId);
        
        if (!$demande) {
            throw $this->createNotFoundException('Demande de consultation non trouvée pour l\'id ' . $demandeId);
        }

        // Pré-remplissage depuis la demande récupérée de la base de données
        $consultation->setGrade($demande->getGrade());
        $consultation->setNom($demande->getNom());
        $consultation->setMatricule($demande->getMatricule());
        $consultation->setMotif($demande->getMotif());
        $consultation->setDelivreurDeMotif($demande->getDelivreurDeMotif());
        // Pré-remplir la LIBUTE depuis la demande si disponible
        if (method_exists($demande, 'getLIBUTE')) {
            $consultation->setLIBUTE($demande->getLIBUTE());
        }

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

            // supprimer la demande associée
            $em->remove($demande);
            $em->flush();

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

            $this->addFlash('success', 'Nouvelle consultation enregistrée avec succès.');
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

            // Gestion conditionnelle selon le rôle
            $roles = $user->getRoles();
            $isUserSimple = in_array('ROLE_USER', $roles) && !in_array('ROLE_ADMIN', $roles) && !in_array('ROLE_SUPER_ADMIN', $roles);
            
            if ($isUserSimple) {
                // Pour utilisateur simple : CODUTE, LIBUTE, LOCAL sont remplis, matricule, grade, nom sont null
                $user->setMatricule(null);
                $user->setTitle(null);
                $user->setName(null);
            } else {
                // Pour admin/super-admin : matricule, grade, nom sont remplis, CODUTE, LIBUTE, LOCAL sont null
                $user->setCODUTE(null);
                $user->setLIBUTE(null);
                $user->setLOCAL(null);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_list_user');
        }

        return $this->render('main/user/new_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #############################################################################################################
    ###########################################Recherche de consultation#########################################
    #############################################################################################################
    #[Route('/search', name: 'app_search')]
    public function search(
        Request $request, 
        ConsultationListRepository $consultationRepo,
        DemandeDeConsultationRepository $demandeRepo,
        UserRepository $userRepo
    ): Response
    {
        $query = $request->query->get('q', '');
        $type = $request->query->get('type', 'consultations'); // consultations, demandes, users

        $results = [];
        $template = 'main/consultations/search_consultations.html.twig';
        
        if ($query) {
            switch ($type) {
                case 'demandes':
                    $results = $demandeRepo->createQueryBuilder('d')
                        ->where('d.Nom LIKE :q OR d.Matricule LIKE :q OR d.Grade LIKE :q')
                        ->setParameter('q', "%$query%")
                        ->orderBy('d.id', 'DESC')
                        ->setFirstResult(0)
                        ->setMaxResults(4)
                        ->getQuery()
                        ->getResult();
                    $template = 'main/demandes/search_demandes.html.twig';
                    break;
                    
                case 'users':
                    $results = $userRepo->createQueryBuilder('u')
                        ->where('u.name LIKE :q OR u.Matricule LIKE :q OR u.title LIKE :q OR u.username LIKE :q')
                        ->setParameter('q', "%$query%")
                        ->orderBy('u.id', 'DESC')
                        ->setFirstResult(0)
                        ->setMaxResults(4)
                        ->getQuery()
                        ->getResult();
                    $template = 'main/user/search_users.html.twig';
                    break;
                    
                default: // consultations
                    $qb = $consultationRepo->createQueryBuilder('c')
                        ->where('c.Nom LIKE :q OR c.Matricule LIKE :q OR c.Grade LIKE :q')
                        ->setParameter('q', "%$query%")
                        ->orderBy('c.id', 'DESC')
                        ->setFirstResult(0)
                        ->setMaxResults(4);

                    if ($this->isGranted('ROLE_USER')) {
                        $user = $this->getUser();
                        $libute = ($user instanceof User) ? $user->getLIBUTE() : null;
                        if ($libute) {
                            $qb->andWhere('c.LIBUTE = :libute')->setParameter('libute', $libute);
                        } else {
                            $results = [];
                            break;
                        }
                    }

                    $results = $qb->getQuery()->getResult();
                    break;
            }
        }

        return $this->render($template, [
            'results' => $results,
            'query' => $query,
            'type' => $type,
        ]);
    }

    #############################################################################################################
    ######################API pour charger plus de résultats de recherche########################################
    #############################################################################################################
    #[Route('/api/search/load-more', name: 'api_search_load_more')]
    public function loadMoreSearchResults(
        Request $request, 
        ConsultationListRepository $consultationRepo,
        DemandeDeConsultationRepository $demandeRepo,
        UserRepository $userRepo
    ): JsonResponse
    {
        $query = $request->query->get('q', '');
        $type = $request->query->get('type', 'consultations');
        $page = (int)$request->query->get('page', 1);
        $limit = 4;
        $offset = ($page - 1) * $limit;

        $results = [];
        $cardTemplate = 'main/consultations/_card.html.twig';
        
        if ($query) {
            switch ($type) {
                case 'demandes':
                    $results = $demandeRepo->createQueryBuilder('d')
                        ->where('d.Nom LIKE :q OR d.Matricule LIKE :q OR d.Grade LIKE :q')
                        ->setParameter('q', "%$query%")
                        ->orderBy('d.id', 'DESC')
                        ->setFirstResult($offset)
                        ->setMaxResults($limit)
                        ->getQuery()
                        ->getResult();
                    $cardTemplate = 'main/demandes/_card.html.twig';
                    break;
                    
                case 'users':
                    $results = $userRepo->createQueryBuilder('u')
                        ->where('u.name LIKE :q OR u.Matricule LIKE :q OR u.title LIKE :q OR u.username LIKE :q')
                        ->setParameter('q', "%$query%")
                        ->orderBy('u.id', 'DESC')
                        ->setFirstResult($offset)
                        ->setMaxResults($limit)
                        ->getQuery()
                        ->getResult();
                    // Pour les utilisateurs, on doit créer un template de card ou réutiliser celui de la liste
                    $cardTemplate = null; // On va gérer ça différemment
                    break;
                    
                default: // consultations
                    $qb = $consultationRepo->createQueryBuilder('c')
                        ->where('c.Nom LIKE :q OR c.Matricule LIKE :q OR c.Grade LIKE :q')
                        ->setParameter('q', "%$query%")
                        ->orderBy('c.id', 'DESC')
                        ->setFirstResult($offset)
                        ->setMaxResults($limit);

                    if ($this->isGranted('ROLE_USER')) {
                        $user = $this->getUser();
                        $libute = ($user instanceof User) ? $user->getLIBUTE() : null;
                        if ($libute) {
                            $qb->andWhere('c.LIBUTE = :libute')->setParameter('libute', $libute);
                        } else {
                            $results = [];
                            break;
                        }
                    }

                    $results = $qb->getQuery()->getResult();
                    break;
            }
        }

        $html = '';
        foreach ($results as $item) {
            if ($type === 'users') {
                // Pour les utilisateurs, on utilise le même format que dans la liste
                $html .= $this->renderView('main/user/_card.html.twig', [
                    'user' => $item
                ]);
            } elseif ($type === 'demandes') {
                $html .= $this->renderView($cardTemplate, [
                    'demande' => $item
                ]);
            } else {
                $html .= $this->renderView($cardTemplate, [
                    'consultation' => $item
                ]);
            }
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
    public function editConsultation(ManagerRegistry $doctrine, Request $request, string $id): Response
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
            $consultation->setLIBUTE($request->request->get('libute'));
            
            // Handle Exemption - si disabled, sera null
            $exemptions = $request->request->all('exemption') ?? [];
            $consultation->setExemption(empty($exemptions) ? null : $exemptions);
            
            // Handle dates exemption - si disabled, sera null
            $debutExemption = $request->request->get('debut_exemption');
            if ($debutExemption) {
                $consultation->setDebutExemption(new \DateTime($debutExemption));
            } else {
                $consultation->setDebutExemption(null);
            }
            
            $finExemption = $request->request->get('fin_exemption');
            if ($finExemption) {
                $consultation->setFinExemption(new \DateTime($finExemption));
            } else {
                $consultation->setFinExemption(null);
            }
            
            // Handle Adresse - si disabled, sera null
            $adresses = $request->request->all('adresse') ?? [];
            $consultation->setAdrresse(empty($adresses) ? null : $adresses);
            
            // Handle PATC - si disabled, sera null
            $patc = $request->request->get('patc');
            $consultation->setPATC($patc ? (int)$patc : null);
            
            // Handle Repos - si disabled, sera vide/null
            $repos = $request->request->get('repos');
            $consultation->setRepos($repos ?: null);

            $em->persist($consultation);
            $em->flush();

            $this->addFlash('success', 'Consultation mise à jour avec succès !');
            return $this->redirectToRoute('app_main');
        }

        // Get exemption and adresse options
        $exemptionOptions = $doctrine->getRepository(ExemptionOption::class)->findAll();
        $adresseOptions = $doctrine->getRepository(AdresseOption::class)->findAll();

        return $this->render('main/consultations/edit_consultation.html.twig', [
            'consultation' => $consultation,
            'exemptionOptions' => $exemptionOptions,
            'adresseOptions' => $adresseOptions
        ]);
    }

    #############################################################################################################
    #########################################Modifier un utilisateur#############################################
    #############################################################################################################
    #[Route('/users/edit/{id}', name: 'app_user_edit')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function editUser(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, string $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvée pour l\'id '.$id);
        }

        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ne mettre à jour le mot de passe que s'il est fourni
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $hashed = $passwordHasher->hashPassword($user, $plain);
                $user->setPassword($hashed);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur mise à jour avec succès !');
            return $this->redirectToRoute('app_list_user');
        }

        return $this->render('main/user/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    #############################################################################################################
    ##########################################Liste des utilisateurs#############################################
    #############################################################################################################
    #[isGranted('ROLE_SUPER_ADMIN')]
    #[Route('/users', name: 'app_list_user')]
    public function listUser(UserRepository $userRepository): Response
    {
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

        return $this->redirectToRoute('app_list_user');
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
            $delivreur = trim($user->getLIBUTE() . ' ' . $user->getLOCAL());
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

            $this->addFlash('success', 'Nouvelle demande de consultation enregistrée avec succès.');
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

        $demandes = $demandeDeConsultationRepository->findBy([], ['id' => 'ASC']);

        return $this->render('main/demandes/list_demande.html.twig', [
            'Demandes' => $demandes,
        ]);
    }

}
