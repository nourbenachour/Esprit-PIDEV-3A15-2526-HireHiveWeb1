<?php

namespace App\Controller\Reclamation;

use App\Entity\Claim;
use App\Entity\Claim_response;
use App\Form\Reclamation\ClaimResponseType;
use App\Form\Reclamation\ClaimType;
use App\Repository\Reclamation\ClaimRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * AdminClaimController – Back-Office for ROLE_ADMIN.
 *
 * All routes live under /backoffice/claims.
 * Admins can see ALL claims (not limited to a user), manage statuses,
 * post responses, and view the statistics dashboard (Feature 1).
 */
#[Route('/backoffice/reclamations', name: 'app_admin_claim_')]
#[IsGranted('ROLE_ADMIN')]
class AdminClaimController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClaimRepository        $claimRepository,
    ) {}

    // -----------------------------------------------------------------------
    // INDEX – GET /backoffice/reclamations
    // -----------------------------------------------------------------------
    /**
     * Lists ALL claims across all users inside one table, along with the stats map.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('q');
        $status = $request->query->get('status');
        $priority = $request->query->get('priority');

        if ($search !== null || $status !== null || $priority !== null) {
            $claims = $this->claimRepository->searchMultiCriteria($search, $status, $priority);

            if ($request->isXmlHttpRequest()) {
                return $this->render('admin/claim/_table_body.html.twig', [
                    'claims' => $claims,
                ]);
            }
        } else {
            // 1. Fetch claims
            $claims = $this->claimRepository->findAllOrderedByDate();
        }

        // 2. Fetch stats
        $statsByStatus = $this->claimRepository->countByStatus();
        $total = array_sum($statsByStatus);

        return $this->render('admin/claim/index.html.twig', [
            'claims'        => $claims,
            'statsByStatus' => $statsByStatus,
            'total'         => $total,
        ]);
    }

    // -----------------------------------------------------------------------
    // SHOW – GET /backoffice/claims/{id}
    // -----------------------------------------------------------------------
    /**
     * Displays a claim in detail and renders the ClaimResponseType form
     * so the admin can post a response directly from this page.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $claim        = $this->findClaimOrThrow($id);
        $responseForm = $this->createForm(ClaimResponseType::class, new Claim_response());

        return $this->render('admin/claim/show.html.twig', [
            'claim'        => $claim,
            'responses'    => $claim->getClaim_responses(),
            'responseForm' => $responseForm->createView(),
        ]);
    }

    // -----------------------------------------------------------------------
    // RESPOND – POST /backoffice/claims/{id}/respond
    // -----------------------------------------------------------------------
    /**
     * Processes the admin's response submission.
     *
     * Flow:
     * 1. Build a ClaimResponseType form around a new Claim_response entity.
     * 2. handleRequest() binds the POST data.
     * 3. isValid() verifies the Assert\NotBlank on $message server-side.
     * 4. If valid: link response to claim, set responder_id, persist & flush.
     * 5. Optionally mark the claim as IN_PROGRESS if it was OPEN.
     */
    #[Route('/{id}/respond', name: 'respond', methods: ['POST'])]
    public function respond(Request $request, string $id): Response
    {
        $claim    = $this->findClaimOrThrow($id);
        $response = new Claim_response();
        $form     = $this->createForm(ClaimResponseType::class, $response);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Link response to its parent claim
            $response->setClaim_id($claim);

            // Record which admin posted the response
            /** @var \App\Entity\Users $admin */
            $admin = $this->getUser();
            $response->setResponder_id($admin->getId());

            // Auto-advance claim status from OPEN → IN_PROGRESS upon first response
            if ($claim->getStatus() === 'OPEN') {
                $claim->setStatus('IN_PROGRESS');
            }

            $this->em->persist($response);
            $this->em->flush();

            $this->addFlash('success', 'Réponse ajoutée avec succès.');
        } else {
            $this->addFlash('error', 'Le message de réponse ne peut pas être vide.');
        }

        return $this->redirectToRoute('app_admin_claim_show', ['id' => $id]);
    }

    // -----------------------------------------------------------------------
    // NEW – GET/POST /backoffice/reclamations/new
    // -----------------------------------------------------------------------
    /**
     * Admin creates a claim explicitly on behalf of a user.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, \App\Repository\User\UserRepository $userRepository, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        $users = $userRepository->findAll();
        $errors = [];

        if ($request->isMethod('POST')) {
            $userId = $request->request->get('user_id');
            $title = $request->request->get('title');
            $type = $request->request->get('type');
            $priority = $request->request->get('priority', 'LOW');
            $description = $request->request->get('description');

            $claim = new Claim();
            $claim->setUser_id($userId ?: null);
            $claim->setTitle((string)$title);
            $claim->setType((string)$type);
            $claim->setDescription((string)$description);
            $claim->setPriority((string)$priority);

            $validationErrors = $validator->validate($claim);

            if (empty($userId)) {
                $errors[] = "Veuillez sélectionner un utilisateur.";
            }

            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $err) {
                    $errors[] = $err->getMessage();
                }
            }

            if (empty($errors)) {
                $this->em->persist($claim);
                
                // Optional: Initial response from the admin creation page
                $responseText = trim((string)$request->request->get('initial_response', ''));
                if ($responseText !== '') {
                    $response = new Claim_response();
                    $response->setClaim_id($claim);
                    $response->setMessage($responseText);
                    
                    /** @var \App\Entity\Users $admin */
                    $admin = $this->getUser();
                    $response->setResponder_id($admin->getId());
                    
                    $this->em->persist($response);
                }

                $this->em->flush();

                $this->addFlash('success', 'Réclamation créée avec succès pour cet utilisateur.');
                return $this->redirectToRoute('app_admin_claim_index');
            }
        }

        return $this->render('admin/claim/new.html.twig', [
            'users' => $users,
            'errors' => $errors,
        ]);
    }

    // -----------------------------------------------------------------------
    // EDIT – GET /backoffice/reclamations/{id}/edit   (show form)
    // -----------------------------------------------------------------------
    /**
     * Allows an admin to update ONLY a claim's status, and optionally post an admin response.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $id): Response
    {
        $claim = $this->findClaimOrThrow($id);

        if ($request->isMethod('POST')) {
            $newStatus = $request->request->get('claim_status');
            if ($newStatus && in_array($newStatus, ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])) {
                $claim->setStatus($newStatus);
                if ($newStatus === 'RESOLVED') {
                    $claim->setResolved_at(new \DateTime());
                }
            }

            $newPriority = $request->request->get('claim_priority');
            if ($newPriority && in_array($newPriority, ['LOW', 'MEDIUM', 'URGENT'])) {
                $claim->setPriority($newPriority);
            }

            // Optional: New response from the edit page
            $responseText = trim((string)$request->request->get('claim_response', ''));
            if ($responseText !== '') {
                $response = new Claim_response();
                $response->setClaim_id($claim);
                $response->setMessage($responseText);
                
                /** @var \App\Entity\Users $admin */
                $admin = $this->getUser();
                $response->setResponder_id($admin->getId());
                
                $this->em->persist($response);
            }

            $this->em->flush();
            $this->addFlash('success', 'Statut mis à jour avec succès.');

            return $this->redirectToRoute('app_admin_claim_show', ['id' => $id]);
        }

        return $this->render('admin/claim/edit.html.twig', [
            'claim' => $claim,
        ]);
    }

    // -----------------------------------------------------------------------
    // DELETE – POST /backoffice/claims/{id}/delete
    // -----------------------------------------------------------------------
    /**
     * Hard-deletes a claim (and all its responses via CASCADE).
     * POST-only to prevent accidental deletion via GET links.
     * CSRF token validated for security.
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, string $id): Response
    {
        $claim = $this->findClaimOrThrow($id);

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->em->remove($claim);
            $this->em->flush();
            $this->addFlash('success', 'Réclamation supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_claim_index');
    }

    // -----------------------------------------------------------------------
    // Private helper
    // -----------------------------------------------------------------------
    private function findClaimOrThrow(string $id): Claim
    {
        $claim = $this->claimRepository->find($id);

        if (!$claim) {
            throw $this->createNotFoundException('Claim not found.');
        }

        return $claim;
    }
}
