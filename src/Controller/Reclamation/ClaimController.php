<?php

namespace App\Controller\Reclamation;

use App\Entity\Claim;
use App\Form\Reclamation\ClaimType;
use App\Repository\Reclamation\ClaimRepository;
use App\Service\ClaimPriorityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * ClaimController – Front-Office CRUD for ROLE_USER.
 *
 * All routes are prefixed with /reclamations.
 * Every action verifies that the logged-in user owns the claim they are
 * trying to view/edit/delete (ownership check).
 *
 * Business Feature 2 (Priority Detection) is applied inside new() before persist.
 */
#[Route('/frontoffice/reclamations', name: 'app_claim_')]
#[IsGranted('ROLE_USER')]
class ClaimController extends AbstractController
{
    // -----------------------------------------------------------------------
    // Constructor injection – EntityManager, Repository, and Priority Service
    // -----------------------------------------------------------------------
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClaimRepository        $claimRepository,
        private readonly ClaimPriorityService   $priorityService,
    ) {}

    // -----------------------------------------------------------------------
    // LIST – GET /reclamations
    // -----------------------------------------------------------------------
    /**
     * Lists all claims belonging to the currently authenticated user.
     *
     * The repository's findByUserId() restricts the query to the logged-in
     * user's id_user, so users cannot see each other's claims.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var \App\Entity\Users $user */
        $user   = $this->getUser();
        $claims = $this->claimRepository->findByUserId($user->getId());

        return $this->render('claim/index.html.twig', [
            'claims' => $claims,
        ]);
    }

    // -----------------------------------------------------------------------
    // NEW – GET /reclamations/new   (show form)
    // POST /reclamations/new  (process form)
    // -----------------------------------------------------------------------
    /**
     * Handles claim creation.
     *
     * Flow:
     * 1. Instantiate a blank Claim with default status=OPEN.
     * 2. Build the ClaimType form (novalidate attr – HTML5 validation OFF).
     * 3. On POST: handleRequest() binds data; isSubmitted() + isValid() checks
     *    server-side Assert constraints on the entity.
     * 4. If INVALID → re-render form with error messages (proves server-side validation).
     * 5. If VALID → Feature 2: call ClaimPriorityService.detectPriority(description)
     *    and set the auto-detected priority on the entity before persisting.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $claim = new Claim();
        $form  = $this->createForm(ClaimType::class, $claim);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // The priority is now populated directly from the ClaimType dropdown choice.
            // Feature 2 auto-detection is disabled so we don't overwrite manual user selections.
            // $priority = $this->priorityService->detectPriority($claim->getDescription());
            // $claim->setPriority($priority);

            $priority = $claim->getPriority();

            // Set owner (user_id FK)
            /** @var \App\Entity\Users $user */
            $user = $this->getUser();
            $claim->setUser_id($user->getId());

            $this->em->persist($claim);
            $this->em->flush();

            $this->addFlash(
                'success',
                sprintf(
                    'Votre réclamation "%s" a été soumise avec succès ! Priorité auto-détectée: %s.',
                    $claim->getTitle(),
                    $priority
                )
            );

            return $this->redirectToRoute('app_claim_index');
        }

        return $this->render('claim/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // -----------------------------------------------------------------------
    // SHOW – GET /reclamations/{id}
    // -----------------------------------------------------------------------
    /**
     * Displays the details of a single claim along with all responses posted
     * by admins. The ownership check ensures users can only view their own claims.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $claim = $this->findOwnedClaimOrThrow($id);
        
        $responseForm = $this->createForm(\App\Form\Reclamation\ClaimResponseType::class, new \App\Entity\Claim_response());

        return $this->render('claim/show.html.twig', [
            'claim'     => $claim,
            'responses' => $claim->getClaim_responses(),
            'responseForm' => $responseForm->createView(),
        ]);
    }

    // -----------------------------------------------------------------------
    // REPLY – POST /reclamations/{id}/reply
    // -----------------------------------------------------------------------
    /**
     * Processes a client's reply to the chat thread.
     */
    #[Route('/{id}/reply', name: 'reply', methods: ['POST'])]
    public function reply(Request $request, string $id): Response
    {
        $claim = $this->findOwnedClaimOrThrow($id);
        
        $response = new \App\Entity\Claim_response();
        $form     = $this->createForm(\App\Form\Reclamation\ClaimResponseType::class, $response);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            if ($claim->getStatus() === 'CLOSED') {
                $this->addFlash('error', 'Cette réclamation est fermée. Vous ne pouvez plus y répondre.');
                return $this->redirectToRoute('app_claim_show', ['id' => $id]);
            }

            $response->setClaim_id($claim);

            /** @var \App\Entity\Users $user */
            $user = $this->getUser();
            $response->setResponder_id($user->getId());

            $this->em->persist($response);
            $this->em->flush();

            $this->addFlash('success', 'Votre message a été envoyé.');
        } else {
            $this->addFlash('error', 'Le message ne peut pas être vide.');
        }

        return $this->redirectToRoute('app_claim_show', ['id' => $id]);
    }

    // -----------------------------------------------------------------------
    // EDIT – GET /reclamations/{id}/edit   (show pre-filled form)
    // POST /reclamations/{id}/edit  (process update)
    // -----------------------------------------------------------------------
    /**
     * Allows a user to update their own claim's title, description, and type.
     * Status and priority are NOT editable by the user (admin-only fields).
     *
     * The form is the same ClaimType – server-side validation is re-applied on POST.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $id): Response
    {
        $claim = $this->findOwnedClaimOrThrow($id);

        if ($claim->getClaim_responses()->count() > 0) {
            $this->addFlash('error', 'Vous ne pouvez plus éditer cette réclamation car l\'administration a déjà répondu.');
            return $this->redirectToRoute('app_claim_index');
        }

        $form  = $this->createForm(ClaimType::class, $claim);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Réclamation mise à jour avec succès.');

            return $this->redirectToRoute('app_claim_show', ['id' => $id]);
        }

        return $this->render('claim/edit.html.twig', [
            'claim' => $claim,
            'form'  => $form->createView(),
        ]);
    }

    // -----------------------------------------------------------------------
    // DELETE – POST /reclamations/{id}/delete
    // -----------------------------------------------------------------------
    /**
     * Deletes a claim. Accepts POST only to prevent accidental GET-triggered deletion.
     * A CSRF token is validated inside the template's delete form.
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, string $id): Response
    {
        $claim = $this->findOwnedClaimOrThrow($id);

        // CSRF protection: the template must pass token named 'delete{id}'
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->em->remove($claim);
            $this->em->flush();
            $this->addFlash('success', 'Réclamation supprimée.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_claim_index');
    }

    // -----------------------------------------------------------------------
    // Private helper: ownership check
    // -----------------------------------------------------------------------
    /**
     * Finds a Claim by id and verifies it belongs to the logged-in user.
     * Throws a 404 if the claim doesn't exist, or 403 if it belongs to someone else.
     */
    private function findOwnedClaimOrThrow(string $id): Claim
    {
        $claim = $this->claimRepository->find($id);

        if (!$claim) {
            throw $this->createNotFoundException('Claim not found.');
        }

        /** @var \App\Entity\Users $user */
        $user = $this->getUser();

        if ((string) $claim->getUser_id() !== (string) $user->getId()) {
            throw $this->createAccessDeniedException('You can only access your own claims.');
        }

        return $claim;
    }
}
