<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ConsultationList;
use App\Entity\User;
use App\Form\ConsultationType;
use App\Form\UserType;
use App\Repository\ConsultationListRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ConsultationListRepository $repository): Response
    {
        $consultations = $repository->findBy([], ['id' => 'DESC']);
        $espace = " ";

        return $this->render('main/index.html.twig', [
            'consultations' => $consultations,
            'espace' => $espace,
        ]);
    }
    #[Route('/consultation/new', name: 'app_new_consultation')]
    #[IsGranted('ROLE_ADMIN')]
    public function newConsultation(Request $request, EntityManagerInterface $em): Response
    {
        $consultation = new ConsultationList();
        $consultation->setDate(new \DateTime());

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

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($consultation);
            $em->flush();

            $this->addFlash('success', 'Consultation enregistrée avec succès.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/new_consultation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

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

        return $this->render('main/new_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, ConsultationListRepository $repo): Response
    {
        $query = $request->query->get('q', '');

        $results = [];
        if ($query) {
            $results = $repo->createQueryBuilder('c')
                ->where('c.Nom LIKE :q OR c.Matricule LIKE :q')
                ->setParameter('q', "%$query%")
                ->getQuery()
                ->getResult();
        }

        return $this->render('main/search_results.html.twig', [
            'results' => $results,
            'query' => $query,
        ]);
    }

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

        return $this->render('main/edit_consultation.html.twig', [
            'consultation' => $consultation
        ]);
    }
}
