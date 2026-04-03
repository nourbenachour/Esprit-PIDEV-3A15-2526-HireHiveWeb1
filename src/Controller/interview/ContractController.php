<?php

namespace App\Controller\interview;

use App\Entity\Contrat;
use App\Entity\Recruiter;
use App\Entity\Users;
use App\Form\interview\ContractType;
use App\Repository\interview\ContractRepository;
use App\Service\interview\ContractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contract', name: 'contract_')]
class ContractController extends AbstractController
{
    public function __construct(
        private ContractRepository $repository,
        private ContractService $service,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Liste tous les contrats avec filtres.
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
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

        $contracts = $this->repository->findByFilters($searchText, $status, $dateFrom, $dateTo);
        $contracts = $this->filterContractsByCurrentRole($contracts);

        return $this->render('interview/contract/index.html.twig', [
            'contracts' => $contracts,
            'statuses' => ContractService::VALID_STATUSES,
            'canManageContracts' => $this->isGranted('ROLE_RECRUITER'),
        ]);
    }

    /**
     * Affiche les détails d'un contrat.
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $contract = $this->em->getRepository(Contrat::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé');
        }

        if (!$this->canAccessContract($contract)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas consulter ce contrat.');
        }

        return $this->render('interview/contract/show.html.twig', [
            'contract' => $contract,
            'contractView' => $this->buildSafeContractView($contract),
        ]);
    }

    /**
     * Prevent Twig runtime errors when typed properties are not initialized.
     */
    private function buildSafeContractView(Contrat $contract): array
    {
        return [
            'id' => $contract->getIdContrat(),
            'candidateName' => $this->safeString(fn () => $contract->getCandidateName()) ?? '-',
            'companyName' => $this->safeString(fn () => $contract->getCompanyName()) ?? '-',
            'contractType' => $this->safeString(fn () => $contract->getContractType()) ?? '-',
            'salary' => $this->safeFloat(fn () => $contract->getSalary()),
            'startDate' => $this->safeDate(fn () => $contract->getStartDate()),
            'endDate' => $this->safeDate(fn () => $contract->getEndDate()),
            'status' => $this->safeString(fn () => $contract->getStatus()) ?? 'PENDING',
            'signedAt' => $this->safeDate(fn () => $contract->getSignedAt()),
            'signature' => $this->safeString(fn () => $contract->getSignature()),
        ];
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

    private function safeFloat(callable $callback): float
    {
        try {
            return (float) $callback();
        } catch (\Error) {
            return 0.0;
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
     * Formulaire de création d'un nouveau contrat.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $contract = new Contrat();
        $recruiterCompanyName = $this->resolveRecruiterCompanyName();
        if ($recruiterCompanyName !== null) {
            $contract->setCompanyName($recruiterCompanyName);
        }

        $form = $this->createForm(ContractType::class, $contract, [
            'lock_company_name' => true,
            'company_name_hint' => $recruiterCompanyName,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Status is workflow-managed and no longer editable in the form.
                $contract->setStatus('PENDING');
                $contract->setSignature('');
                $contract->setSignedAt(new \DateTime());

                $this->service->validateDateCoherence($contract->getStartDate(), $contract->getEndDate());
                $this->service->validateStatus($contract->getStatus());
                $this->service->validateContractType($contract->getContractType());

                // This entity uses assigned identifiers, so set an ID before persist.
                $contract->setIdContrat($this->getNextContractId());

                $this->em->persist($contract);
                $this->em->flush();

                $this->addFlash('success', 'Contrat créé avec succès');
                return $this->redirectToRoute('contract_show', ['id' => $contract->getIdContrat()]);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('interview/contract/new.html.twig', [
            'form' => $form,
            'recruiterCompanyName' => $recruiterCompanyName,
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

    private function getNextContractId(): int
    {
        $maxId = (int) $this->em->createQueryBuilder()
            ->select('COALESCE(MAX(c.idContrat), 0)')
            ->from(Contrat::class, 'c')
            ->getQuery()
            ->getSingleScalarResult();

        return $maxId + 1;
    }

    /**
     * Formulaire d'édition d'un contrat.
     */
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $contract = $this->em->getRepository(Contrat::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé');
        }

        if (!$this->isOwnedByCurrentRecruiter($contract)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce contrat.');
        }

        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->service->validateDateCoherence($contract->getStartDate(), $contract->getEndDate());
                $this->service->validateStatus($contract->getStatus());

                $this->em->flush();
                $this->addFlash('success', 'Contrat modifié avec succès');
                return $this->redirectToRoute('contract_show', ['id' => $contract->getIdContrat()]);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('interview/contract/edit.html.twig', [
            'form' => $form,
            'contract' => $contract,
        ]);
    }

    /**
     * Suppression sécurisée (avec CSRF token).
     */
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $contract = $this->em->getRepository(Contrat::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé');
        }

        if (!$this->isOwnedByCurrentRecruiter($contract)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce contrat.');
        }

        if ($this->isCsrfTokenValid('delete' . $contract->getIdContrat(), $request->request->get('_token'))) {
            $this->em->remove($contract);
            $this->em->flush();
            $this->addFlash('success', 'Contrat supprimé avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('contract_index');
    }

    /**
     * Envoyer le contrat au candidat (PENDING -> SENT).
     */
    #[Route('/{id}/send', name: 'send', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function send(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $contract = $this->em->getRepository(Contrat::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé');
        }

        if (!$this->isOwnedByCurrentRecruiter($contract)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas envoyer ce contrat.');
        }

        if ($this->isCsrfTokenValid('send' . $contract->getIdContrat(), $request->request->get('_token'))) {
            try {
                $this->service->sendContract($contract);
                $this->addFlash('success', 'Contrat envoyé avec succès');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('contract_show', ['id' => $contract->getIdContrat()]);
    }

    /**
     * Signer le contrat (SENT -> SIGNED).
     * Accepte une signature en base64 (image).
     */
    #[Route('/{id}/sign', name: 'sign', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function sign(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CANDIDATE');

        $contract = $this->em->getRepository(Contrat::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé');
        }

        if (!$this->isOwnedByCurrentCandidate($contract)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas signer ce contrat.');
        }

        if ($this->isCsrfTokenValid('sign' . $contract->getIdContrat(), $request->request->get('_token'))) {
            $signatureBase64 = $request->request->get('signature');
            try {
                $this->service->signContract($contract, $signatureBase64);
                $this->addFlash('success', 'Contrat signé avec succès');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('contract_show', ['id' => $contract->getIdContrat()]);
    }

    /**
     * Refuser le contrat (SENT -> REJECTED).
     */
    #[Route('/{id}/reject', name: 'reject', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function reject(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CANDIDATE');

        $contract = $this->em->getRepository(Contrat::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé');
        }

        if (!$this->isOwnedByCurrentCandidate($contract)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas refuser ce contrat.');
        }

        if ($this->isCsrfTokenValid('reject' . $contract->getIdContrat(), $request->request->get('_token'))) {
            $this->service->rejectContract($contract);
            $this->addFlash('warning', 'Contrat refusé');
        }

        return $this->redirectToRoute('contract_show', ['id' => $contract->getIdContrat()]);
    }

    private function filterContractsByCurrentRole(array $contracts): array
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $contracts;
        }

        if ($this->isGranted('ROLE_RECRUITER')) {
            $company = $this->resolveRecruiterCompanyName();
            if ($company === null) {
                return [];
            }

            return array_values(array_filter($contracts, fn (Contrat $c): bool => $this->sameText((string) $c->getCompanyName(), $company)));
        }

        if ($this->isGranted('ROLE_CANDIDATE')) {
            $candidateName = $this->resolveCandidateFullName();
            if ($candidateName === null) {
                return [];
            }

            return array_values(array_filter($contracts, fn (Contrat $c): bool => $this->sameText((string) $c->getCandidateName(), $candidateName)));
        }

        return [];
    }

    private function canAccessContract(Contrat $contract): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->isGranted('ROLE_RECRUITER')) {
            return $this->isOwnedByCurrentRecruiter($contract);
        }

        if ($this->isGranted('ROLE_CANDIDATE')) {
            return $this->isOwnedByCurrentCandidate($contract);
        }

        return false;
    }

    private function isOwnedByCurrentRecruiter(Contrat $contract): bool
    {
        $company = $this->resolveRecruiterCompanyName();
        if ($company === null) {
            return false;
        }

        return $this->sameText((string) $contract->getCompanyName(), $company);
    }

    private function isOwnedByCurrentCandidate(Contrat $contract): bool
    {
        $candidateName = $this->resolveCandidateFullName();
        if ($candidateName === null) {
            return false;
        }

        return $this->sameText((string) $contract->getCandidateName(), $candidateName);
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
