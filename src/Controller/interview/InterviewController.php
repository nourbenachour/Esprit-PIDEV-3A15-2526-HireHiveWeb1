<?php

namespace App\Controller\interview;

use App\Entity\Interview;
use App\Entity\Recruiter;
use App\Entity\Users;
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
        $interviews = $this->filterInterviewsByCurrentRole($interviews);

        return $this->render('interview/interview/index.html.twig', [
            'interviews' => $interviews,
            'statuses' => InterviewService::VALID_STATUSES,
            'canManageInterviews' => $this->isGranted('ROLE_RECRUITER'),
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

        if (!$this->canAccessInterview($interview)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas consulter cet entretien.');
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
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $interview = new Interview();
        $recruiterCompanyName = $this->resolveRecruiterCompanyName();
        if ($recruiterCompanyName !== null) {
            $interview->setCompanyName($recruiterCompanyName);
        }

        $form = $this->createForm(InterviewType::class, $interview, [
            'lock_company_name' => true,
            'company_name_hint' => $recruiterCompanyName,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Valider la cohérence horaires
                $heure_debut_str = (string) $form->get('heureDebut')->getData();
                $heure_fin_str = (string) $form->get('heureFin')->getData();
                
                $this->service->validateTimeCoherence($heure_debut_str, $heure_fin_str);

                // These values are managed by business workflow, not by scheduling form.
                $interview->setStatus('PENDING');
                $interview->setResult('SENT');
                $interview->setAttendanceStatus('PLANNED');

                // Keep meet link empty for now (API integration disabled).
                $interview->setMeet_link('');
                $interview->setRequestDate(new \DateTime());

                // This entity uses assigned identifiers, so set an ID before persist.
                $interview->setIdInterview($this->getNextInterviewId());

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

    private function getNextInterviewId(): int
    {
        $maxId = (int) $this->em->createQueryBuilder()
            ->select('COALESCE(MAX(i.idInterview), 0)')
            ->from(Interview::class, 'i')
            ->getQuery()
            ->getSingleScalarResult();

        return $maxId + 1;
    }

    /**
     * Formulaire d'édition d'un entretien.
     */
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if (!$this->isOwnedByCurrentRecruiter($interview)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet entretien.');
        }

        // Normalize legacy HH:mm:ss values to HH:mm so Symfony time transformer
        // doesn't fail with "Trailing data" on edit form binding.
        $this->normalizeTimesForForm($interview);

        $form = $this->createForm(InterviewType::class, $interview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $heure_debut_str = (string) $form->get('heureDebut')->getData();
                $heure_fin_str = (string) $form->get('heureFin')->getData();
                
                $this->service->validateTimeCoherence($heure_debut_str, $heure_fin_str);

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
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if (!$this->isOwnedByCurrentRecruiter($interview)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cet entretien.');
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
        $this->denyAccessUnlessGranted('ROLE_CANDIDATE');

        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if (!$this->isOwnedByCurrentCandidate($interview)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas répondre à cet entretien.');
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
        $this->denyAccessUnlessGranted('ROLE_CANDIDATE');

        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if (!$this->isOwnedByCurrentCandidate($interview)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas répondre à cet entretien.');
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
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if (!$this->isOwnedByCurrentRecruiter($interview)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet entretien.');
        }

        if ($this->isCsrfTokenValid('complete' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->service->completeInterview($interview);
            $this->addFlash('success', 'Entretien marqué comme terminé');
        }

        return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
    }

    /**
     * Action: Annuler l'entretien.
     */
    #[Route('/{id}/cancel', name: 'cancel', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function cancel(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $interview = $this->em->getRepository(Interview::class)->find($id);

        if (!$interview) {
            throw $this->createNotFoundException('Entretien non trouvé');
        }

        if (!$this->isOwnedByCurrentRecruiter($interview)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet entretien.');
        }

        if ($this->isCsrfTokenValid('cancel' . $interview->getIdInterview(), $request->request->get('_token'))) {
            $this->service->cancelInterview($interview);
            $this->addFlash('warning', 'Entretien annulé');
        }

        return $this->redirectToRoute('interview_show', ['id' => $interview->getIdInterview()]);
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

    private function canAccessInterview(Interview $interview): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->isGranted('ROLE_RECRUITER')) {
            return $this->isOwnedByCurrentRecruiter($interview);
        }

        if ($this->isGranted('ROLE_CANDIDATE')) {
            return $this->isOwnedByCurrentCandidate($interview);
        }

        return false;
    }

    private function isOwnedByCurrentRecruiter(Interview $interview): bool
    {
        $company = $this->resolveRecruiterCompanyName();
        if ($company === null) {
            return false;
        }

        return $this->sameText((string) $interview->getCompanyName(), $company);
    }

    private function isOwnedByCurrentCandidate(Interview $interview): bool
    {
        $candidateName = $this->resolveCandidateFullName();
        if ($candidateName === null) {
            return false;
        }

        return $this->sameText((string) $interview->getCandidateName(), $candidateName);
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

    private function normalizeTimesForForm(Interview $interview): void
    {
        $interview->setHeureDebut($this->normalizeToHourMinute((string) $interview->getHeureDebut()));
        $interview->setHeureFin($this->normalizeToHourMinute((string) $interview->getHeureFin()));
    }

    private function normalizeToHourMinute(string $raw): string
    {
        $value = trim($raw);
        if (preg_match('/^(\d{2}:\d{2})/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}
