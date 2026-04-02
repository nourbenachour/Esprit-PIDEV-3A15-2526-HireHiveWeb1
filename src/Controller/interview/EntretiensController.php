<?php

namespace App\Controller\interview;

use App\Repository\interview\ContractRepository;
use App\Repository\interview\InterviewRepository;
use App\Service\interview\InterviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EntretiensController extends AbstractController
{
    private const MONTHS_FR = [
        1 => 'Janvier',
        2 => 'Fevrier',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Aout',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Decembre',
    ];

    #[Route('/frontoffice/entretien', name: 'app_frontoffice_interviews', methods: ['GET'])]
    public function index(Request $request, InterviewRepository $repository): Response
    {
        $searchText = $request->query->get('search');
        $status = $request->query->get('status');
        $dateFrom = null;
        $dateTo = null;

        if ($request->query->get('dateFrom')) {
            try {
                $dateFrom = new \DateTime($request->query->get('dateFrom'));
            } catch (\Exception) {
            }
        }

        if ($request->query->get('dateTo')) {
            try {
                $dateTo = new \DateTime($request->query->get('dateTo'));
            } catch (\Exception) {
            }
        }

        $interviews = $repository->findByFilters($searchText, $status, $dateFrom, $dateTo);

        return $this->render('interview/interview/index.html.twig', [
            'interviews' => $interviews,
            'statuses' => InterviewService::VALID_STATUSES,
        ]);
    }

    #[Route('/entretiens', name: 'entretiens_index_legacy', methods: ['GET'])]
    public function indexLegacyRedirect(): Response
    {
        return $this->redirectToRoute('app_frontoffice_interviews');
    }

    #[Route('/frontoffice/entretien/calendrier', name: 'entretiens_calendrier', methods: ['GET'])]
    #[Route('/entretiens/calendrier', name: 'entretiens_calendrier_legacy', methods: ['GET'])]
    public function calendrier(Request $request, InterviewRepository $repository): Response
    {
        $month = $request->query->get('month', (new \DateTime())->format('Y-m'));

        try {
            $start = new \DateTime($month . '-01 00:00:00');
        } catch (\Exception) {
            $start = new \DateTime('first day of this month 00:00:00');
        }

        $end = (clone $start)->modify('last day of this month')->setTime(23, 59, 59);

        $plannedInterviews = $repository->findPlannedByDateRange($start, $end);

        $days = $this->buildCalendarDays($start, $plannedInterviews);
        $weeks = array_chunk($days, 7);
        $prevMonth = (clone $start)->modify('-1 month')->format('Y-m');
        $nextMonth = (clone $start)->modify('+1 month')->format('Y-m');
        $monthNumber = (int) $start->format('n');
        $yearNumber = (int) $start->format('Y');

        $monthOptions = [];
        foreach (self::MONTHS_FR as $num => $label) {
            $monthOptions[] = [
                'value' => $num,
                'label' => $label,
                'selected' => $num === $monthNumber,
            ];
        }

        $yearOptions = [];
        for ($y = $yearNumber - 3; $y <= $yearNumber + 3; $y++) {
            $yearOptions[] = [
                'value' => $y,
                'selected' => $y === $yearNumber,
            ];
        }

        return $this->render('interview/entretiens/calendrier.html.twig', [
            'plannedInterviews' => $plannedInterviews,
            'month' => $start,
            'calendarWeeks' => $weeks,
            'calendarMonthLabel' => self::MONTHS_FR[$monthNumber] . ' ' . $yearNumber,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
        ]);
    }

    private function buildCalendarDays(\DateTimeInterface $monthStart, array $plannedInterviews): array
    {
        $monthStartAtZero = (clone $monthStart)->setTime(0, 0, 0);
        $monthEnd = (clone $monthStartAtZero)->modify('last day of this month');
        $calendarStart = (clone $monthStartAtZero)->modify('monday this week');
        $calendarEnd = (clone $monthEnd)->modify('sunday this week');
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');

        $interviewsByDate = [];
        foreach ($plannedInterviews as $interview) {
            try {
                $dateKey = $interview->getInterviewDate()->format('Y-m-d');
                $interviewsByDate[$dateKey][] = [
                    'id' => $interview->getIdInterview(),
                    'candidateName' => (string) $interview->getCandidateName(),
                    'companyName' => (string) $interview->getCompanyName(),
                    'timeRange' => $this->formatTimeDisplay((string) $interview->getHeureDebut()) . ' - ' . $this->formatTimeDisplay((string) $interview->getHeureFin()),
                    'status' => (string) $interview->getStatus(),
                ];
            } catch (\Throwable) {
                // Skip malformed rows; calendar stays readable.
            }
        }

        $days = [];
        $cursor = clone $calendarStart;
        while ($cursor <= $calendarEnd) {
            $dateKey = $cursor->format('Y-m-d');
            $days[] = [
                'date' => clone $cursor,
                'dayNumber' => (int) $cursor->format('j'),
                'inCurrentMonth' => $cursor->format('m') === $monthStartAtZero->format('m'),
                'isToday' => $dateKey === $today,
                'interviews' => $interviewsByDate[$dateKey] ?? [],
            ];
            $cursor = (clone $cursor)->modify('+1 day');
        }

        return $days;
    }

    private function formatTimeDisplay(string $timeValue): string
    {
        $trimmed = trim($timeValue);
        if ($trimmed === '') {
            return '-';
        }

        if (preg_match('/^\d{2}:\d{2}/', $trimmed, $matches)) {
            return $matches[0];
        }

        return $trimmed;
    }

    #[Route('/frontoffice/entretien/contrats', name: 'entretiens_contracts_redirect', methods: ['GET'])]
    public function contractsRedirect(): Response
    {
        return $this->redirectToRoute('contract_index');
    }

    #[Route('/frontoffice/entretien/questionnaire', name: 'entretiens_questionnaire_redirect', methods: ['GET'])]
    public function questionnaireRedirect(): Response
    {
        return $this->redirectToRoute('questionnaire_index');
    }

    #[Route('/frontoffice/entretien/dashboard', name: 'entretiens_dashboard', methods: ['GET'])]
    #[Route('/entretiens/dashboard', name: 'entretiens_dashboard_legacy', methods: ['GET'])]
    public function dashboard(InterviewRepository $interviewRepository, ContractRepository $contractRepository): Response
    {
        return $this->render('interview/entretiens/dashboard.html.twig', [
            'totalInterviews' => $interviewRepository->count([]),
            'pendingInterviews' => $interviewRepository->countByStatus('PENDING'),
            'signedContracts' => $contractRepository->countByStatus('SIGNED'),
        ]);
    }
}
