<?php

namespace App\Controller\JobOffer;

use App\Repository\JobOfferRepository;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class JobOfferFrontController extends AbstractController
{
    #[Route('/frontoffice/job/offers', name: 'app_joboffer_front_list', methods: ['GET'])]
    public function offers(Request $request, JobOfferRepository $repo, ApplicationRepository $appRepo): Response
    {
        $search = $request->query->get('q', '');
        $contractType = $request->query->get('contract_type', '');
        $offers = $repo->findOpen($search, $contractType);

        // Also load the current user's applications for the combined view
        $applications = [];
        $user = $this->getUser();
        if ($user && method_exists($user, 'getId')) {
            $applications = $appRepo->findByCandidate((int) $user->getId());
        }

        return $this->render('joboffer/frontoffice/offers.html.twig', [
            'offers' => $offers,
            'applications' => $applications,
            'filter_query' => $search,
            'filter_contract_type' => $contractType,
        ]);
    }

    #[Route('/frontoffice/job/offers/{id}', name: 'app_joboffer_front_detail', methods: ['GET'])]
    public function detail(int $id, JobOfferRepository $repo, ApplicationRepository $appRepo): Response
    {
        $offre = $repo->findById($id);
        if (!$offre) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_joboffer_front_list');
        }

        // Closed/draft offers are not visible to candidates
        if (($offre['status'] ?? '') !== 'OPEN') {
            $this->addFlash('error', "Cette offre n'est plus disponible.");
            return $this->redirectToRoute('app_joboffer_front_list');
        }

        $user = $this->getUser();
        $hasApplied = false;
        if ($user && method_exists($user, 'getId')) {
            $hasApplied = $appRepo->hasApplied((int) $user->getId(), $id);
        }

        return $this->render('joboffer/frontoffice/offer_detail.html.twig', [
            'offre' => $offre,
            'has_applied' => $hasApplied,
        ]);
    }

    #[Route('/frontoffice/job/offers/{id}/apply', name: 'app_joboffer_front_apply', methods: ['POST'])]
    public function apply(int $id, Request $request, JobOfferRepository $offerRepo, ApplicationRepository $appRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour postuler.');
            return $this->redirectToRoute('app_login');
        }

        $offre = $offerRepo->findById($id);
        if (!$offre || ($offre['status'] ?? '') !== 'OPEN') {
            $this->addFlash('error', "Cette offre n'est plus disponible.");
            return $this->redirectToRoute('app_joboffer_front_list');
        }

        $userId = (int) $user->getId();

        if ($appRepo->hasApplied($userId, $id)) {
            $this->addFlash('warning', 'Vous avez déjà postulé à cette offre.');
            return $this->redirectToRoute('app_joboffer_front_detail', ['id' => $id]);
        }

        // Validate
        $errors = [];
        $lettre = trim((string) $request->request->get('lettre', ''));
        $cvFile = $request->files->get('cv_file');

        if ($lettre === '' || mb_strlen($lettre) < 20) {
            $errors[] = 'La lettre de motivation est obligatoire (minimum 20 caractères).';
        }
        if ($lettre !== '' && !preg_match('/\p{L}/u', $lettre)) {
            $errors[] = 'La lettre de motivation doit contenir au moins une lettre.';
        }
        if (!$cvFile) {
            $errors[] = 'Le CV est obligatoire (fichier PDF).';
        } elseif ($cvFile->getMimeType() !== 'application/pdf') {
            $errors[] = 'Le CV doit être au format PDF.';
        }

        if (!empty($errors)) {
            foreach ($errors as $msg) { $this->addFlash('error', $msg); }
            return $this->redirectToRoute('app_joboffer_front_detail', ['id' => $id]);
        }

        // Upload CV
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/cv';
        if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0775, true); }
        $cvFilename = uniqid('cv_', true) . '.pdf';
        $cvFile->move($uploadsDir, $cvFilename);
        $cvPath = '/uploads/cv/' . $cvFilename;

        $appRepo->insert([
            'condidat_id' => $userId,
            'job_offer_id' => $id,
            'cv_file_path' => $cvPath,
            'lettre' => $lettre,
        ]);

        $this->addFlash('success', 'Votre candidature a été envoyée avec succès !');
        return $this->redirectToRoute('app_joboffer_front_my_applications');
    }

    #[Route('/frontoffice/job/my-applications', name: 'app_joboffer_front_my_applications', methods: ['GET'])]
    public function myApplications(): Response
    {
        return $this->redirectToRoute('app_joboffer_front_list', ['tab' => 'candidatures']);
    }
}
