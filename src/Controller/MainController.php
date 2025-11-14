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

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ConsultationListRepository $repository): Response
    {
        $consultations = $repository->findBy([], ['Date' => 'DESC']);

        return $this->render('main/index.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[Route('/consultation/new', name: 'app_new_consultation')]
    public function newConsultation(Request $request, EntityManagerInterface $em): Response
    {
        $consultation = new ConsultationList();
        // Mettre la date de consultation automatiquement à maintenant (heure du serveur)
        $consultation->setDate(new \DateTime());

        // Pre-remplir par le nom de l'utilisateur connecté dans le champ "Délivreur d'observation"
        $user = $this->getUser();
        if ($user instanceof User) {
            $delivreur = trim($user->getTitle() . ' ' . $user->getName());
            $consultation->setDelivreurDObservation($delivreur);
        }

        $form = $this->createForm(ConsultationType::class, $consultation);
        // enlever la date du formulaire car elle est mis automatiquement
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

            // Default role
            $user->setRoles(['ROLE_USER']);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé.');

            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/new_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
