<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ConsultationListRepository;
use App\Repository\DemandeDeConsultationRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;

class StatistiqueController extends AbstractController
{
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/statistique', name: 'app_statistique_consiltations')]
    public function index(
        ConsultationListRepository $consultationRepo,
        DemandeDeConsultationRepository $demandeRepo,
        UserRepository $userRepo
    ): Response {

        // Statistiques consultations
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $totalConsultations = $consultationRepo->count([]);

        $todayConsultations = (int) $consultationRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.Date >= :startOfDay')
            ->andWhere('c.Date < :endOfDay')
            ->setParameter('startOfDay', $today)
            ->setParameter('endOfDay', (clone $today)->modify('+1 day'))
            ->getQuery()
            ->getSingleScalarResult();

        // Courbe sur 30 jours
        $startDate = (clone $today)->modify('-29 days'); // 30 jours incluant aujourd'hui

        $consultationsLast30Days = $consultationRepo->createQueryBuilder('c')
            ->andWhere('c.Date >= :startDate')
            ->andWhere('c.Date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', (clone $today)->setTime(23, 59, 59))
            ->getQuery()
            ->getResult();

        // Initialiser toutes les dates de l'intervalle à 0
        $perDay = [];
        $labels = [];
        $data = [];

        $cursor = clone $startDate;
        while ($cursor <= $today) {
            $key = $cursor->format('Y-m-d');
            $perDay[$key] = 0;
            $cursor->modify('+1 day');
        }

        // Compter les consultations par jour
        foreach ($consultationsLast30Days as $consultation) {
            /** @var \App\Entity\ConsultationList $consultation */
            $key = $consultation->getDate()?->format('Y-m-d');
            if ($key && array_key_exists($key, $perDay)) {
                $perDay[$key]++;
            }
        }

        foreach ($perDay as $dateKey => $count) {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $dateKey);
            $labels[] = $dateObj ? $dateObj->format('d/m') : $dateKey;
            $data[] = $count;
        }

        // Statistiques demandes (en attente = toutes les demandes non encore transformées en consultation)
        $pendingDemandes = $demandeRepo->count([]);

        // Statistiques utilisateurs
        $totalUsers = $userRepo->count([]);
        $users = $userRepo->findAll();

        $rolesCount = [
            'ROLE_SUPER_ADMIN' => 0,
            'ROLE_ADMIN' => 0,
            'ROLE_USER' => 0,
        ];

        foreach ($users as $user) {
            /** @var \App\Entity\User $user */
            foreach ($user->getRoles() as $role) {
                if (isset($rolesCount[$role])) {
                    $rolesCount[$role]++;
                }
            }
        }

        return $this->render('main/statistiques/main_statistique.html.twig', [
            'totalConsultations' => $totalConsultations,
            'todayConsultations' => $todayConsultations,
            'consultationsChartLabels' => $labels,
            'consultationsChartData' => $data,
            'pendingDemandes' => $pendingDemandes,
            'totalUsers' => $totalUsers,
            'rolesCount' => $rolesCount,
        ]);
    }
}