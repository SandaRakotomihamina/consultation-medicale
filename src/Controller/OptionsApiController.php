<?php

namespace App\Controller;

use App\Entity\ExemptionOption;
use App\Entity\AdresseOption;
use App\Repository\ExemptionOptionRepository;
use App\Repository\AdresseOptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OptionsApiController extends AbstractController
{
    #############################################################################################################
    #####################################Afficher les options d'exemptions#######################################
    #############################################################################################################
    #[Route('/api/exemption-options', name: 'api_exemption_options', methods: ['GET'])]
    public function listExemptions(ExemptionOptionRepository $repo): JsonResponse
    {
        $items = array_map(fn($i) => $i->getValue(), $repo->findBy([], ['value' => 'ASC']));
        return new JsonResponse(['items' => $items]);
    }

    #############################################################################################################
    ######################################Ajouter une option d'exemption#########################################
    #############################################################################################################
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/exemption-options', name: 'api_exemption_options_add', methods: ['POST'])]
    public function addExemption(Request $request, ExemptionOptionRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $value = trim($data['value'] ?? '');
        if (!$value) {
            return new JsonResponse(['error' => 'Valeur manquante'], 400);
        }
        if ($repo->findByValue($value)) {
            return new JsonResponse(['value' => $value], 200);
        }
        $opt = new ExemptionOption($value);
        $em->persist($opt);
        $em->flush();
        return new JsonResponse(['value' => $opt->getValue()], 201);
    }

    #############################################################################################################
    ######################################Lister les options d'adressage#########################################
    #############################################################################################################
    #[Route('/api/adresse-options', name: 'api_adresse_options', methods: ['GET'])]
    public function listAdresses(AdresseOptionRepository $repo): JsonResponse
    {
        $items = array_map(fn($i) => $i->getValue(), $repo->findBy([], ['value' => 'ASC']));
        return new JsonResponse(['items' => $items]);
    }

    #############################################################################################################
    #######################################Ajouter une option adressage##########################################
    #############################################################################################################
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/adresse-options', name: 'api_adresse_options_add', methods: ['POST'])]
    public function addAdresse(Request $request, AdresseOptionRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $value = trim($data['value'] ?? '');
        if (!$value) {
            return new JsonResponse(['error' => 'Valeur manquante'], 400);
        }
        if ($repo->findByValue($value)) {
            return new JsonResponse(['value' => $value], 200);
        }
        $opt = new AdresseOption($value);
        $em->persist($opt);
        $em->flush();
        return new JsonResponse(['value' => $opt->getValue()], 201);
    }
}
