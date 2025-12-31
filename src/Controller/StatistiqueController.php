<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ConsultationListRepository;
use App\Repository\DemandeDeConsultationRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;

class StatistiqueController extends AbstractController
{
    #[Route('/statistique', name: 'app_statistique_consiltations')]
    public function index(
        ConsultationListRepository $consultationRepo,
        DemandeDeConsultationRepository $demandeRepo,
        UserRepository $userRepo
    ): Response {
        
        if (! $this->isGranted('ROLE_SUPER_ADMIN') && ! $this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès réservé aux super-admins, administrateurs et utilisateurs simples.');
        }
        
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

        // ========== STATISTIQUES SUR LES REPOS ==========
        $repos24h = (int) $consultationRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.Repos = :repos')
            ->setParameter('repos', '24h')
            ->getQuery()
            ->getSingleScalarResult();

        $repos48h = (int) $consultationRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.Repos = :repos')
            ->setParameter('repos', '48h')
            ->getQuery()
            ->getSingleScalarResult();

        $repos72h = (int) $consultationRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.Repos = :repos')
            ->setParameter('repos', '72h')
            ->getQuery()
            ->getSingleScalarResult();

        $totalAvecRepos = $repos24h + $repos48h + $repos72h;
        $totalSansRepos = $totalConsultations - $totalAvecRepos;
        $pourcentageAvecRepos = $totalConsultations > 0 ? round(($totalAvecRepos / $totalConsultations) * 100, 1) : 0;
        $pourcentageSansRepos = $totalConsultations > 0 ? round(($totalSansRepos / $totalConsultations) * 100, 1) : 0;

        // ========== STATISTIQUES PAR GRADE ==========
        $consultationsByGrade = $consultationRepo->createQueryBuilder('c')
            ->select('c.Grade, COUNT(c.id) as count')
            ->groupBy('c.Grade')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();

        $gradesLabels = [];
        $gradesData = [];
        foreach ($consultationsByGrade as $item) {
            $gradesLabels[] = $item['Grade'] ?? 'Non défini';
            $gradesData[] = (int) $item['count'];
        }

        // ========== STATISTIQUES PAR MÉDECIN/DÉLIVREUR ==========
        $consultationsByMedecin = $consultationRepo->createQueryBuilder('c')
            ->select('c.DelivreurDObservation, COUNT(c.id) as count')
            ->where('c.DelivreurDObservation IS NOT NULL')
            ->andWhere("c.DelivreurDObservation != ''")
            ->groupBy('c.DelivreurDObservation')
            ->orderBy('count', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $medecinsLabels = [];
        $medecinsData = [];
        foreach ($consultationsByMedecin as $item) {
            $medecinsLabels[] = $item['DelivreurDObservation'] ?? 'Non défini';
            $medecinsData[] = (int) $item['count'];
        }

        // ========== STATISTIQUES MENSUELLES (12 derniers mois) ==========
        $startMonth = (clone $today)->modify('-11 months')->modify('first day of this month')->setTime(0, 0, 0);
        
        // Récupérer toutes les consultations des 12 derniers mois
        $consultationsLast12Months = $consultationRepo->createQueryBuilder('c')
            ->where('c.Date >= :startMonth')
            ->setParameter('startMonth', $startMonth)
            ->getQuery()
            ->getResult();

        // Compter par mois en PHP
        $monthCounts = [];
        foreach ($consultationsLast12Months as $consultation) {
            /** @var \App\Entity\ConsultationList $consultation */
            $date = $consultation->getDate();
            if ($date) {
                $key = $date->format('Y-m');
                if (!isset($monthCounts[$key])) {
                    $monthCounts[$key] = 0;
                }
                $monthCounts[$key]++;
            }
        }

        $monthLabels = [];
        $monthData = [];
        $currentMonth = clone $startMonth;
        while ($currentMonth <= $today) {
            $key = $currentMonth->format('Y-m');
            $monthLabels[] = $currentMonth->format('M Y');
            $monthData[] = $monthCounts[$key] ?? 0;
            $currentMonth->modify('+1 month');
        }

        // Mois actuel vs mois précédent
        $currentMonthStart = (clone $today)->modify('first day of this month')->setTime(0, 0, 0);
        $currentMonthEnd = (clone $today)->modify('last day of this month')->setTime(23, 59, 59);
        $previousMonthStart = (clone $currentMonthStart)->modify('-1 month');
        $previousMonthEnd = (clone $previousMonthStart)->modify('last day of this month')->setTime(23, 59, 59);

        $currentMonthCount = (int) $consultationRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.Date >= :start')
            ->andWhere('c.Date <= :end')
            ->setParameter('start', $currentMonthStart)
            ->setParameter('end', $currentMonthEnd)
            ->getQuery()
            ->getSingleScalarResult();

        $previousMonthCount = (int) $consultationRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.Date >= :start')
            ->andWhere('c.Date <= :end')
            ->setParameter('start', $previousMonthStart)
            ->setParameter('end', $previousMonthEnd)
            ->getQuery()
            ->getSingleScalarResult();

        $monthEvolution = $previousMonthCount > 0 
            ? round((($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100, 1)
            : ($currentMonthCount > 0 ? 100 : 0);

        // ========== STATISTIQUES HEBDOMADAIRES (par jour de semaine) ==========
        // Si l'utilisateur est ROLE_USER, on filtre par sa LIBUTE pour l'ensemble des statistiques
        $filteredConsultations = [];
        if ($this->isGranted('ROLE_USER')) {
            $user = $this->getUser();
            $libute = ($user instanceof \App\Entity\User) ? $user->getLIBUTE() : null;
            if ($libute) {
                $filteredConsultations = $consultationRepo->createQueryBuilder('c')
                    ->where('c.LIBUTE = :libute')
                    ->setParameter('libute', $libute)
                    ->getQuery()
                    ->getResult();
            } else {
                $filteredConsultations = [];
            }
        } else {
            $filteredConsultations = $consultationRepo->findAll();
        }

        $weekdayCounts = [0, 0, 0, 0, 0, 0, 0]; // Dimanche à Samedi (0-6)
        
        foreach ($filteredConsultations as $consultation) {
            /** @var \App\Entity\ConsultationList $consultation */
            $date = $consultation->getDate();
            if ($date) {
                $weekday = (int) $date->format('w'); // 0 = Dimanche, 6 = Samedi
                $weekdayCounts[$weekday]++;
            }
        }

        $weekdayNames = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $weekdayLabels = $weekdayNames;
        $weekdayData = $weekdayCounts;

        // ========== STATISTIQUES POUR ADRESSE, PATC ET EXEMPTIONS ACTIVES ==========
        $adresseCounts = [];
        $patcCounts = [];
        $exemptionCounts = [];

        $todayStart = (new \DateTime())->setTime(0,0,0);
        $todayEnd = (clone $todayStart)->setTime(23,59,59);

        foreach ($filteredConsultations as $consultation) {
            // Adresse (stockée en JSON array)
            $adrs = $consultation->getAdrresse();
            if (is_array($adrs)) {
                foreach ($adrs as $adr) {
                    $a = trim((string) $adr);
                    if ($a === '') continue;
                    $adresseCounts[$a] = ($adresseCounts[$a] ?? 0) + 1;
                }
            }

            // PATC
            $patc = $consultation->getPATC();
            if ($patc !== null && $patc !== '') {
                $key = (string) $patc;
                $patcCounts[$key] = ($patcCounts[$key] ?? 0) + 1;
            }

            // Exemptions actives
            $exemptions = $consultation->getExemption();
            $debut = $consultation->getDebutExemption();
            $fin = $consultation->getFinExemption();
            if (is_array($exemptions) && $debut instanceof \DateTime && $fin instanceof \DateTime) {
                // Vérifier que l'exemption est active aujourd'hui (inclus)
                if ($debut <= $todayEnd && $fin >= $todayStart) {
                    foreach ($exemptions as $ex) {
                        $e = trim((string) $ex);
                        if ($e === '') continue;
                        $exemptionCounts[$e] = ($exemptionCounts[$e] ?? 0) + 1;
                    }
                }
            }
        }

        // Trier par occurrences décroissantes
        arsort($adresseCounts);
        arsort($patcCounts);
        arsort($exemptionCounts);

        $adresseLabels = array_keys($adresseCounts);
        $adresseData = array_values($adresseCounts);

        $patcLabels = array_keys($patcCounts);
        $patcData = array_values($patcCounts);

        $exemptionLabels = array_keys($exemptionCounts);
        $exemptionData = array_values($exemptionCounts);

        // ========== STATISTIQUES DEMANDES ==========
        $pendingDemandes = $demandeRepo->count([]);

        // Demandes sur 30 derniers jours
        $demandesLast30Days = $demandeRepo->createQueryBuilder('d')
            ->andWhere('d.Date >= :startDate')
            ->andWhere('d.Date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', (clone $today)->setTime(23, 59, 59))
            ->getQuery()
            ->getResult();

        $demandesPerDay = [];
        $demandesLabels = [];
        $demandesData = [];

        $cursor = clone $startDate;
        while ($cursor <= $today) {
            $key = $cursor->format('Y-m-d');
            $demandesPerDay[$key] = 0;
            $cursor->modify('+1 day');
        }

        foreach ($demandesLast30Days as $demande) {
            /** @var \App\Entity\DemandeDeConsultation $demande */
            $key = $demande->getDate()?->format('Y-m-d');
            if ($key && array_key_exists($key, $demandesPerDay)) {
                $demandesPerDay[$key]++;
            }
        }

        foreach ($demandesPerDay as $dateKey => $count) {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $dateKey);
            $demandesLabels[] = $dateObj ? $dateObj->format('d/m') : $dateKey;
            $demandesData[] = $count;
        }

        // Taux de conversion demandes → consultations
        $totalDemandesHistorique = $demandeRepo->count([]);
        // On estime que les consultations proviennent de demandes (approximation)
        // Pour un calcul plus précis, il faudrait une relation entre demande et consultation
        $conversionRate = $totalConsultations > 0 && ($totalDemandesHistorique + $totalConsultations) > 0
            ? round(($totalConsultations / ($totalDemandesHistorique + $totalConsultations)) * 100, 1)
            : 0;

        // Temps moyen entre demande et consultation (approximation basée sur les dates)
        $allDemandes = $demandeRepo->findAll();
        $allConsultations = $consultationRepo->findAll();
        $delais = [];
        foreach ($allDemandes as $demande) {
            $demandeDate = $demande->getDate();
            if (!$demandeDate) continue;
            
            // Chercher une consultation correspondante (même matricule et date proche)
            foreach ($allConsultations as $consultation) {
                if ($consultation->getMatricule() === $demande->getMatricule() 
                    && $consultation->getDate() 
                    && $consultation->getDate() >= $demandeDate) {
                    $diff = $demandeDate->diff($consultation->getDate());
                    $delais[] = $diff->days;
                    break;
                }
            }
        }
        $moyenneDelai = count($delais) > 0 ? round(array_sum($delais) / count($delais), 1) : 0;

        // ========== STATISTIQUES UTILISATEURS ==========
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
            // Consultations de base
            'totalConsultations' => $totalConsultations,
            'todayConsultations' => $todayConsultations,
            'consultationsChartLabels' => $labels,
            'consultationsChartData' => $data,
            
            // Repos
            'repos24h' => $repos24h,
            'repos48h' => $repos48h,
            'repos72h' => $repos72h,
            'totalAvecRepos' => $totalAvecRepos,
            'totalSansRepos' => $totalSansRepos,
            'pourcentageAvecRepos' => $pourcentageAvecRepos,
            'pourcentageSansRepos' => $pourcentageSansRepos,
            
            // Grades
            'gradesLabels' => $gradesLabels,
            'gradesData' => $gradesData,
            
            // Médecins
            'medecinsLabels' => $medecinsLabels,
            'medecinsData' => $medecinsData,
            
            // Mensuelles
            'monthLabels' => $monthLabels,
            'monthData' => $monthData,
            'currentMonthCount' => $currentMonthCount,
            'previousMonthCount' => $previousMonthCount,
            'monthEvolution' => $monthEvolution,
            
            // Hebdomadaires
            'weekdayLabels' => $weekdayLabels,
            'weekdayData' => $weekdayData,
            
            // Demandes
            'pendingDemandes' => $pendingDemandes,
            'demandesChartLabels' => $demandesLabels,
            'demandesChartData' => $demandesData,
            'conversionRate' => $conversionRate,
            'moyenneDelai' => $moyenneDelai,
            
            // Utilisateurs
            'totalUsers' => $totalUsers,
            'rolesCount' => $rolesCount,

            // Adresse / PATC / Exemptions actives
            'adresseLabels' => $adresseLabels ?? [],
            'adresseData' => $adresseData ?? [],
            'patcLabels' => $patcLabels ?? [],
            'patcData' => $patcData ?? [],
            'exemptionLabels' => $exemptionLabels ?? [],
            'exemptionData' => $exemptionData ?? [],
        ]);
    }
}