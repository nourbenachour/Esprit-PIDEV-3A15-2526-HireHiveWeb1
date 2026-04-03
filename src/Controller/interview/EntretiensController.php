<?php

namespace App\Controller\interview;

use App\Entity\Interview;
use App\Entity\Recruiter;
use App\Entity\Users;
use App\Repository\interview\ContractRepository;
use App\Repository\interview\InterviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\interview\InterviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EntretiensController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

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
        $interviews = $this->filterInterviewsByCurrentRole($interviews);

        return $this->render('interview/interview/index.html.twig', [
            'interviews' => $interviews,
            'statuses' => InterviewService::VALID_STATUSES,
            'canManageInterviews' => $this->isGranted('ROLE_RECRUITER'),
        ]);
    }

    #[Route('/backoffice/interviews', name: 'interview_backoffice_admin', methods: ['GET'], priority: 200)]
    public function adminBackoffice(Request $request, InterviewRepository $interviewRepository, ContractRepository $contractRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $interviewSearch = $request->query->get('i_search');
        $interviewStatus = $request->query->get('i_status');
        $interviewFrom = null;
        $interviewTo = null;

        $contractSearch = $request->query->get('c_search');
        $contractStatus = $request->query->get('c_status');
        $contractFrom = null;
        $contractTo = null;

        if ($request->query->get('i_from')) {
            try {
                $interviewFrom = new \DateTime((string) $request->query->get('i_from'));
            } catch (\Exception) {
            }
        }

        if ($request->query->get('i_to')) {
            try {
                $interviewTo = new \DateTime((string) $request->query->get('i_to'));
            } catch (\Exception) {
            }
        }

        if ($request->query->get('c_from')) {
            try {
                $contractFrom = new \DateTime((string) $request->query->get('c_from'));
            } catch (\Exception) {
            }
        }

        if ($request->query->get('c_to')) {
            try {
                $contractTo = new \DateTime((string) $request->query->get('c_to'));
            } catch (\Exception) {
            }
        }

        return $this->render('interview/entretiens/admin_index.html.twig', [
            'interviews' => $interviewRepository->findByFilters($interviewSearch, $interviewStatus, $interviewFrom, $interviewTo),
            'contracts' => $contractRepository->findByFilters($contractSearch, $contractStatus, $contractFrom, $contractTo),
            'interviewStatuses' => InterviewService::VALID_STATUSES,
            'contractStatuses' => \App\Service\interview\ContractService::VALID_STATUSES,
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

    private function filterInterviewsByCurrentRole(array $interviews): array
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $interviews;
        }

        if ($this->isGranted('ROLE_RECRUITER')) {
            $company = $this->resolveRecruiterCompanyName();
            if ($company === null) {
                return [];
            }

            return array_values(array_filter($interviews, fn (Interview $i): bool => $this->sameText((string) $i->getCompanyName(), $company)));
        }

        if ($this->isGranted('ROLE_CANDIDATE')) {
            $candidateName = $this->resolveCandidateFullName();
            if ($candidateName === null) {
                return [];
            }

            return array_values(array_filter($interviews, fn (Interview $i): bool => $this->sameText((string) $i->getCandidateName(), $candidateName)));
        }

        return [];
    }

    private function resolveRecruiterCompanyName(): ?string
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return null;
        }

        $recruiter = $this->em->getRepository(Recruiter::class)->findOneBy(['user_id' => $user]);
        if (!$recruiter instanceof Recruiter) {
            return null;
        }

        $companyName = trim((string) $recruiter->getCompany_name());
        return $companyName === '' ? null : $companyName;
    }

    private function resolveCandidateFullName(): ?string
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return null;
        }

        $fullName = trim(sprintf('%s %s', (string) $user->getFirst_name(), (string) $user->getLast_name()));
        return $fullName === '' ? null : $fullName;
    }

    private function sameText(string $a, string $b): bool
    {
        return mb_strtolower(trim($a)) === mb_strtolower(trim($b));
    }
}
