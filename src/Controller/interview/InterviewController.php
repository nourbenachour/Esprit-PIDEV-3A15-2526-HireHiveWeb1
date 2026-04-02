<?php

namespace App\Controller\interview;

use App\Entity\Interview;
use App\Form\interview\InterviewType;
use App\Repository\interview\InterviewRepository;
use App\Service\interview\InterviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/interview', name: 'interview_')]
class InterviewController extends AbstractController
{
    public function __construct(
        private InterviewRepository $repository,
        private InterviewService $service,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Liste tous les entretiens avec filtres (recherche, statut, dates).
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchText = $request->query->get('search');
        $status = $request->query->get('status');
        $dateFrom = null;
        $dateTo = null;

        // Parsage des dates si présentes
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

        $interviews = $this->repository->findByFilters($searchText, $status, $dateFrom, $dateTo);

        return $this->render('interview/interview/index.html.twig', [
            'interviews' => $interviews,
            'statuses' => InterviewService::VALID_STATUSES,
        ]);
    }

    /**
     * Affiche les détails d'un entretien.
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        return $this->render('interview/interview/show.html.twig', [
            'interview' => $interview,
            'interviewView' => $this->buildSafeInterviewView($interview),
        ]);
    }

    private function buildSafeInterviewView(Interview $interview): array
    {
        return [
            'id' => $interview->getIdInterview(),
            'candidateName' => $this->safeString(fn () => $interview->getCandidateName()) ?? '-',
            'companyName' => $this->safeString(fn () => $interview->getCompanyName()) ?? '-',
            'interviewDate' => $this->safeDate(fn () => $interview->getInterviewDate()),
            'heureDebut' => $this->formatTimeDisplay($this->safeString(fn () => $interview->getHeureDebut())),
            'heureFin' => $this->formatTimeDisplay($this->safeString(fn () => $interview->getHeureFin())),
            'status' => $this->safeString(fn () => $interview->getStatus()) ?? 'PENDING',
            'result' => $this->safeString(fn () => $interview->getResult()) ?? 'SENT',
            'attendanceStatus' => $this->safeString(fn () => $interview->getAttendanceStatus()) ?? 'PLANNED',
            'requestDate' => $this->safeDate(fn () => $interview->getRequestDate()),
            'decisionDate' => $this->safeDate(fn () => $interview->getDecisionDate()),
        ];
    }

    private function formatTimeDisplay(?string $timeValue): string
    {
        if ($timeValue === null) {
            return '-';
        }

        $trimmed = trim($timeValue);
        if ($trimmed === '') {
            return '-';
        }

        if (preg_match('/^\d{2}:\d{2}/', $trimmed, $matches)) {
            return $matches[0];
        }

        return $trimmed;
    }

    private function safeString(callable $callback): ?string
    {
        try {
            $value = $callback();
            if ($value === null) {
                return null;
            }

            $stringValue = trim((string) $value);
            return $stringValue === '' ? null : $stringValue;
        } catch (\Error) {
            return null;
        }
    }

    private function safeDate(callable $callback): ?\DateTimeInterface
    {
        try {
            $value = $callback();
            return $value instanceof \DateTimeInterface ? $value : null;
        } catch (\Error) {
            return null;
        }
    }

    /**
     * Formulaire de création d'un nouvel entretien.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $interview = new Interview();
        $form = $this->createForm(InterviewType::class, $interview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Valider la cohérence horaires
                $heureDebut = $form->get('heureDebut')->getData();
                $heureFin = $form->get('heureFin')->getData();
                
                // Convertir en string HH:MM
                $heure_debut_str = $heureDebut->format('H:i');
                $heure_fin_str = $heureFin->format('H:i');
                
                $this->service->validateTimeCoherence($heure_debut_str, $heure_fin_str);
                $this->service->validateStatus($interview->getStatus());
                $this->service->validateResult($interview->getResult());

                // Keep meet link empty for now (API integration disabled).
                $interview->setMeet_link('');
                $interview->setRequestDate(new \DateTime());
                $this->em->persist($interview);
                $this->em->flush();

                $this->addFlash('success', 'Entretien créé avec succès');
                return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('interview/interview/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Formulaire d'édition d'un entretien.
     */
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        $form = $this->createForm(InterviewType::class, $interview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $heureDebut = $form->get('heureDebut')->getData();
                $heureFin = $form->get('heureFin')->getData();
                
                $heure_debut_str = $heureDebut->format('H:i');
                $heure_fin_str = $heureFin->format('H:i');
                
                $this->service->validateTimeCoherence($heure_debut_str, $heure_fin_str);
                $this->service->validateStatus($interview->getStatus());

                $this->em->flush();
                $this->addFlash('success', 'Entretien modifié avec succès');
                return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('interview/interview/edit.html.twig', [
            'form' => $form,
            'interview' => $interview,
        ]);
    }

    /**
     * Suppression sécurisée (avec CSRF token).
     */
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if ($this->isCsrfTokenValid('delete' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->em->remove($interview);
            $this->em->flush();
            $this->addFlash('success', 'Entretien supprimé avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('interview_index');
    }

    /**
     * Action: Candidat accepte l'entretien.
     */
    #[Route('/{id}/accept', name: 'accept', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function accept(int $id, Request $request): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if ($this->isCsrfTokenValid('accept' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->service->acceptInterview($interview);
            $this->addFlash('success', 'Entretien accepté');
        }

        return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
    }

    /**
     * Action: Candidat refuse l'entretien.
     */
    #[Route('/{id}/reject', name: 'reject', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function reject(int $id, Request $request): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if ($this->isCsrfTokenValid('reject' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->service->rejectInterview($interview);
            $this->addFlash('success', 'Entretien refusé');
        }

        return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
    }

    /**
     * Action: Marquer comme terminé.
     */
    #[Route('/{id}/complete', name: 'complete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function complete(int $id, Request $request): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if ($this->isCsrfTokenValid('complete' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->service->completeInterview($interview);
            $this->addFlash('success', 'Entretien marqué comme terminé');
        }

        return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
    }

    /**
     * Action: Marquer comme no-show.
     */
    #[Route('/{id}/no-show', name: 'no_show', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function noShow(int $id, Request $request): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if ($this->isCsrfTokenValid('noshow' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->service->markAsNoShow($interview);
            $this->addFlash('info', 'Entretien marqué comme no-show');
        }

        return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
    }

    /**
     * Action: Annuler l'entretien.
     */
    #[Route('/{id}/cancel', name: 'cancel', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function cancel(int $id, Request $request): Response
    {
        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if ($this->isCsrfTokenValid('cancel' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->service->cancelInterview($interview);
            $this->addFlash('warning', 'Entretien annulé');
        }

        return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
    }
}
